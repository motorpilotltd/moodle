<?php

namespace local_dynamic_cohorts;

class dynamic_cohorts
{

    const TYPE_STANDARD = 0;
    const TYPE_DYNAMIC = 1;

    const CRITERIA_TYPE_CONTAIN = 0;
    const CRITERIA_TYPE_DOES_NOT_CONTAIN = 1;
    const CRITERIA_TYPE_IS_EQUAL_TO = 2;
    const CRITERIA_TYPE_STARTS_WITH = 3;
    const CRITERIA_TYPE_ENDS_WITH = 4;
    const CRITERIA_TYPE_IS_EMPTY = 5;
    const CRITERIA_TYPE_IS_NOT_EQUAL_TO = 6;
    const CRITERIA_TYPE_IS_CHECKED = 7;
    const CRITERIA_TYPE_IS_NOT_CHECKED = 8;
    const CRITERIA_TYPE_IS_AFTER = 9;
    const CRITERIA_TYPE_IS_BEFORE = 10;
    const CRITERIA_TYPE_IS_MEMBER = 11;
    const CRITERIA_TYPE_IS_NOT_MEMBER = 12;

    const OPERATOR_AND = 1;    
    const OPERATOR_OR = 0;

    const FIELD_TYPE_USER = 1;
    const FIELD_TYPE_CUSTOM = 2;
    const FIELD_TYPE_HUB = 3;
    const FIELD_TYPE_COHORT = 4;

    /**
     * Get cohort types
     *
     * @return array
     */
    public static function get_cohort_types()
    {
        return [
            self::TYPE_STANDARD => get_string('typestandard', 'local_dynamic_cohorts'),
            self::TYPE_DYNAMIC => get_string('typedynamic', 'local_dynamic_cohorts')
        ];
    }

    /**
     * Get rulesets with rules
     * @param $cohortid
     * @return array
     */
    public static function get_rulesets($cohortid){
        global $DB;
        $rulesets = $DB->get_records('wa_cohort_rulesets', ['cohortid' => $cohortid]);
        $rules = $DB->get_records('wa_cohort_ruleset_rules', ['cohortid' => $cohortid]);
        $customfields = $DB->get_records('user_info_field', null, '', 'shortname, datatype');
        foreach($rules as $rule){
            if(!isset($rulesets[$rule->rulesetid]->rules)){
                $rulesets[$rule->rulesetid]->rules = [];
            }
            if($rule->fieldtype == self::FIELD_TYPE_CUSTOM){
                $rule->datatype = $customfields[$rule->field]->datatype;
            }   
            $rulesets[$rule->rulesetid]->rules[] = $rule;
        }
        return $rulesets;
    }

    /**
     * Add dynamic cohort fields to standard moodle cohort record
     *
     * @param $cohort cohort record object
     * @return mixed
     */
    public static function add_dynamic_cohort_fields($cohort)
    {
        global $DB;
        $dynamiccohort = $DB->get_record('wa_cohort', ['cohortid' => $cohort->id]);
        $rulesets = $DB->get_records('wa_cohort_rulesets', ['cohortid' => $cohort->id]);
        $rules = $DB->get_records('wa_cohort_ruleset_rules', ['cohortid' => $cohort->id]);
        if ($dynamiccohort) {
            $cohort->type = self::TYPE_DYNAMIC;
            $cohort->memberadd = $dynamiccohort->memberadd;
            $cohort->memberremove = $dynamiccohort->memberremove;
            $cohort->operator = $dynamiccohort->operator;
            $cohort->rulesets = [];
            foreach($rulesets as $ruleset){
                $ruleset->rules = [];
                foreach($rules as $rule){
                    if($rule->rulesetid == $ruleset->id){
                        $ruleset->rules[] = $rule;
                    }
                }
                $cohort->rulesets[] = $ruleset;
            }
        } else {
            $cohort->type = self::TYPE_STANDARD;
            $cohort->memberadd = 0;
            $cohort->memberremove = 0;
            $cohort->operator = 1;
            $cohort->rulesets = [];
        }

        $query = "
            SELECT    
              wcr.id, 
              wcr.roleid,
              wcr.contextid
            FROM {wa_cohort_roles} wcr
            WHERE wcr.cohortid = :cohortid
        ";

        $cohort->roles = $DB->get_records_sql($query, ['cohortid' => $cohort->id]);

        return $cohort;
    }

