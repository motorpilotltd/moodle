<?php
namespace local_dynamic_cohorts\task;
use local_dynamic_cohorts\dynamic_cohorts;

require_once($CFG->dirroot.'/cohort/lib.php');

class check_members extends \core\task\scheduled_task
{
    public function get_name()
    {
        // Shown in admin screens
        return get_string('checkmembers', 'local_dynamic_cohorts');
    }

    /**
     * Add or remove members from cohort
     *
     * @global \moodle_database $DB
     */
    public function execute()
    {
        global $DB;
        /**
         * We want to run only 100 action per cron crun
         */
        $actionscounter = 0;
        $maxactions = 100;

        // Load rules so we can optimise accordingly.
        $rules = $DB->get_records('wa_cohort_ruleset_rules');
        $userrules = array('id' => true);
        $hubrules = array();
        $customrules = array();
        foreach ($rules as $rule) {
            switch ($rule->fieldtype) {
                case dynamic_cohorts::FIELD_TYPE_CUSTOM:
                    $customrules[$rule->field] = true;
                    break;
                case dynamic_cohorts::FIELD_TYPE_HUB:
                    $hubrules[$rule->field] = true;
                    break;
                case dynamic_cohorts::FIELD_TYPE_USER:
                    $userrules[$rule->field] = true;
                    break;
            }
        }

        $userselect = 'u.' . implode(', u.', array_keys($userrules));
        
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
            if (array_key_exists($name, $hubrules)) {
                $alias = 'hub_' . \core_text::strtolower($name);
                $hubfields[] = "h.{$name} AS {$alias}";
            }
        }

        $hubselect = '';
        $hubjoin = '';
        if ($hubfields) {
            $hubselect = ', ' . implode(', ', $hubfields);
            // Casting to be sure of equalities.
            $castidnumber = $DB->sql_cast_char2int('u.idnumber');
            $hubjoin = "LEFT JOIN SQLHUB.ARUP_ALL_STAFF_V h ON h.EMPLOYEE_NUMBER = {$castidnumber}";
        }

        /**
         * Get all users from system
         */
        $query = "
            SELECT
              {$userselect}
              {$hubselect}
            FROM {user} u
            {$hubjoin}
            WHERE u.id > 1
            AND u.deleted = :deleted
            AND u.suspended = :suspended
        ";

        $params = [];
        $params['deleted'] = 0;
        $params['suspended'] = 0;

        $users = $DB->get_records_sql($query, $params);

        if (!empty($userrules['country'])) {
            $countries = get_string_manager()->get_list_of_countries();
            foreach($users as $user){
                $country = isset($countries[$user->country]) ? $countries[$user->country] : '';
                $user->country = $country;
            }
        }

