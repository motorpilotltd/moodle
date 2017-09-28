<?php

/**
 * Model
 * 
 * @package     wa_learning_path
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */

namespace wa_learning_path\model;

/**
 * Activity model
 * 
 * @package     local_wa_cad_integration
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */
class activity {

//    Activity Types:
    const TYPE_VIDEO = 'video';
    const TYPE_TEXT = 'text';

    /**
     * Get list of Activitys 
     * @global type $CFG
     * @global resource $DB
     * @param type $sort
     * @param type $dir
     * @param type $page
     * @param type $recordsperpage
     * @param type $extrasql
     * @param type $params
     * @param type $count
     * @return type
     */
    public static function get_list($sort = 'title', $dir = 'ASC', $page = 0, $recordsperpage = 0, $extrasql = '',
            $params = array(), $count = false, $completioninfo = false) {

        global $DB, $USER;

        $where = "WHERE 1=1 ";

        if ($extrasql) {
            $where .= " AND $extrasql";
        }

        if ($sort) {
            $sort = " ORDER BY $sort $dir";
        }
        
        $completionsql = '';
        $join = '';
        
        if($completioninfo) {
            $params['userid'] = (int) $USER->id;
            $completionsql = ' , ac.timecreated as completedtime ';
            $join = 'LEFT JOIN {wa_learning_path_a_com} ac ON a.id = ac.activityid AND ac.userid = :userid';
        }
        
        // Query DB.
        if (!$count) {
            // SQL query.
            $sql = ''
                    . " SELECT DISTINCT a.*, lp.title as learningpath {$completionsql}
                        FROM {wa_learning_path_activity} a "
                    . "LEFT JOIN {wa_learning_path} lp ON lp.id = a.idlearningpath "
                    . "LEFT JOIN {wa_learning_path_act_region} ar ON ar.activityid = a.id "
                        . " {$join} ";
                    

            $rs = $DB->get_records_sql($sql . $where . $sort, $params, $page, $recordsperpage);
            
            $ids = array();
            foreach($rs as &$r) {
                $ids[] = $r->id;
                $r->regionsname = array();
                $r->regionids = array();
            }
            
            if ($ids) {
                list($usql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'activityid');
                $sql = ""
                        . "SELECT ar.id, ar.regionid, r.name, ar.activityid "
                        . "FROM {wa_learning_path_act_region} ar "
                        . "LEFT JOIN {local_regions_reg} r ON ar.regionid = r.id "
                        . "WHERE activityid ";

                $regions = $DB->get_records_sql($sql . $usql, $params);
                if (!empty($regions)) {
                    foreach ($regions as $region) {
                        $rs[$region->activityid]->regionsname[] = is_null($region->name) ? get_string('global',
                                        'local_wa_learning_path') : $region->name;
                        $rs[$region->activityid]->regionids[] = $region->regionid;
                    }
                }
            }
        } else {
            // SQL query.
            $sql = ''
                    . ' SELECT '
                    . "     COUNT(distinct a.id)"
                    . "FROM {wa_learning_path_activity} a "
                    . "LEFT JOIN {wa_learning_path_act_region} ar ON ar.activityid = a.id ";

            return $DB->count_records_sql($sql . $where, $params);
        }
        
        return $rs;
    }

    /**
     * Count Activitys.
     * @param type $extrasql
     * @param type $extraparams
     * @return type
     */
    public static function count($extrasql = '', $extraparams = array()) {
        return self::get_list('', '', 0, 0, $extrasql, $extraparams, true);
    }

    /**
     * Get Activity data
     * @global resource $DB
     * @global \wa_learning_path\model\type $CFG
     * @param Integer ID of Activity
     * @return stdClass Activity data.
     */
    public static function get($id) {
        global $DB;
        if (empty($id)) {
            return false;
        }

        $record = $DB->get_record('wa_learning_path_activity', array('id' => (int) $id));

        if($record) {
            $sql = ""
                    . "SELECT ar.id, ar.regionid, r.name, ar.activityid "
                    . "FROM {wa_learning_path_act_region} ar "
                    . "LEFT JOIN {local_regions_reg} r ON ar.regionid = r.id "
                    . "WHERE activityid = :activityid";

            $regions = $DB->get_records_sql($sql, array('activityid' => (int) $id));
            
            if (!empty($regions)) {
                foreach ($regions as $region) {
                    $record->region[] = $region->regionid;
                    $record->regionsname[] = is_null($region->name) ? get_string('global',
                                    'local_wa_learning_path') : $region->name;
                }
            }
        }
        return $record;
    }

    /**
     * Remove record.
     * @global resource $DB
     * @return bool
     */
    public static function delete($id) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/local/wa_learning_path/classes/event/activity_deleted.php');