    /**
     * Update cohort
     * @param $data form data
     */
    public static function update_cohort($data)
    {
        global $DB;
        $dynamiccohort = $DB->get_record('wa_cohort', ['cohortid' => $data->id]);
        if ($data->type == self::TYPE_DYNAMIC) {
            /**
             * Save basic data for dynamic cohort
             */
            if (!$dynamiccohort) {
                $dynamiccohort = new \stdClass();
                $dynamiccohort->cohortid = $data->id;
                $dynamiccohort->memberadd = $data->adduser;
                $dynamiccohort->memberremove = $data->removeuser;
                $dynamiccohort->operator = $data->rulesetsoperator;
                $dynamiccohort->id = $DB->insert_record('wa_cohort', $dynamiccohort, true);
            } else {
                $dynamiccohort->memberadd = $data->adduser;
                $dynamiccohort->memberremove = $data->removeuser;
                $dynamiccohort->operator = $data->rulesetsoperator;
                $DB->update_record('wa_cohort', $dynamiccohort);
            }
            $addedrulesets = [];
            if(isset($data->rulesets)){
                foreach($data->rulesets as $rulesetid){
                    /**
                     * Add ruleset if any rule is configured
                     */
                    if(isset($data->rulesetrules['field'][$rulesetid])){
                        if(!$ruleset = $DB->get_record('wa_cohort_rulesets', ['id' => $rulesetid])){
                            $ruleset = new \stdClass();
                            $ruleset->timecreated = time();
                        }
                        $ruleset->cohortid = $data->id;
                        $ruleset->operator = $data->ruleoperator[$rulesetid];
                        $ruleset->timemodified = time();
                        if(!isset($ruleset->id)){
                            $ruleset->id = $DB->insert_record('wa_cohort_rulesets', $ruleset, true);
                        }else{
                            $DB->update_record('wa_cohort_rulesets', $ruleset);
                        }
                        $addedrulesets[] = $ruleset->id;
                        /**
                         * Add rules for single ruleset
                         */
                        $addedrules = [];
                        foreach($data->rulesetrules['field'][$rulesetid] as $rulecounter => $field){
                            $rule = new \stdClass();
                            $rule->cohortid = $data->id;
                            $rule->rulesetid = $ruleset->id;

                            switch ($field){
                                case 'cohort':
                                    $rule->fieldtype = self::FIELD_TYPE_COHORT;
                                    break;
                                case (preg_match('/^custom_/is', $field) ? true : false) :
                                    $rule->fieldtype = self::FIELD_TYPE_CUSTOM;
                                    break;
                                case (preg_match('/^hub_/is', $field) ? true : false) :
                                    $rule->fieldtype = self::FIELD_TYPE_HUB;
                                    break;
                                default:
                                    $rule->fieldtype = self::FIELD_TYPE_USER;
                                    break;
                            }
                            $rule->field = preg_replace('/^(custom|user|hub)_/is', '', $field);
                            $rule->criteriatype = $data->rulesetrules['criteriatype'][$rulesetid][$rulecounter];
                            /**
                             * If rule criteria type is date or datetime change date to timestamp
                             */
                            if(in_array(self::get_field_type($field), ['date', 'datetime'] )){
                                $date = new \DateTime($data->rulesetrules['value'][$rulesetid][$rulecounter]);
                                $rule->value = $date->getTimestamp();
                            }else{
                                $rule->value = $data->rulesetrules['value'][$rulesetid][$rulecounter];    
                            }
                            $rule->timecreated = time();
                            $rule->timemodified = time();
                            $rule->id = $DB->insert_record('wa_cohort_ruleset_rules', $rule, true);
                            $addedrules[] = $rule->id;
                        }
                        /**
                         * Delete old rules
                         */
                        list($sql, $params) = $DB->get_in_or_equal($addedrules, SQL_PARAMS_NAMED, 'param', false, true);
                        $query = "
                            cohortid = :cohortid
                            AND rulesetid = :rulesetid
                            AND id ".$sql."
                        ";
                        $params['cohortid'] = $data->id;
                        $params['rulesetid'] = $ruleset->id;
                        $DB->delete_records_select('wa_cohort_ruleset_rules', $query, $params);
                    }
                }
            }
            /**
             * Delete old rulesets and rules
             */
            list($sql, $params) = $DB->get_in_or_equal($addedrulesets, SQL_PARAMS_NAMED, 'param', false, true);
            $query = "
                cohortid = :cohortid
                AND id ".$sql."
            ";
            $params['cohortid'] = $data->id;
            $DB->delete_records_select('wa_cohort_rulesets', $query, $params);

            $query = "
                cohortid = :cohortid
                AND rulesetid ".$sql."
            ";
            $params['cohortid'] = $data->id;
            $DB->delete_records_select('wa_cohort_ruleset_rules', $query, $params);
        } else {
            if ($dynamiccohort) {
                /**
                 * Dynamic cohort was changed to manual - remove all dynamic cohort data and clear members
                 */
                self::delete_dynamic_cohort($data->id);
                $DB->delete_records('cohort_members', array('cohortid'=>$data->id));
            }
        }

        /**
         * Add roles
         */
        $DB->delete_records('wa_cohort_roles', ['cohortid' => $data->id]);
        if(isset($data->roles) && is_array($data->roles)){
            foreach ($data->roles as $role) {
                $record = new \stdClass();
                $record->cohortid = $data->id;
                $record->roleid = $role['roleid'];
                $record->contextid = $role['contextid'];
                $record->timecreated = time();
                $DB->insert_record('wa_cohort_roles', $record);
            }
        }
    }

