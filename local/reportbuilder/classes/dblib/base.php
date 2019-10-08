<?php

namespace local_reportbuilder\dblib;

class base {
    public static function getbdlib() {
        global $DB;

        static $libobj;

        if (!isset($libobj)) {
            switch (get_class($DB)) {
                case 'mysqli_native_moodle_database':
                    $libobj = new mysqli();
                    break;
                case 'mssql_native_moodle_database':
                    $libobj = new mssql();
                    break;
                case 'sqlsrv_native_moodle_database':
                    $libobj = new sqlsrv();
                    break;
                case 'pgsql_native_moodle_database':
                    $libobj = new pgsql();
                    break;
            }
        }

        return $libobj;
    }

    /**
     * Returns the SQL to be used in order to CAST one column to FLOAT
     *
     * Be aware that the CHAR column you're trying to cast contains really
     * int values or the RDBMS will throw an error!
     *
     * @param string fieldname the name of the field to be casted
     * @return string the piece of SQL code to be used in your statement.
     */
    public function sql_cast_char2float($fieldname) {
        return ' ' . $fieldname . ' ';
    }

    /**
     * Returns the SQL to be used in order to CAST one column to CHAR
     *
     * @param string fieldname the name of the field to be casted
     * @return string the piece of SQL code to be used in your statement.
     */
    public function sql_cast_2char($fieldname) {
        return ' ' . $fieldname . ' ';
    }

    /**
     * Returns true if group concat supports order by.
     *
     * Not all databases support order by.
     * If it is not supported the when calling sql_group_concat with an order by it will be ignored.
     * You can call this method to check whether the database supports it, in order to implement alternative solutions.
     *
     * @return bool
     * @deprecated since Totara 11.7 This function will be removed when MSSQL 2017 is the minimum required version. All other
     *         databases support orderby.
     * @since Totara 11.7
     */
    public function sql_group_concat_orderby_supported() {
        return true;
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
        throw new \coding_exception('the database driver does not support sql_group_concat()');
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
        throw new \coding_exception('the database driver does not support sql_group_concat_unique()');
    }

    /**
     * Get a recordset of objects and its count without limits applied given an SQL statement.
     *
     * This is useful for pagination in that it lets you avoid having to make a second COUNT(*) query.
     *
     * IMPORTANT NOTES:
     *   - Wrap queries with UNION in single SELECT. Otherwise an incorrect count will ge given.
     *
     * This method should only be used in situations where a count without limits is required.
     * If you don't need the count please use get_recordset_sql().
     *
     * @param string $sql the SQL select query to execute.
     * @param array|null $params array of sql parameters (optional)
     * @param int $limitfrom return a subset of records, starting at this point (optional).
     * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
     * @param int &$count This variable will be filled with the count of rows returned by the select without limits applied.
     *         (optional) Please note that you can also ask the returned recordset for the count by calling
     *         get_count_without_limits().
     * @return \counted_recordset A counted_recordset instance.
     * @throws \coding_exception if the database driver does not support this method.
     *
     * @since Totara 2.6.45, 2.7.28, 2.9.20, 9.8
     *
     */
    public function get_counted_recordset_sql($sql, array $params = null, $limitfrom = 0, $limitnum = 0, &$count = 0) {
        throw new \coding_exception('The database driver does not support get_counted_recordset_sql()');
    }

    /**
     * Returns a unique param name.
     *
     * @param string $prefix Defaults to param, make it something sensible for the code. Keep it short!
     * @return string
     */
    public function get_unique_param($prefix = 'param') {
        static $paramcounts = array();
        if (debugging('', DEBUG_DEVELOPER) && strlen($prefix) > 20) {
            // You should keep your param short in order to avoid running close to the limit if it gets used a lot.
            // Ideally you will make it only a word or two.
            debugging('Please reduce the length of your prefix to less than 20.', DEBUG_DEVELOPER);
        }
        if (!isset($paramcounts[$prefix])) {
            $paramcounts[$prefix] = 0;
        }
        $paramcounts[$prefix]++;
        return 'uq_' . $prefix . '_' . $paramcounts[$prefix];
    }
}