        $status = $DB->delete_records('wa_learning_path_activity', array('id' => (int) $id));
        
        if($status) {
            $event = \local_wa_learning_path\event\activity_deleted::create(array(
                'objectid' => $id,
                'context' => \context_system::instance()
            ));
            $event->trigger();
        }
        
        return $status;
    }

    /**
     * Save model data.
     * @global resource $DB
     * @param type $data
     * @return boolean
     */
    public static function create($data) {
        global $DB, $CFG;

        require_once($CFG->dirroot.'/local/wa_learning_path/classes/event/activity_created.php');
        require_once($CFG->dirroot.'/local/wa_learning_path/classes/event/activity_updated.php');
        
        if (empty($data->id)) {
            // New Activity
            $data->timecreated = time();
            $data->timeupdated = $data->timecreated;
            $regions = $data->region;
            unset($data->region);
            $id = $DB->insert_record('wa_learning_path_activity', $data, true);
            
            $event = \local_wa_learning_path\event\activity_created::create(array(
                'objectid' => $id,
                'context' => \context_system::instance(),
                'other' => array(
                    'title' => $data->title,
                    'region' => $regions,
                    'type' => $data->type,
                ),
            ));
            $event->trigger();
        } else {
            // Update Activity
            $data->timeupdated = time();
            $regions = $data->region;
            unset($data->region);
            $DB->update_record('wa_learning_path_activity', $data);
            $id = $data->id;
            
            $event = \local_wa_learning_path\event\activity_updated::create(array(
                'objectid' => $id,
                'context' => \context_system::instance(),
                'other' => array(
                    'title' => $data->title,
                    'region' => $regions,
                    'type' => $data->type,
                ),
            ));
            $event->trigger();
            
        }
        
        if($id) {
            $DB->delete_records('wa_learning_path_act_region', array('activityid' => $id));

            foreach ($regions as $regionid) {
                $r = new \stdClass();
                $r->activityid = $id;
                $r->regionid = $regionid;
                $DB->insert_record('wa_learning_path_act_region', $r, true);
            }
        }
        
        return $id;
    }
    
    /**
     * Check if usere have access and get activity data
     * @global resource $DB
     * @global \wa_learning_path\model\type $CFG
     * @param Integer ID of Activity
     * @return stdClass Activity data.
     */
    public static function check_and_get($id) {
        global $DB, $USER;
        if (empty($id)) {
            return false;
        }

        if (empty($userid)) {
            $userid = $USER->id;
        }
        
        $record = $DB->get_record('wa_learning_path_activity', array('id' => (int) $id));
        
        return $record;
    }

    /**
     * Set activity compledion data.
     * @global resource $DB
     * @global type $USER
     * @param Int Activity ID
     * @param Boolen|Int Completion info.
     * @param Int|null User ID
     * @return Int 0 -> set as incomplete, 1-> set as completed, 2-> already completed
     */
    public static function set_completion($id, $completion, $userid = null) {
        global $DB, $USER;
        if (empty($id)) {
            return false;
        }
        
        if (is_null($userid)) {
            $userid = $USER->id;
        }

        if(!empty($completion)) {
            
            $current = $DB->get_record('wa_learning_path_a_com', array('activityid' => (int) $id, 'userid' => (int) $userid));
            
            if(empty($current)) {
                $data = new \stdClass();
                $data->activityid = (int) $id;
                $data->userid = (int) $userid;
                $data->timecreated = time();
                $data->timeupdated = $data->timecreated;
                $compid = $DB->insert_record('wa_learning_path_a_com', $data, true);
                // Load activity for CPD upload.
                $activity = $DB->get_record('wa_learning_path_activity', array('id' => (int) $id));
                if ($compid && $activity && $activity->enablecdp && get_config('local_wa_learning_path', 'activate_cpd_upload')) {
                    // Save CPD record.
                    // Get staff id.
                    $staffid = $DB->get_field('user', 'idnumber', array('id' => $userid));
                    // Format learning description.
                    $taps = new \local_taps_interface();
                    $taps->add_cpd_record(
                            $staffid,
                            $activity->title,
                            $activity->provider,
                            strtoupper(date('d-M-Y')),
                            $activity->duration,
                            $activity->unit,
                            array(
                                'p_subject_catetory' => $activity->subject,
                                'p_learning_method' => $activity->learningmethod,
                                'p_learning_desc' => $activity->learningdescription,
                            )
                            );
                }

                $status = 1;
            } else{
                $status = 2;
            }
        } else {
            $DB->delete_records('wa_learning_path_a_com', array('activityid' => (int) $id));
            $status = 0;
        }

        return $status;
    }
}
