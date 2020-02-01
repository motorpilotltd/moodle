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

// Learning path statuses.
define('WA_LEARNING_PATH_DELETED', 0);
define('WA_LEARNING_PATH_DRAFT', 1);
define('WA_LEARNING_PATH_PUBLISH', 2);
define('WA_LEARNING_PATH_PUBLISH_NOT_VISIBLE', 3);

define('WA_LEARNING_PATH_ESSENTIAL', 1);
define('WA_LEARNING_PATH_RECOMMENDED', 2);
define('WA_LEARNING_PATH_ELECTIVE', 3);

define('WA_LEARNING_PATH_CUSTOM_FIELD_LEVEL', 'level');
define('WA_LEARNING_PATH_CUSTOM_FIELD_METHODOLOGY', 'methodology');
define('WA_LEARNING_PATH_CUSTOM_FIELD_COURSE_ICON', 'learningpathicon');
define('WA_LEARNING_PATH_CUSTOM_FIELD_702010', '702010');

/**
 * Learning path model
 *
 * @package     local_wa_cad_integration
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */
class learningpath {

    //'local_wa_learning_path', 'image'

    const FILE_COMPONENT = 'local_wa_learning_path';
    const FILE_AREA = 'image';
    const VIEW_LIST = 'list';
    const VIEW_TILES = 'tiles';
    const SUBSCRIBE_ACTIVE = 1;
    const SUBSCRIBE_INACTIVE = 0;

    public static $positions = array('essential', 'recommended', 'elective');

