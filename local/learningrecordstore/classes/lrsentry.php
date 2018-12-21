<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 07/12/2018
 * Time: 14:22
 */

namespace local_learningrecordstore;

use renderer_base;

require_once("$CFG->dirroot/completion/data_object.php");

class lrsentry extends \data_object implements \templatable {
    public $table = 'local_learningrecordstore';

    public $required_fields = ['id'];
    public $optional_fields = [
            'provider' => null,
            'healthandsafetycategory' => null,
            'location' => null,
            'providerid' => null,
            'staffid' => null,
            'duration' => null,
            'durationunits' => null,
            'completiontime' => null,
            'description' => null,
            'certificateno' => null,
            'providername' => null,
            'classcategory' => null,
            'classcost' => null,
            'classcostcurrency' => null,
            'timemodified' => null,
            'expirydate' => null,
            'classtype' => null,
            'starttime' => null,
            'endtime' => null,
            'locked' => null,
    ];

    public $provider;
    public $healthandsafetycategory;
    public $location;
    public $providerid; //(could be a classid, moodle courseid, lynda video id etc.
    public $staffid; // generally a user idnumber
    public $duration;
    public $durationunits;
    public $completiontime;
    public $description;
    public $certificateno;
    public $providername;
    public $classcategory;
    public $classcost;
    public $classcostcurrency;
    public $timemodified;
    public $expirydate;
    public $classtype;
    public $starttime;
    public $endtime;
    public $locked;

    public function generateurl() {
        switch ($this->provider) {
            case "moodle":
                return new \moodle_url('/course/view.php', array('id' => $this->providerid));
                break;
            case "Lynda.com":
                return new \moodle_url('local/lynda/launch.php', array('lyndacourseid' => $this->providerid));
                break;
            default:
                return false;
                break;
        }
    }

    public function formatexpirydate() {
        if (!empty($this->expirydate)) {
            return userdate($this->expirydate);
        } else {
            return '';
        }
    }

    public function formatduration() {
        return $data = (float) $this->duration . ' ' . \mod_tapsenrol\taps::resolvedurationunit($this->durationunits);
    }

    public function export_for_template(renderer_base $output) {
        $obj = new \stdClass();
        $obj->duration = $this->formatduration();
        $obj->coursename = format_string($this->providername);
        $obj->classtype = format_string($this->classtype);
        $obj->classcategory = format_string($this->classcategory);
        $obj->completiontime = $this->completiontime;
        $obj->expirydate = $this->expirydate;
        return $obj;
    }

    /**
     * @param array $params
     * @return self
     */
    public static function fetch($params) {
        return self::fetch_helper('local_learningrecordstore', __CLASS__, $params);
    }

    /**
     * @param string $staffid
     * @param int $timestampsince
     * @return self[]
     */
    public static function fetchbystaffid($staffid, $timestampsince = null) {
        global $DB;

        $where = "staffid = :staffid";
        $params = ['staffid' => $staffid];

        if (isset($timestampsince)) {
            $where .= " AND completiontime >= :timestampsince";
            $params['timestampsince'] = $timestampsince;
        }

        if ($datas = $DB->get_records_select('local_learningrecordstore', $where, $params)) {

            $results = [];
            foreach ($datas as $data) {
                $instance = new self();
                self::set_properties($instance, $data);
                $results[$instance->id] = $instance;
            }
            return $results;

        } else {
            return false;
        }
    }

    public static function fetchbyproviderid($provider, $providerid, $staffid = null) {
        global $DB;

        $providercompare = $DB->sql_compare_text('provider');
        $params = ['providerid' => $providerid, 'providername' => $provider];

        $stafffrag = '';

        if (!empty($staffid)) {
            $params['staffid'] = $staffid;
            $stafffrag = ' AND staffid = :staffid ';
        }

        $sql =
                "SELECT * FROM {local_learningrecordstore} WHERE providerid = :providerid AND $providercompare = :providername $stafffrag";

        return $DB->get_record_sql($sql, $params);
    }

    public static function bulkupdatedescription($provider, $providerid, $description) {
        global $DB;
        $sql =
                "UPDATE {local_learningrecordstore} SET learningdesc = :coursedescription WHERE provider = :provider AND providerid = :providerid";
        $DB->execute($sql, ['provider' => $provider, 'coursedescription' => $description, 'providerid' => $providerid]);
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all($params) {
        $ret = self::fetch_all_helper('local_learningrecordstore', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        return $ret;
    }
}