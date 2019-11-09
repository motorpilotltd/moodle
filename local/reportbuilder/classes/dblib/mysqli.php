<?php

namespace local_reportbuilder\dblib;

class mysqli extends base {

    public function sql_cast_char2float($fieldname) {
        return ' CAST(' . $fieldname . ' AS DECIMAL(20,2)) ';
    }

    public function sql_cast_2char($fieldname) {
        $charset = $this->get_charset();
        return ' CAST(' . $fieldname . ' AS CHAR) COLLATE ' . $charset . '_bin';
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

        if ($orderby) {
            $orderby = "ORDER BY $orderby";
        } else {
            $orderby = "";
        }
        // See: http://dev.mysql.com/doc/refman/5.7/en/group-by-functions.html#function_group-concat
        $separator = $DB->get_manager()->generator->addslashes($separator);
        return " GROUP_CONCAT({$expr} {$orderby} SEPARATOR '{$separator}') ";
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

        // See: http://dev.mysql.com/doc/refman/5.7/en/group-by-functions.html#function_group-concat
        $separator = $DB->get_manager()->generator->addslashes($separator);
        return " GROUP_CONCAT(DISTINCT {$expr} SEPARATOR '{$separator}') ";
    }

    /**
     * Get a number of records as a moodle_recordset and count of rows without limit statement using a SQL statement.
     * This is usefull for pagination to avoid second COUNT(*) query.
     *
     * IMPORTANT NOTES:
     *   - Wrap queries with UNION in single SELECT. Otherwise an incorrect count will ge given.
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
     * @since Totara 2.6.45, 2.7.28, 2.9.20, 9.8
     *
     */
    public function get_counted_recordset_sql($sql, array $params = null, $limitfrom = 0, $limitnum = 0, &$count = 0) {
        global $DB;

        if (!preg_match('/^\s*SELECT\s/is', $sql)) {
            throw new \dml_exception('dmlcountedrecordseterror', null, "Counted recordset query must start with SELECT");
        }

        $sqlcnt = preg_replace('/^\s*SELECT\s/is', 'SELECT SQL_CALC_FOUND_ROWS ', $sql);

        $recordset = $DB->get_recordset_sql($sqlcnt, $params, $limitfrom, $limitnum);

        // Get count.
        $mysqlcount = $DB->get_field_sql("SELECT FOUND_ROWS()");
        $recordset = new counted_recordset($recordset, $mysqlcount);
        $count = $recordset->get_count_without_limits();

        return $recordset;
    }

    /**
     * Get expected database charset for current db collation.
     *
     * NOTE: only utf8 and utf8mb4 charsets are supported,
     *       so watch out if used for external database connections.
     *
     * @return string
     */
    public function get_charset() {
        global $DB;
        $dbcollation = $DB->get_dbcollation();
        if (strpos($dbcollation, 'utf8mb4_') === 0) {
            return 'utf8mb4';
        } else {
            return 'utf8';
        }
    }
}