    /**
     * Delete dynamic cohort
     * @param $cohortid
     * @param bool $withroles
     */
    public static function delete_dynamic_cohort($cohortid, $withroles = false)
    {
        global $DB;
        $DB->delete_records('wa_cohort', ['cohortid' => $cohortid]);
        $DB->delete_records('wa_cohort_rulesets', ['cohortid' => $cohortid]);
        $DB->delete_records('wa_cohort_ruleset_rules', ['cohortid' => $cohortid]);
        if($withroles){
            $DB->delete_records('wa_cohort_roles', ['cohortid' => $cohortid]);
        }
    }

    /**
     * Add info about cohort type to results of standard moodle get cohort functions (cohort_get_cohorts, cohort_get_all_cohorts)
     * @param $cohorts return from cohort_get_cohorts or cohort_get_all_cohorts moodle function
     */
    public static function process_cohorts($cohorts)
    {
        global $DB;
        $cohortsids = [0];
        foreach ($cohorts['cohorts'] as $cohort) {
            $cohortsids[] = $cohort->id;
        }

        list($usql, $params) = $DB->get_in_or_equal($cohortsids);

        $query = "
            SELECT 
              c.cohortid,
              c.memberadd,
              c.memberremove
            FROM {wa_cohort} c
            WHERE c.cohortid " . $usql . "
        ";

        $dynamiccohorts = $DB->get_records_sql($query, $params);

        foreach ($cohorts['cohorts'] as $cohort) {
            $cohort->type = self::TYPE_STANDARD;
            if (isset($dynamiccohorts[$cohort->id])) {
                $cohort->type = self::TYPE_DYNAMIC;
            }
        }


        return $cohorts;
    }

    /**
     * Check if given cohort is dynamic
     * @param $cohortid Cohort ID
     * @return bool
     */
    public static function check_if_dynamic($cohortid)
    {
        global $DB;

        return $DB->record_exists('wa_cohort', ['cohortid' => $cohortid]);
    }

