<?php
namespace local_dynamic_cohorts\task;
use local_dynamic_cohorts\dynamic_cohorts;
use local_dynamic_cohorts\aadgroups;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/cohort/lib.php');

class check_members extends \core\task\scheduled_task {

    private $users;

    private $rules;
    private $userrules;
    private $hubrules;
    private $customrules;

    // Only run 1000 actions per task.
    private $actionscounter = 0;
    private $maxactions = 1000;

    public function get_name()
    {
        // Shown in admin screens.
        return get_string('checkmembers', 'local_dynamic_cohorts');
    }

    /**
     * Add or remove members from cohort
     */
    public function execute()
    {
        global $DB;

        $this->get_users();

        /**
         * Get only dynamic cohorts
         */
        $cohorts = $DB->get_records('wa_cohort');

        foreach($cohorts as $cohort) {
            $addedusers = 0;
            $removedusers = 0;
            mtrace("Check Cohort ID: " . $cohort->cohortid);
            /**
             * Get rulesets with rules
             */
            $rulesets = dynamic_cohorts::get_rulesets($cohort->cohortid);
            /**
             * Get current cohort members
             */
            $members = $DB->get_records_menu('cohort_members', ['cohortid' => $cohort->cohortid], '', 'userid');
            foreach ($this->users as $user) {
                foreach ($rulesets as $ruleset) {
                    /**
                     * Check if user meets ruleset rules
                     */
                    $add = $this->meets_ruleset($ruleset, $user);
                    /**
                     * If ruleset criteria are meet and operator beetwen ruleset is OR then leave foreach and add user
                     */
                    if ($add && $cohort->operator == dynamic_cohorts::OPERATOR_OR) {
                        break;
                    }

                    /**
                     * If ruleset criteria are not meet and operator beetwen ruleset is AND then leave foreach and remove user if exists
                     */
                    if (!$add && $cohort->operator == dynamic_cohorts::OPERATOR_AND) {
                        break;
                    }
                }
                if ($add) {
                    if (!array_key_exists($user->id, $members) && $cohort->memberadd == 1) {
                        if($this->actionscounter >= $this->maxactions) {
                            break;
                        }
                        mtrace("Added user: " . $user->id);
                        $addedusers++;
                        \cohort_add_member($cohort->cohortid, $user->id);
                        $this->actionscounter++;
                    }
                } else {
                    if (array_key_exists($user->id, $members) && $cohort->memberremove == 1) {
                        if ($this->actionscounter >= $this->maxactions) {
                            break;
                        }
                        mtrace("Remove user: " . $user->id);
                        $removedusers++;
                        \cohort_remove_member($cohort->cohortid, $user->id);
                        $this->actionscounter++;
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

            mtrace("Added users: " . $addedusers);
            mtrace($removeduserstext);
            mtrace("-------------------------------");
        }
    }

    private function get_rules() {
        global $DB;

        // Load rules so we can optimise accordingly.
        $this->rules = $DB->get_records('wa_cohort_ruleset_rules');
        $this->userrules = [
            'id' => true,
            'idnumber' => true
        ];
        $this->hubrules = [];
        $this->customrules = [];
        foreach ($this->rules as $rule) {
            switch ($rule->fieldtype) {
                case dynamic_cohorts::FIELD_TYPE_CUSTOM:
                    $this->customrules[$rule->field] = true;
                    break;
                case dynamic_cohorts::FIELD_TYPE_HUB:
                    $this->hubrules[$rule->field] = true;
                    break;
                case dynamic_cohorts::FIELD_TYPE_USER:
                    $this->userrules[$rule->field] = true;
                    break;
            }
        }
    }

    private function get_users() {
        global $DB;

        // Get rules to optimise user loading.
        $this->get_rules();

        $userselect = 'u.' . implode(', u.', array_keys($this->userrules));

        // Open second connection as we need no prefix.
        $cfg = $DB->export_dbconfig();
        if (!isset($cfg->dboptions)) {
            $cfg->dboptions = [];
        }
        // Pretend it's external to remove prefix injection.
        $DB2 = \moodle_database::get_driver_instance($cfg->dbtype, $cfg->dblibrary, true);
        $DB2->connect($cfg->dbhost, $cfg->dbuser, $cfg->dbpass, $cfg->dbname, false, $cfg->dboptions);
        $hubfields = [];
        $columns = $DB2->get_columns('ARUP_ALL_STAFF_V');
        foreach (array_keys($columns) as $name) {
            if (array_key_exists($name, $this->hubrules)) {
                $alias = 'hub_' . \core_text::strtolower($name);
                $hubfields[] = "h.{$name} AS {$alias}";
            }
        }
        $DB2->dispose();

        $hubselect = '';
        $hubjoin = '';
        if ($hubfields) {
            $hubselect = ', ' . implode(', ', $hubfields);
            // Casting to be sure of equalities.
            $castidnumber = $DB->sql_cast_char2int('u.idnumber');
            $hubjoin = "LEFT JOIN SQLHUB.ARUP_ALL_STAFF_V h ON h.EMPLOYEE_NUMBER = {$castidnumber}";
        }

        // Get all users from system.
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

        $this->users = $DB->get_records_sql($query, $params);

        if (!empty($this->userrules['country'])) {
            $countries = get_string_manager()->get_list_of_countries();
            foreach($this->users as $user) {
                $country = isset($countries[$user->country]) ? $countries[$user->country] : '';
                $user->country = $country;
            }
        }

        // Get all custom fields values (Only include required custom fields).
        list($in, $inparams) = $DB->get_in_or_equal(array_keys($this->customrules), SQL_PARAMS_NAMED, 'param', true, null);
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

        // Assign custom fields to user array.
        foreach($userscustomfields as $userscustomfield) {
            $var = 'custom_'.$userscustomfield->shortname;
            $this->users[$userscustomfield->userid]->$var = $userscustomfield->data;
        }

        $query = "
            SELECT
              cm.id,
              cm.userid,
              cm.cohortid
            FROM {cohort_members} cm
            JOIN {user} u ON u.id = cm.userid
            WHERE u.id > 1
            AND u.deleted = :deleted
            AND u.suspended = :suspended
        ";
        $cohortmembers = $DB->get_records_sql($query, $params);

        // Assign cohorts to user array.
        foreach ($cohortmembers as $cohortmember) {
            if (!isset($this->users[$cohortmember->userid]->cohort)) {
                $this->users[$cohortmember->userid]->cohort = [];
            }
            $this->users[$cohortmember->userid]->cohort[] = $cohortmember->cohortid;
        }

        $aadgroupmembers = $this->get_aad_group_members();

        // Assign AAD groups to user array.
        foreach ($aadgroupmembers as $idnumber => $groups) {
            $userid = array_search($idnumber, array_column($this->users, 'idnumber', 'id'));
            if ($userid) {
                $this->users[$userid]->aadgroup = $groups;
            }
        }
    }

    private function get_aad_group_members() {
        global $DB;

        // Array of users (key is idnumber) and their groups.
        $return = [];

        $aadgroups = $DB->get_records_menu('wa_cohort_ruleset_rules', ['field' => 'aadgroup'], '', 'id, value');
        foreach ($aadgroups as $aadgroup) {
            $members = aadgroups::get_group_members($aadgroup);
            foreach ($members as $idnumber => $username) {
                if (!isset($return[$idnumber])) {
                    $return[$idnumber] = [];
                }
                $return[$idnumber][] = $aadgroup;
            }
        }

        return $return;
    }

    /**
     * Check if ruleset criteria are meet
     *
     * @param $ruleset
     * @param $user
     * @return bool
     */
    public function meets_ruleset($ruleset, $user) {
        $add = true;
        foreach($ruleset->rules as $rule) {
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
            if(isset($rule->datatype) && $rule->datatype == 'datetime' && (!isset($user->$fieldname) || (int) $user->$fieldname == 0)) {
                $add = false;
            }elseif($this->meets_rule($rule->criteriatype, $rule->value, isset($user->$fieldname) ? $user->$fieldname : '')) {
                if($ruleset->operator == dynamic_cohorts::OPERATOR_OR) {
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
    public function meets_rule($criteriatype, $value, $field) {
        switch ($criteriatype) {
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
            case dynamic_cohorts::CRITERIA_TYPE_IS_MEMBER:
                return in_array($value, (array)$field);
                break;
            case dynamic_cohorts::CRITERIA_TYPE_IS_NOT_MEMBER:
                return !in_array($value, (array)$field);
                break;
        }
    }
}