        /**
         * Get all custom fields values
         */
        // Only include required custom fields.
        list($in, $inparams) = $DB->get_in_or_equal(array_keys($customrules), SQL_PARAMS_NAMED, 'param', true, null);
        $query = "
            SELECT
              uid.id,
              uif.shortname,
              uid.data,
              uid.userid
            FROM {user_info_field} uif 
            JOIN {user_info_data} uid ON uid.fieldid = uif.id
            JOIN {user} u ON u.id = uid.userid
            WHERE u.id > 1
            AND u.deleted = :deleted
            AND u.suspended = :suspended
            AND uif.shortname {$in}
        ";
        $userscustomfields = $DB->get_records_sql($query, array_merge($params, $inparams));
        /**
         * Assign custom fields to user array
         */
        foreach($userscustomfields as $userscustomfield){
            $var = 'custom_'.$userscustomfield->shortname;
            $users[$userscustomfield->userid]->$var = $userscustomfield->data;
        }
        /**
         * Get only dynamic cohorts
         */
        $cohorts = $DB->get_records('wa_cohort');
        foreach($cohorts as $cohort){
            $addedusers = 0;
            $removedusers = 0;
            mtrace("Check Cohort ID: ".$cohort->cohortid);
            /**
             * Get rulesets with rules
             */
            $rulesets = dynamic_cohorts::get_rulesets($cohort->cohortid);
            /**
             * Get current cohort members
             */
            $members = $DB->get_records_menu('cohort_members', ['cohortid' => $cohort->cohortid], '', 'userid');
            foreach($users as $user){
                foreach($rulesets as $ruleset){
                    /**
                     * Check if user meets ruleset rules
                     */
                    $add = $this->meets_ruleset($ruleset, $user);
                    
                    /**
                     * If ruleset criteria are meet and operator beetwen ruleset is OR then leave foreach and add user
                     */
                    if($add && $cohort->operator == dynamic_cohorts::OPERATOR_OR){
                        break;
                    }

                    /**
                     * If ruleset criteria are not meet and operator beetwen ruleset is AND then leave foreach and remove user if exists
                     */
                    if(!$add && $cohort->operator == dynamic_cohorts::OPERATOR_AND){
                        break;
                    }
                }
                if($add){
                    if(!array_key_exists($user->id, $members) && $cohort->memberadd == 1){
                        if($actionscounter >= $maxactions){
                            break;
                        }
                        mtrace("Added user: ".$user->id);
                        $addedusers++;
                        \cohort_add_member($cohort->cohortid, $user->id);
                        $actionscounter++;
                    }
                }else{
                    if(array_key_exists($user->id, $members) && $cohort->memberremove == 1){
                        if($actionscounter >= $maxactions){
                            break;
                        }
                        mtrace("Remove user: ".$user->id);
                        $removedusers++;
                        \cohort_remove_member($cohort->cohortid, $user->id);
                        $actionscounter++;
                    }
                }
            }
            // Remove any suspended/deleted users.
            $removeuserscountsql = "SELECT COUNT(u.id) FROM {cohort_members} cm JOIN {user} u ON u.id = cm.userid WHERE cm.cohortid = :cohortid AND (u.deleted = 1 OR u.suspended = 1)";
            $removedusers += $suspendedusers = $DB->count_records_sql($removeuserscountsql, ['cohortid' => $cohort->cohortid]);
            $removeusersselect = "userid IN (SELECT u.id FROM {cohort_members} cm JOIN {user} u ON u.id = cm.userid WHERE cm.cohortid = :cohortid AND (u.deleted = 1 OR u.suspended = 1))";
            $DB->delete_records_select('cohort_members', $removeusersselect, ['cohortid' => $cohort->cohortid]);
            
            $removeduserstext = "Removed users: {$removedusers}";

            if ($suspendedusers) {
                $removeduserstext .= " (Of which suspended/deleted users: {$suspendedusers})";
            }

            mtrace("Added users: ".$addedusers);
            mtrace($removeduserstext);
            mtrace("-------------------------------");
        }
    }

    /**
     * Check if ruleset criteria are meet
     * 
     * @param $ruleset
     * @param $user
     * @return bool
     */
    public function meets_ruleset($ruleset, $user){
        $add = true;
        foreach($ruleset->rules as $rule){
            switch ($rule->fieldtype) {
                case dynamic_cohorts::FIELD_TYPE_CUSTOM:
                    $fieldname = 'custom_' . $rule->field;
                    break;
                case dynamic_cohorts::FIELD_TYPE_HUB:
                    $fieldname = 'hub_' . \core_text::strtolower($rule->field);
                    break;
                default:
                    $fieldname = $rule->field;
                    break;
            }
            
            /**
             * If rule field is datetime type and field is not set or disabled set rule criteria as not meet
             */
            if(isset($rule->datatype) && $rule->datatype == 'datetime' && (!isset($user->$fieldname) || (int) $user->$fieldname == 0)){
                $add = false;
            }elseif($this->meets_rule($rule->criteriatype, $rule->value, isset($user->$fieldname) ? $user->$fieldname : '')){
                if($ruleset->operator == dynamic_cohorts::OPERATOR_OR){
                    $add = true;
                    break;
                }
            }else{
                $add = false;
            }
        }
        return $add;
    }

    /**
     * Check if rule criteria is meet
     * @param $criteriatype 
     * @param $value rule value
     * @param $field field that need to be checked for value
     * @return bool
     */
    public function meets_rule($criteriatype, $value, $field){
        switch ($criteriatype){
            case dynamic_cohorts::CRITERIA_TYPE_CONTAIN:
                return preg_match('/'.preg_quote($value).'/is', $field);
                break;
            case dynamic_cohorts::CRITERIA_TYPE_DOES_NOT_CONTAIN:
                return preg_match('/^((?!'.preg_quote($value).').)*$/is', $field);
                break;
            case dynamic_cohorts::CRITERIA_TYPE_IS_EQUAL_TO:
                return ($value == $field);
                break;
            case dynamic_cohorts::CRITERIA_TYPE_STARTS_WITH:
                return preg_match('/^'.preg_quote($value).'/is', $field);
                break;
            case dynamic_cohorts::CRITERIA_TYPE_ENDS_WITH:
                return preg_match('/'.preg_quote($value).'$/is', $field);
                break;
            case dynamic_cohorts::CRITERIA_TYPE_IS_EMPTY:
                return ($value == '');
                break;
            case dynamic_cohorts::CRITERIA_TYPE_IS_NOT_EQUAL_TO:
                return ($value != $field);
                break;
            case dynamic_cohorts::CRITERIA_TYPE_IS_CHECKED:
                return ($field == 1);
                break;
            case dynamic_cohorts::CRITERIA_TYPE_IS_NOT_CHECKED:
                return ($field == 0);
                break;
            case dynamic_cohorts::CRITERIA_TYPE_IS_BEFORE:
                return (int) $field < (int) $value;
                break;
            case dynamic_cohorts::CRITERIA_TYPE_IS_AFTER:
                return (int) $field > (int) $value;
                break;
        }
    }
}