    /**
     * Get list of published Lerning Paths
     * @global resource $DB
     * @return type
     */
    public static function get_published_list($userid = null, $region = null, $extrasql = '', $params = array(), $count = false) {
        global $DB, $USER, $CFG;

        require_once("$CFG->dirroot/local/wa_learning_path/lib.php");

        if (is_null($userid)) {
            $userid = $USER->id;
        }

        $where = '';

//        if ($extrasql) {
//
//        }

        if(!is_null($region)) {
            if($region > 0) {
                $where = " AND (EXISTS (SELECT id
                                FROM {wa_learning_path_region} lpr
                                WHERE lpr.learningpathid = lp.id AND (lpr.regionid = :region OR lpr.regionid = :global) )
                          ) ";
            } else if ($region == 0) {
                $where = " AND (EXISTS (SELECT id
                                FROM {wa_learning_path_region} lpr
                                WHERE lpr.learningpathid = lp.id AND lpr.regionid = :global )
                          ) ";
            } else {
                $where = '';
            }
        }
        $params['status'] = WA_LEARNING_PATH_PUBLISH;
        $params['userid'] = (int) $userid;
        $params['global'] = 0;
        $params['region'] = (int) $region;
        $params['preview'] = 0;
        $params['s_userid'] = (int) $userid;
        $params['s_status'] = self::SUBSCRIBE_ACTIVE;

        if($count) {
            $sql = ''
                    . ' SELECT '
                    . "     COUNT(distinct lp.id) "
                    . "FROM {wa_learning_path} lp "
                    . "WHERE lp.status = :status AND lp.preview = :preview {$where}";

            return $DB->count_records_sql($sql , $params);
        }
        // SQL query.
        $sql = "SELECT DISTINCT lp.*, CASE WHEN s.id IS NULL THEN 0 ELSE 1 END as subscribed
                FROM {wa_learning_path} lp
                LEFT JOIN {wa_learning_path_subscribe} s on s.learningpathid = lp.id AND s.userid = :s_userid AND s.status = :s_status
                WHERE
                    lp.status = :status AND lp.preview = :preview {$where}
                ORDER BY subscribed DESC, title ASC";

        $records = $DB->get_records_sql($sql, $params);

        $ids = array();
        foreach ($records as &$p) {
            $p->regions_names = array();
            $ids[] = $p->id;
        }

        if ($ids) {
            list($usql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'learningpathid');
            $sql = ""
                    . "SELECT lr.id, lr.regionid, r.name, lr.learningpathid "
                    . "FROM {wa_learning_path_region} lr "
                    . "LEFT JOIN {local_regions_reg} r ON lr.regionid = r.id "
                    . "WHERE learningpathid ";

            $regions = $DB->get_records_sql($sql . $usql, $params);
            if (!empty($regions)) {
                foreach ($regions as $region) {
                    $records[$region->learningpathid]->regions_names[] = is_null($region->name) ? get_string('global',
                                    'local_wa_learning_path') : $region->name;
                }
            }
        }

        return $records;
    }

    public static function count_published_list($userid = null,  $region = null, $extrasql = '', $params = array()) {
        // SQL query.
        return self::get_published_list($userid, $region, $extrasql, $params, true);
    }

    /**
     * Get list of Learning Paths
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
            $params = array(), $count = false) {

        global $DB;

        $where = "WHERE c.status != " . WA_LEARNING_PATH_DELETED . ' and preview = 0 ';

        if ($extrasql) {
            $where .= " AND $extrasql";
        }

        if ($sort) {
            $sort = " ORDER BY $sort $dir";
        }

        // Query DB.
        if (!$count) {
            // SQL query.
            $sql = ''
                    . ' SELECT distinct c.*, c.title as lp_title, cc.name as category
                    FROM {wa_learning_path} c
                    LEFT JOIN {course_categories} cc on c.category = cc.id
                    LEFT JOIN {wa_learning_path_region} lr on lr.learningpathid = c.id
                    ';

            $rs = $DB->get_records_sql($sql . $where . $sort, $params, $page, $recordsperpage);

            $ids = array();
            foreach ($rs as &$p) {
                $ids[] = $p->id;
                $p->regionsname = array();
                $p->regionsid = array();

                if ($p->status == WA_LEARNING_PATH_DRAFT) {
                    $p->status_text = get_string('draft', 'local_wa_learning_path');
                } elseif ($p->status == WA_LEARNING_PATH_PUBLISH_NOT_VISIBLE) {
                    $p->status_text = get_string('publish_not_visible', 'local_wa_learning_path');
                } else {
                    $p->status_text = get_string('publish', 'local_wa_learning_path');
                }
            }

            if ($ids) {
                list($usql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'learningpathid');
                $sql = ""
                        . "SELECT lr.id, lr.regionid, r.name, lr.learningpathid "
                        . "FROM {wa_learning_path_region} lr "
                        . "LEFT JOIN {local_regions_reg} r ON lr.regionid = r.id "
                        . "WHERE learningpathid ";

                $regions = $DB->get_records_sql($sql . $usql, $params);
                if (!empty($regions)) {
                    foreach ($regions as $region) {
                        $rs[$region->learningpathid]->regionsname[] = is_null($region->name) ? get_string('global',
                                        'local_wa_learning_path') : $region->name;
                        $rs[$region->learningpathid]->regionsid[] = $region->regionid;
                    }
                }
            }
        } else {
            // SQL query.
            $sql = ''
                    . ' SELECT '
                    . "     COUNT(distinct c.id)"
                    . "FROM {wa_learning_path} c "
                    . "LEFT JOIN {wa_learning_path_region} lr on lr.learningpathid = c.id ";

            return $DB->count_records_sql($sql . $where, $params);
        }

        return $rs;
    }

    /**
     * Count Learning Paths.
     * @param type $extrasql
     * @param type $extraparams
     * @return type
     */
    public static function count($extrasql = '', $extraparams = array()) {
        return self::get_list('', '', 0, 0, $extrasql, $extraparams, true);
    }

    /**
     * Get Learning Path data
     * @global resource $DB
     * @global \wa_learning_path\model\type $CFG
     * @param Integer ID of Learning Path
     * @return stdClass Learning Path data.
     */
    public static function get($id) {
        global $DB;
        if (empty($id)) {
            return false;
        }

        $params = array(
            'id' => (int) $id,
        );

        $record = $DB->get_record('wa_learning_path', $params);

        if (!empty($record)) {
            $record->subscribed = 0;
            $record->keywords_list = self::prepare_keywords_list($record->keywords);
            $record->regions = $DB->get_records('wa_learning_path_region', array('learningpathid' => $record->id));
            $record->region = array();
            foreach ($record->regions as $r) {
                $record->region[] = $r->regionid;
            }
            unset($record->regions);
        }
        return $record;
    }

    /**
     * Preparation keywords list
     * @param String Keywords data
     * @return Array List of keywords.
     */
    public static function prepare_keywords_list($keywords) {
        $keywords_list = array();

        if (trim($keywords) != '') {
            $tmp = explode(',', $keywords);
            foreach ($tmp as $k) {
                if (trim($k) != '') {
                    $keywords_list[] = trim($k);
                }
            }
        }
        return $keywords_list;
    }

    /**
     * Check if usere have access and get Learning Path data
     * @global resource $DB
     * @global \wa_learning_path\model\type $CFG
     * @param Integer ID of Learning Path
     * @return stdClass Learning Path data.
     */
    public static function check_and_get($id, $userid = null, $ispreview = false) {
        global $DB, $USER;
        if (empty($id)) {
            return false;
        }

        if (empty($userid)) {
            $userid = $USER->id;
        }

        $statussql = "";

        if (\wa_learning_path\lib\is_contenteditor()) {
            // We let content editors view learning paths of any status
        } else if (\wa_learning_path\lib\has_capability('viewdraftlearningpath')) {
            // Can also view DRAFT learning paths.
            list ($statustmp, $params) = $DB->get_in_or_equal(array(WA_LEARNING_PATH_PUBLISH, WA_LEARNING_PATH_PUBLISH_NOT_VISIBLE, WA_LEARNING_PATH_DRAFT), SQL_PARAMS_NAMED);
            $statussql = " AND  lp.status {$statustmp} ";
        } else {
            // Can only view published (can be invisible) learning paths.
            list ($statustmp, $params) = $DB->get_in_or_equal(array(WA_LEARNING_PATH_PUBLISH, WA_LEARNING_PATH_PUBLISH_NOT_VISIBLE), SQL_PARAMS_NAMED);
            $statussql = " AND  lp.status {$statustmp} ";
        }


        if (!\wa_learning_path\lib\has_capability('addlearningpath') &&
            !\wa_learning_path\lib\has_capability('deletelearningpath') &&
            !\wa_learning_path\lib\has_capability('editlearningmatrix') &&
            !\wa_learning_path\lib\has_capability('editmatrixgrid') &&
            !\wa_learning_path\lib\has_capability('deleteactivity') &&
            !\wa_learning_path\lib\has_capability('publishlearningpath')) {
            $statussql .= " AND lp.status != :deleted ";
            $params['deleted'] = WA_LEARNING_PATH_DELETED;
        }

        $params['id'] = (int) $id;
        $params['userid'] = (int) $userid;
        $params['global'] = 0;
        $params['s_userid'] = (int) $userid;
        $params['preview'] = 0;
        $params['s_status'] = self::SUBSCRIBE_ACTIVE;


        $sql = "SELECT lp.*, CASE WHEN s.id IS NULL THEN 0 ELSE 1 END as subscribed
                FROM {wa_learning_path} lp
                LEFT JOIN {wa_learning_path_subscribe} s on s.learningpathid = lp.id AND s.userid = :s_userid AND s.status = :s_status
                WHERE lp.id = :id {$statussql} AND preview = :preview ";

        $record = $DB->get_record_sql($sql, $params);

        if (!empty($record)) {
            $record->keywords_list = self::prepare_keywords_list($record->keywords);
            $record->regions = $DB->get_records('wa_learning_path_region', array('learningpathid' => $record->id));
            $record->region = array();
            foreach ($record->regions as $r) {
                $record->region[$r->regionid] = $r->regionid;
            }
            unset($record->regions);
        }

        return $record;
    }

    /**
     * Gets Learning payh image/url to image
     * @global \wa_learning_path\model\type $CFG
     * @param Int Learning Path ID
     * @param Boolen If true returns  HTML (img) element, else return url.
     * @return String
     */
    public static function get_image_url($id, $img = false) {
        global $CFG;

        require_once($CFG->libdir . '/filestorage/file_storage.php');
        require_once($CFG->dirroot . '/course/lib.php');

        $fs = \get_file_storage();
        $context = \context_system::instance();
        $files = $fs->get_area_files(
                $context->id, \wa_learning_path\model\learningpath::FILE_COMPONENT,
                \wa_learning_path\model\learningpath::FILE_AREA, (int) $id, 'filename', false);

        if (empty($files) && $img) {
            return \html_writer::empty_tag('img',
                            array('src' => new \moodle_url('/local/wa_learning_path/pix/default.svg'), 'class' => 'learning_path_image'));
        }

        if (empty($files)) {
            return '';
        }
        // Should be only one file!
        $file = reset($files);
        $isimage = $file->is_valid_image();

        $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $id . '/' . $file->get_filename(), !$isimage);
        if ($img) {
            return \html_writer::empty_tag('img', array('src' => $url, 'class' => 'learning_path_image'));
        } else {
            return $url;
        }
    }

    /**
     * Set status of Learning Path
     * @global resource $DB
     * @param type $id
     * @param type $status
     */
    public static function set_status($id, $status) {
        global $DB;
        $lp = self::get($id);
        if ($lp) {
            $lp->status = $status;
            $DB->update_record('wa_learning_path', $lp);
        }
    }

    /**
     * Set path matrix of Learning Path
     * @global resource $DB
     * @param type $id
     * @param type $status
     */
    public static function set_matrix($id, $matrix) {
        global $DB;
        $lp = self::get($id);
        if ($lp) {
            $lp->matrix = $matrix;
            $DB->update_record('wa_learning_path', $lp);
        }
    }

    /**
     * Remove record.
     * @global resource $DB
     * @return bool
     */
    public static function delete($learningpath = null) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/local/wa_learning_path/classes/event/learning_path_deleted.php');

        if (!is_object($learningpath)) {
            $learningpath = self::get((int) $learningpath);
        }

        $learningpath->status = WA_LEARNING_PATH_DELETED;

        $DB->update_record('wa_learning_path', $learningpath);

        $event = \local_wa_learning_path\event\learning_path_deleted::create(array(
                    'objectid' => $learningpath->id,
                    'context' => \context_system::instance()
        ));
        $event->trigger();

        return false;
    }

    public static function delete_preview($preview) {
        global $DB;
        $sql = "delete from {wa_learning_path} where preview = " . (int) $preview;
        $DB->execute($sql);
    }

    /**
     * Save model data.
     * @global resource $DB
     * @param type $data
     * @return boolean
     */
    public static function create($data, $preview = false) {
        global $DB, $CFG;

        $table = 'wa_learning_path';

        require_once($CFG->dirroot . '/local/wa_learning_path/classes/event/learning_path_created.php');
        require_once($CFG->dirroot . '/local/wa_learning_path/classes/event/learning_path_updated.php');

//        if (isset($data->content_editor)) {
//            $data->introduction = $data->content_editor['text'];
//            $data->format = $data->content_editor['format'];
//            $data->itemid = $data->content_editor['itemid'];
//        }
//
//        if (isset($data->content_editor)) {
//            $data->introduction = $data->introduction_editor['text'];
//            $data->format = $data->introduction_editor['format'];
//            $data->itemid = $data->introduction_editor['itemid'];
//        } else {
//            if (!$data->introduction) {
//                $data->introduction = '';
//                $data->format = 1;
//                $data->itemid = 0;
//            }
//        }

        if (!isset($data->introduction)) {
            $data->introduction = '';
            $data->format = 1;
            $data->itemid = 0;
        }

        $data->preview = $preview;

        if (empty($data->id) || $preview) {
            // New Learning path
            $data->timecreated = time();
            if (!isset($data->matrix)) {
                $data->matrix = '';
            }

            // Remove last preview record.
            if ($preview) {
                self::delete_preview($preview);
            }

            $id = $DB->insert_record($table, $data, true);
            $DB->delete_records('wa_learning_path_region', array('learningpathid' => $id));

            foreach ($data->region as $regionid) {
                $r = new \stdClass();
                $r->learningpathid = $id;
                $r->regionid = $regionid;
                $DB->insert_record('wa_learning_path_region', $r, true);
            }

            if (!$preview) {
                $event = \local_wa_learning_path\event\learning_path_created::create(array(
                            'objectid' => $id,
                            'context' => \context_system::instance(),
                            'other' => array(
                                'title' => $data->title,
                                'region' => $data->region,
                                'category' => $data->category,
                                'status' => $data->status,
                            ),
                ));
                $event->trigger();
            }
        } else {
            // Update Learning path
            $DB->update_record($table, $data);
            $id = $data->id;

            $DB->delete_records('wa_learning_path_region', array('learningpathid' => $id));

            foreach ($data->region as $regionid) {
                $r = new \stdClass();
                $r->learningpathid = $id;
                $r->regionid = $regionid;
                $DB->insert_record('wa_learning_path_region', $r, true);
            }

            if (!$preview) {
                $event = \local_wa_learning_path\event\learning_path_updated::create(array(
                            'objectid' => $id,
                            'context' => \context_system::instance(),
                            'other' => array(
                                'title' => $data->title,
                                'region' => $data->region,
                                'category' => $data->category,
                                'status' => $data->status,
                            ),
                ));
                $event->trigger();
            }
        }

        return $id;
    }

    /**
     * Setup default Learning paths view
     *
     * @global \wa_learning_path\model\type $SESSION
     * @param type $mode
     */
    public static function setup_default_view($mode) {
        global $USER, $DB;

        if ($mode != self::VIEW_LIST && $mode != self::VIEW_TILES) {
            $mode = self::VIEW_LIST;
        }

        $usermode = \wa_learning_path\lib\create_profile_field('learningpathdefultview',
                'Learning Path default view of list', 0,
                "Learners will be able to select a list view of the learning paths by clicking on the 'List view' hyperlink on the top right of the screen. At launch, this view will be the default view.");

        $data = $DB->get_record('user_info_data', array('userid' => $USER->id, 'fieldid' => $usermode->id));

        if (empty($data)) {
            $data = new \stdClass();
            $data->fieldid = $usermode->id;
            $data->userid = $USER->id;
            $data->data = $mode;
            $data->dataformat = 0;

            $DB->insert_record('user_info_data', $data);
        } else {
            $data->data = $mode;

            $DB->update_record('user_info_data', $data);
        }

        if (empty($USER->profile)) {
            $USER->profile = array();
        }

        $USER->profile['learningpathdefultview'] = $mode;
    }

    /**
     * Get default Learning paths view
     * @global type $SESSION
     * @return Int
     */
    public static function get_default_view() {
        global $USER;

        if (empty($USER->profile)) {
            return self::VIEW_LIST;
        }

        return empty($USER->profile['learningpathdefultview']) ? self::VIEW_LIST : $USER->profile['learningpathdefultview'];
    }

    /**
     * Subscribe user into Learning path
     * @global type $USER
     * @global resource $DB
     * @param Int Learning path ID
     * @param Int User ID
     * @return Int Inserted ID or false
     */
    public static function subscribe($id, $userid = null) {
        global $USER, $DB;

        if (empty($id)) {
            return false;
        }

        if (is_null($userid)) {
            $userid = (int) $USER->id;
        }

        $exists = $DB->get_record('wa_learning_path_subscribe',
                array('learningpathid' => $id, 'userid' => $userid, 'status' => self::SUBSCRIBE_ACTIVE));

        if (!empty($exists)) {
            return $exists->id;
        }

        $subscribe = new \stdClass();
        $subscribe->learningpathid = (int) $id;
        $subscribe->userid = (int) $userid;
        $subscribe->status = (int) self::SUBSCRIBE_ACTIVE;
        $subscribe->timecreated = (int) time();
        $subscribe->timeupdated = $subscribe->timecreated;

        $subscribeid = $DB->insert_record('wa_learning_path_subscribe', $subscribe, true);

        return $subscribeid;
    }

    /**
     * unsubscribe user from Learning path
     * @global type $USER
     * @global resource $DB
     * @param Int Learning path ID
     * @param Int User ID
     * @return Int Inserted ID or false
     */
    public static function unsubscribe($id, $userid = null) {
        global $USER, $DB;

        if (empty($id)) {
            return false;
        }

        if (is_null($userid)) {
            $userid = (int) $USER->id;
        }

        return $DB->delete_records('wa_learning_path_subscribe',
                        array('learningpathid' => $id, 'userid' => $userid, 'status' => self::SUBSCRIBE_ACTIVE));
    }

    /**
     * Fill the modules and activites with all required data like name.
     * Alse checks if module (course) is visible
     * @param $activities
     * @param bool $courses
     * @param bool $activities_list
     * @param bool $regions
     * @return mixed
     * @throws \coding_exception
     */
    public static function fill_activities($activities, $courses = false, $activities_list = false, $regions = false,
            $tab = false, $completioninfo = false) {
        global $DB, $USER, $CFG;

        require_once("$CFG->libdir/completionlib.php");

        if (!$regions) {
            $regions = \wa_learning_path\lib\get_regions();
        }

        if (empty($activities)) {
            return array();
        }

        $coursesids = $activitiesids = array();
        foreach ($activities as &$activity) {
            if (isset($activity->positions)) {
                foreach ($activity->positions as $type => &$positions) {
                    if (!$tab || $type == $tab) {
                        foreach ($positions as &$position) {
                            if ($position->type == 'module') {
                                $coursesids[] = (int) $position->id;
                            }

                            if ($position->type == 'activity') {
                                $activitiesids[] = (int) $position->id;
                            }
                        }
                    }
                }
            }
        }

        if (!$courses) {
            $courses = \wa_learning_path\lib\get_modules($coursesids, $completioninfo);
        }

        if (!$activities_list) {
            if ($activitiesids) {
                list($usql, $params) = $DB->get_in_or_equal($activitiesids, SQL_PARAMS_NAMED, 'id');
                $usql = ' a.id ' . $usql;
            } else {
                $params = array();
                $usql = '';
            }

            $activities_list = \wa_learning_path\model\activity::get_list('title', 'asc', 0, 99999, $usql, $params,
                            false, true);
        }

        if ($completioninfo) {
            $enrolled = enrol_get_all_users_courses($USER->id, true);
        }

        foreach ($activities as &$activity) {
            $activity->activities_count = $activity->modules_count = 0;
            $activity->in_progress = false;
            if (isset($activity->positions)) {
                foreach ($activity->positions as $type => &$positions) {
                    if (!$tab || $type == $tab) {
                        foreach ($positions as &$position) {
                            if ($position->type == 'module') {
                                if (isset($courses[$position->id])) {
                                    $activity->modules_count++;
                                    $position->fullname = $courses[$position->id]->fullname;
                                    $position->description = $courses[$position->id]->summary;
                                    $position->summaryformat = $courses[$position->id]->summaryformat;
                                    $position->percent = isset($courses[$position->id]->p702010) ? $courses[$position->id]->p702010
                                                : '';
                                    $position->methodology = isset($courses[$position->id]->methodology) ? $courses[$position->id]->methodology
                                                : '';
									$position->methodologyicon = $position->methodology ? \wa_learning_path\lib\get_course_methodologie_icon((int) $position->id)
												: '';
                                    $position->region = isset($courses[$position->id]->regionids) ? $courses[$position->id]->regionids
                                                : array(0 => '0');
                                    $position->completed = false;
                                    if ($completioninfo) {
                                        $position->enrolled = isset($enrolled[$courses[$position->id]->id]);
                                        $completed = isset($courses[$position->id]->timestarted) ? $courses[$position->id]->timestarted
                                                    : '0';
                                        $position->completed = $completed && $position->enrolled;
                                        if ($position->enrolled) {
                                            $activity->in_progress = true;
                                        }
                                    }
                                    if (isset($position->regionid) && isset($regions[$position->regionid])) {
                                        $position->fullname .= ' (' . get_string('region', 'local_wa_learning_path') . ' ' . $regions[$position->regionid] . ')';
                                    }
                                } else {
                                    $position->id = '';
                                }
                            }

                            if ($position->type == 'activity') {
                                if (isset($activities_list[$position->id])) {
                                    $activity->activities_count++;
                                    $position->title = $activities_list[$position->id]->title;
                                    $position->description = $activities_list[$position->id]->description;
                                    $position->methodology = $activities_list[$position->id]->type;
                                    $position->completed = !empty($activities_list[$position->id]->completedtime);
                                    $position->region = empty($activities_list[$position->id]->regionids) ? array() : $activities_list[$position->id]->regionids;
                                    if (isset($position->regionid) && $regions[$position->regionid]) {
                                        $position->title .= ' (' . get_string('region', 'local_wa_learning_path') . ' ' . $regions[$position->regionid] . ')';
                                    }
                                    if ($completioninfo && $position->completed) {
                                        $activity->in_progress = true;
                                    }
                                } else {
                                    $position->id = '';
                                }
                            }
                        }
                    }
                }

                $activity->count = $activity->modules_count + $activity->activities_count;
            }
        }

        return $activities;
    }

    public static function check_regions_match($selectedregions, $availableregions) {

        if (empty($selectedregions)) {
            // If empty all regions should be visible.
            return true;
        }

        $availableregions = is_null($availableregions) ? array() : $availableregions;

        // If exists region Global - is visible.
        if (array_search(0, $availableregions) !== false) {
            return true;
        }

        foreach ($selectedregions as $s_region) {
            // If region exists - element is visible.
            if (array_search($s_region, $availableregions) !== false) {
                return true;
            }
        }

        return false;
    }

    public static function count_visible_rows($matrix, $regions) {
        $count = 0;

        if (empty($matrix->cols)) {
            return $count;
        }

        foreach ($matrix->cols as $col) {
            if (!$col->show || !self::check_regions_match($regions, $col->region)) {
                continue;
            }
            $count++;
        }

        return $count;
    }

    public static function get_cell_labels($key, $matrix) {
        if (empty($matrix)) return array('', '');
        $key = str_replace('#', '', $key);
        $r_label = '';
        $c_label = '';

        $data = explode('_', $key);
        if (!count($data)) {
            return array('', '');
        }

        if ($matrix->rows && isset($data[1])) {
            foreach ($matrix->rows as $row) {
                if ($row->id == $data[1]) {
                    $r_label = $row->name;
                }
            }
        }

        if ($matrix->cols && isset($data[0])) {
            foreach ($matrix->cols as $col) {
                if ($col->id == $data[0]) {
                    $c_label = $col->name;
                }
            }
        }

        return array($r_label, $c_label);
    }

    /**
     * Count activities by all positions
     * @param type $positions
     * @param type $regions
     * @return type
     */
    public static function count_activities_by_positions($positions, $regions, $filtration) {
        $return = array('all' => 0);
        $current = time();
        $halfyear = YEARSECS / 2;

        foreach (self::$positions as $position) {
            $return[$position] = 0;
            if (empty($positions->{$position})) {
                continue;
            }

            foreach ($positions->{$position} as $activity) {
                if (empty($activity->id)) {
                    continue;
                }
                // Filtration.
                if (!empty($filtration['methodology']) && $activity->methodology != $filtration['methodology']) {
                    continue;
                }

                if (!empty($filtration['percent']) && $activity->percent != $filtration['percent']) {
                    continue;
                }

                // Check if region is matched.
                // -1 => All, 0 => Global, $region => selected in Left Nav.
                if (self::check_regions_match($regions, $activity->region)) {
                    $return[$position] ++;
                    $date = isset($activity->date) ? $activity->date : 0;
                    $diff = $current - $date;
                    if ($date != 0 && $diff > 0 && $diff <= $halfyear) {
                        $return['new'][$position] = 1;
                    }
                }
            }

            $return['all'] += $return[$position];
        }

        return $return;
    }

    public static function get_cell_info($cell, $regions) {
        $return = new \stdClass();
        $return->modules_count = 0;
        $return->activities_count = 0;
        $return->in_progress = false;
        $return->objectives_defined = false;

        $positions = isset($cell->positions) ? $cell->positions : array();
        $content = trim($cell->content);
        if ($content != '') {
            $return->objectives_defined = true;
        }

        foreach (self::$positions as $position) {
            if (empty($positions->{$position})) {
                continue;
            }

            foreach ($positions->{$position} as $activity) {
                if (empty($activity->id)) {
                    continue;
                }

                // -1 => All, 0 => Global, $region => selected in Left Nav.
                if (self::check_regions_match($regions, $activity->region)) {
                    if ($activity->type == 'module') {
                        $return->modules_count++;
                        if (isset($activity->enrolled) && $activity->enrolled) {
                            $return->in_progress = true;
                        }
                    }
                    if ($activity->type == 'activity') {
                        $return->activities_count++;
                        if (isset($activity->completed) && $activity->completed) {
                            $return->in_progress = true;
                        }
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Release all activities releated with learning path.
     * @param $id
     */
    public static function release_activities($id) {
        global $DB;
        $sql = "update {wa_learning_path_activity} set idlearningpath = 0 where idlearningpath = " . (int) $id;
        $DB->execute($sql);
    }

    /**
     * Link the activities to the learning path.
     * @param $id
     * @param $ids
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function link_activities_to_learning_path($id, $ids) {
        global $DB;
        list($usql, $params) = $DB->get_in_or_equal(array_unique($ids), SQL_PARAMS_NAMED, 'id');

        $sql = "update {wa_learning_path_activity} set idlearningpath = " . (int) $id . " where id " . $usql;
        $DB->execute($sql, $params);
    }

}
