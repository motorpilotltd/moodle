<?php

namespace local_reportbuilder\dblib;

class sqlsrv extends base {

    public function sql_cast_char2float($fieldname) {
        return ' CAST(' . $fieldname . ' AS FLOAT) ';
    }

    public function sql_cast_2char($fieldname) {
        return ' CAST(' . $fieldname . ' AS NVARCHAR(MAX)) ';
    }

    public function sql_round($fieldname, $places = 0) {
        if ($places >= 0) {
            return "CAST(ROUND({$fieldname}, {$places}) AS DECIMAL(20, {$places}))";
        } else {
            return "ROUND(CAST({$fieldname} AS DECIMAL(20, 0)), {$places})";
        }
    }

    /**
     * Escape sql LIKE special characters like '_' or '%'.
     *
     * @param string $text The string containing characters needing escaping.
     * @param string $escapechar The desired escape character, defaults to '\\'.
     * @return string The escaped sql LIKE string.
     */
    public function sql_like_escape($text, $escapechar = '\\') {
        // Totara fix for weird [] LIKEs in SQL Server.
        $text = str_replace($escapechar, $escapechar . $escapechar, $text);
        $text = str_replace('_', $escapechar . '_', $text);
        $text = str_replace('%', $escapechar . '%', $text);
        $text = str_replace('[', $escapechar . '[', $text);
        return $text;
    }

    /**
     * Returns true if group concat supports order by.
     *
     * Not all databases support order by.
     * If it is not supported the when calling sql_group_concat with an order by it will be ignored.
     * You can call this method to check whether the database supports it in order to implement alternative solutions.
     *
     * @return bool
     * @deprecated since Totara 12 This function will be removed when MSSQL 2017 is the minimum required version. All
     *             other databases support orderby.
     * @since      Totara 12
     */
    public function sql_group_concat_orderby_supported() {
        global $DB;
        $serverinfo = $DB->get_server_info();
        return (version_compare($serverinfo['version'], '14', '>='));
    }

    /**
     * Returns database specific SQL code similar to GROUP_CONCAT() behaviour from MySQL.
     *
     * NOTE: NULL values are skipped, use COALESCE if you want to include a replacement.
     *
     * @param string $expr Expression to get individual values
     * @param string $separator The delimiter to separate the values, a simple string value only
     * @param string $orderby ORDER BY clause that determines order of rows with values,
     *                          optional since Totara 2.6.44, 2.7.27, 2.9.19, 9.7
     * @return string SQL fragment equivalent to GROUP_CONCAT()
     * @since Totara 2.6.34, 2.7.17, 2.9.9
     *
     */
    public function sql_group_concat($expr, $separator, $orderby = '') {
        global $DB;

        $separator = $DB->get_manager()->generator->addslashes($separator);
        // Function string_agg() is supported from SQL Server 2017.
        $serverinfo = $DB->get_server_info();
        if (version_compare($serverinfo['version'], '14', '>=')) {
            if ($orderby && $serverinfo['description'] !== 'mssqlubuntu') {
                $orderby = "WITHIN GROUP (ORDER BY {$orderby})";
            } else {
                $orderby = "";
            }
            return " string_agg(CAST({$expr} AS NVARCHAR(MAX)), '{$separator}') {$orderby} ";
        } else {
            return " dbo.GROUP_CONCAT_D($expr, '{$separator}') ";
        }
    }

    /**
     * Returns database specific SQL code similar to GROUP_CONCAT() behaviour from MySQL
     * where duplicates are removed.
     *
     * NOTE: NULL values are skipped, use COALESCE if you want to include a replacement,
     *       the ordering of results cannot be defined.
     *
     * @param string $expr Expression to get individual values
     * @param string $separator The delimiter to separate the values, a simple string value only
     * @return string SQL fragment equivalent to GROUP_CONCAT()
     * @since Totara 2.6.44, 2.7.27, 2.9.19, 9.7
     *
     */
    public function sql_group_concat_unique($expr, $separator) {
        global $DB;
        $separator = $DB->get_manager()->generator->addslashes($separator);
        return " dbo.GROUP_CONCAT_D(DISTINCT $expr, '{$separator}') ";
    }

    /**
     * Get a number of records as a moodle_recordset and count of rows without limit statement using a SQL statement.
     * This is useful for pagination to avoid second COUNT(*) query.
     *
     * IMPORTANT NOTES:
     *   - Wrap queries with UNION in single SELECT. Otherwise an incorrect count will ge given.
     *   - If an offset greater than 0 and greater than the total number of records is given the SQL query will be
     *     executed twice, a second time with a 0 offset and limit 1 in order to get a true total count.
     *
     * Since this method is a little less readable, use of it should be restricted to
     * code where it's possible there might be large datasets being returned.  For known
     * small datasets use get_records_sql - it leads to simpler code.
     *
     * @param string $sql the SQL select query to execute.
     * @param array $params array of sql parameters (optional)
     * @param int $limitfrom return a subset of records, starting at this point (optional).
     * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
     * @param int &$count this variable will be filled with count of rows returned by select without limit statement
     * @return counted_recordset A moodle_recordset instance.
     * @throws \dml_exception A DML specific exception is thrown for any errors.
     * @throws \coding_exception If an invalid result not containing the count is experienced
     * @since Totara 2.6.45, 2.7.28, 2.9.20, 9.8
     *
     */
    public function get_counted_recordset_sql($sql, array $params = null, $limitfrom = 0, $limitnum = 0, &$count = 0) {
        global $CFG, $DB;

        if (!preg_match('/^\s*SELECT\s/is', $sql)) {
            throw new \dml_exception('dmlcountedrecordseterror', null, "Counted recordset query must start with SELECT");
        }

        $countorfield = 'dml_count_recordset_rows';
        $sqlcnt = preg_replace('/^\s*SELECT\s/is', "SELECT COUNT('x') OVER () AS {$countorfield}, ", $sql);

        $recordset = $DB->get_recordset_sql($sqlcnt, $params, $limitfrom, $limitnum);
        if ($limitfrom > 0 and !$recordset->valid()) {
            // Bad luck, we are out of range and do not know how many are there, we need to make another query.
            $rs2 = $DB->get_recordset_sql($sqlcnt, $params, 0, 1);
            if ($rs2->valid()) {
                $current = $rs2->current();
                $rs2->close();
                if (!property_exists($current, $countorfield)) {
                    throw new \dml_exception("Expected column {$countorfield} used for counting records without limit was not found");
                } else if (!isset($current->{$countorfield})) {
                    throw new \coding_exception("Invalid count result in {$countorfield} used for counting records without limit");
                }
                $recordset = new counted_recordset($recordset, (int) $current->{$countorfield});
                $count = $recordset->get_count_without_limits();
                return $recordset;
            }
            $rs2->close();
        }
        $recordset = new counted_recordset($recordset, $countorfield);
        $count = $recordset->get_count_without_limits();

        return $recordset;
    }

}