    /**
     * Get cohort members
     * @param $cohortid
     * @param array $filters
     * @param int $limit
     * @return array
     */
    public static function get_members($cohortid, $filters = [], $limit = 200)
    {
        global $DB;

        $params = [];
        $query = "
			SELECT 
				DISTINCT u.*
			FROM {cohort_members} cm 
			JOIN {user} u ON u.id = cm.userid
			WHERE cm.cohortid = :cohortid
		";

        if (isset($filters['fullname'])) {
            $query .= "AND " . $DB->sql_like($DB->sql_fullname('u.firstname', 'u.lastname'), ":fullname", false);
            $params['fullname'] = '%' . $filters['fullname'] . '%';
        }

        $params['cohortid'] = $cohortid;
        $users = $DB->get_records_sql($query, $params, 0, $limit);
        foreach ($users as $user) {
            $user->fullname = fullname($user);
        }
        return $users;
    }

    /**
     * Get user standard fields
     * @return array
     */
    public static function get_user_fields()
    {
        return [
            'user_firstname' => get_string('firstname'),
            'user_lastname' => get_string('lastname'),
            'user_email' => get_string('email'),
            'user_city' => get_string('city'),
            'user_country' => get_string('country'),
            'user_idnumber' => get_string('idnumber')
        ];
    }

    /**
     * Get user custom fields
     * @return array
     */
    public static function get_user_custom_fields()
    {
        global $DB;
        $customfields = $DB->get_records_menu('user_info_field', null, '', $DB->sql_concat('\'custom_\'', 'shortname') . ' as shortname, name');
        return $customfields;
    }

    /**
     * Get user hub fields.
     * @return array
     */
    public static function get_user_hub_fields()
    {
        global $DB;
        // Open second connection as we need no prefix.
        $cfg = $DB->export_dbconfig();
        if (!isset($cfg->dboptions)) {
            $cfg->dboptions = array();
        }
        // Pretend it's external to remove prefix injection.
        $DB2 = \moodle_database::get_driver_instance($cfg->dbtype, $cfg->dblibrary, true);
        $DB2->connect($cfg->dbhost, $cfg->dbuser, $cfg->dbpass, $cfg->dbname, false, $cfg->dboptions);
        $hubfields = array();
        $columns = $DB2->get_columns('ARUP_ALL_STAFF_V');
        foreach (array_keys($columns) as $name) {
            $hubfields["hub_{$name}"] = "HUB: {$name}";
        }
        return $hubfields;
    }

    /**
     * Get all rule fields
     * @return array
     */
    public static function get_rule_fields()
    {
        return self::get_user_fields() + self::get_user_custom_fields() + self::get_user_hub_fields() + ['cohort' => get_string('cohort', 'local_dynamic_cohorts')];
    }

    /**
     * Return array of criteria types basing of field type
     * 
     * @param string $fieldtype (empty || checkbox || datetime || all)
     * @return array
     */
    public static function get_criteria_types($fieldtype = '')
    {
        $criteriatypes = [];
        if($fieldtype == 'all' || $fieldtype == 'checkbox'){
            $criteriatypes += [
                self::CRITERIA_TYPE_IS_CHECKED => get_string('ischecked', 'local_dynamic_cohorts'),
                self::CRITERIA_TYPE_IS_NOT_CHECKED => get_string('isnotchecked', 'local_dynamic_cohorts'),
            ];
        }
        if($fieldtype == 'all' || $fieldtype == 'datetime'){
            $criteriatypes += [
                self::CRITERIA_TYPE_IS_EQUAL_TO => get_string('isequalto', 'filters'),
                self::CRITERIA_TYPE_IS_NOT_EQUAL_TO => get_string('isnotequalto', 'filters'),
                self::CRITERIA_TYPE_IS_AFTER => get_string('isafter', 'filters'),
                self::CRITERIA_TYPE_IS_BEFORE => get_string('isbefore', 'filters'),
            ];
        }
        if($fieldtype == 'all' || $fieldtype == ''){
            $criteriatypes += [
                self::CRITERIA_TYPE_CONTAIN => get_string('contains', 'filters'),
                self::CRITERIA_TYPE_DOES_NOT_CONTAIN => get_string('doesnotcontain', 'filters'),
                self::CRITERIA_TYPE_IS_EQUAL_TO => get_string('isequalto', 'filters'),
                self::CRITERIA_TYPE_STARTS_WITH => get_string('startswith', 'filters'),
                self::CRITERIA_TYPE_ENDS_WITH => get_string('endswith', 'filters'),
                self::CRITERIA_TYPE_IS_EMPTY => get_string('isempty', 'filters'),
                self::CRITERIA_TYPE_IS_NOT_EQUAL_TO => get_string('isnotequalto', 'filters')
            ];
        }

        if($fieldtype == 'all' || $fieldtype == 'cohort'){
            $criteriatypes += [
                self::CRITERIA_TYPE_IS_MEMBER => get_string('ismember', 'local_dynamic_cohorts'),
                self::CRITERIA_TYPE_IS_NOT_MEMBER => get_string('isnotmember', 'local_dynamic_cohorts')
            ];
        }

        return $criteriatypes;

    }

    /**
     * Check field type for user custom fields
     * @param $field
     * @return string
     */
    public static function get_field_type($field){
        global $DB;
        if($field == 'cohort'){
            return 'cohort';
        }elseif(empty($field) || preg_match('/^(user|hub)_/is', $field)){
            return 'text';
        }else{
            $infofield = $DB->get_record('user_info_field', ['shortname' => str_replace('custom_', '', $field)]);
            switch ($infofield->datatype){
                case 'checkbox':
                    return $infofield->datatype;
                case 'datetime':
                    if($infofield->param3 == 1){ //with time
                        return $infofield->datatype;
                    }else{
                        return 'date';
                    }
                    break;
                default:
                    return 'text';
                    break;
            }
        }
    }

    /**
     * Check fieldtype and get rule criteria types basing on fields type
     * @param $field
     * @return array
     */
    public static function get_criteria_types_by_field_type($field)
    {
        switch (self::get_field_type($field)) {
            case 'cohort':
                return \local_dynamic_cohorts\dynamic_cohorts::get_criteria_types('cohort');
            case 'checkbox':
                return \local_dynamic_cohorts\dynamic_cohorts::get_criteria_types('checkbox');
                break;
            case 'datetime':
            case 'date':
                return \local_dynamic_cohorts\dynamic_cohorts::get_criteria_types('datetime');
                break;
            default:
                return \local_dynamic_cohorts\dynamic_cohorts::get_criteria_types();
                break;
        }
    }

    /**
     * Get field type prefix:
     * - custom - user custom fields
     * - user - user profile field
     * - empty means other (i.e. cohort)
     *
     * @param $fieldtype
     * @return string
     */
    public static function get_fieldtype_prefix($fieldtype){
        switch($fieldtype){
            case dynamic_cohorts::FIELD_TYPE_CUSTOM:
                return 'custom_';
            case dynamic_cohorts::FIELD_TYPE_USER:
                return 'user_';
            case dynamic_cohorts::FIELD_TYPE_HUB:
                return 'hub_';
            default:
                return '';
        }
    }

    /**
     * Get list of cohorts
     *
     * @param $where - SQL where condition
     * @return array
     * @throws \dml_exception
     */
    public static function get_cohorts($where){
        global $DB;
        return $DB->get_records_select_menu('cohort', $where, null, 'name', 'id, name');
    }

    /**
     * Export cohort members
     *
     * @param $cohortid
     * @param $type - csv
     * @return string
     * @throws \coding_exception
     */
    public static function export_members($cohortid, $type){
        raise_memory_limit(MEMORY_HUGE);
        $members = self::get_members($cohortid, [], 0);
        $fields = [
                'idnumber' => get_string('staffid', 'local_dynamic_cohorts'),
                'firstname' => get_string('firstname', 'local_dynamic_cohorts'),
                'lastname' => get_string('lastname', 'local_dynamic_cohorts'),
                'email' => get_string('email', 'local_dynamic_cohorts'),
                'icq' => get_string('icq', 'local_dynamic_cohorts'),
                'department' => get_string('department', 'local_dynamic_cohorts'),
                'city' => get_string('city', 'local_dynamic_cohorts'),
                'address' => get_string('address', 'local_dynamic_cohorts')
            ];
        $data = [array_values($fields)];
        foreach($members as $member){
            $record = [];
            foreach($fields as $field => $fieldname){
                $record[] = $member->$field;
            }
            $data[] = $record;
        }
        switch ($type){
            case 'csv':
                return self::print_csv($data);
                break;
        }

    }

    /**
     * Print CSV
     *
     * @param $records
     * @param string $delimiter
     * @param string $enclosure
     * @return string
     */
    public static function print_csv($records, $delimiter = 'comma', $enclosure = '"') {
        global $CFG;
        require_once($CFG->libdir . '/csvlib.class.php');
        
        $csvdata = new \csv_export_writer($delimiter, $enclosure);
        foreach ($records as $row) {
            $csvdata->add_data($row);
        }
        $data = $csvdata->print_csv_data(true);
        return $data;
    }

    /**
     * Get roles assignable for given context ID
     *
     * @param $contextid
     * @return array
     * @throws \coding_exception
     */
    public static function get_roles($contextid){
        return get_assignable_roles(\context::instance_by_id($contextid), ROLENAME_BOTH);
    }

    /**
     * Get role name
     *
     * @param $roleid
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_role_name($roleid){
        global $DB;
        $role = $DB->get_record('role', ['id' => $roleid]);
        return role_get_name($role);
    }

    /**
     * Get context name
     *
     * @param $contextid
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_context_name($contextid){
        global $DB;
        $query = "
            SELECT 
              CASE ctx.contextlevel WHEN :contextsystem THEN :systemcontextname ELSE cc.name END as contextname
            FROM {context} ctx
            LEFT JOIN {course_categories} cc ON cc.id=ctx.instanceid
            WHERE ctx.id = :contextid
        ";

        $context = $DB->get_record_sql($query, ['contextid' => $contextid, 'contextsystem' => CONTEXT_SYSTEM, 'systemcontextname' => get_string('system_context', 'local_dynamic_cohorts')]);

        return ($context ? $context->contextname : '');
    }

    /**
     * Get context list (system and all categories)
     *
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_context_list(){
        global $DB, $CFG;
        require_once($CFG->libdir . '/coursecatlib.php');
        $contexts = [1 => get_string('system_context', 'local_dynamic_cohorts')] ;
        $categories = \coursecat::make_categories_list();
        list($sql, $params) = $DB->get_in_or_equal(array_keys($categories), SQL_PARAMS_NAMED, 'param', true, true);

        $query = "
            SELECT 
              c.instanceid,
              c.id
            FROM {context} c
            WHERE c.contextlevel = :contextlevel
            AND c.instanceid ".$sql."
        ";

        $params['contextlevel'] = CONTEXT_COURSECAT;

        $categoriescontext = $DB->get_records_sql($query, $params);
        foreach($categories as $categoryid => $category){
            $contexts[$categoriescontext[$categoryid]->id] = $category;
        }
        return $contexts;
    }

    /**
     * Get list of contexts that I can view the cohorts in
     *
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_viewable_context_list(){
        $contexts = self::get_context_list();
        $cohortlist = [];
        foreach($contexts as $contextid => $context){
            $instance = \context::instance_by_id($contextid);
            if(has_any_capability(array('local/dynamic_cohorts:view', 'local/dynamic_cohorts:edit'), $instance)){
                $cohortlist[] = $contextid;
            }
        }
        return $cohortlist;
    }

    /**
     * Get list of contexts that I can edit the cohorts in
     *
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_editable_context_list(){
        $contexts = self::get_context_list();
        $cohortlist = [];
        foreach($contexts as $contextid => $context){
            $instance = \context::instance_by_id($contextid);
            if(has_any_capability(array('local/dynamic_cohorts:edit'), $instance)){
                $cohortlist[] = $contextid;
            }
        }
        return $cohortlist;
    }

    /**
     * Get cohort record
     *
     * @param $id
     */
    public static function get_cohort($id){
        global $DB;
        return $DB->get_record('cohort', ['id' => $id]);
    }
}