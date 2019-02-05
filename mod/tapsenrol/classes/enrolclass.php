<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 04/02/2019
 * Time: 11:07
 */

namespace mod_tapsenrol;

global $CFG;
require_once("$CFG->dirroot/completion/data_object.php");

class enrolclass extends \data_object {
    const TYPE_CLASSROOM = 10;
    const TYPE_ELEARNING = 20;

    public $table = 'local_taps_class';

    public $required_fields = ['id'];
    public $optional_fields = [
            'classid'                  => null,
            'classname'                => null,
            'courseid'                 => null,
            'coursename'               => null,
            'classtype'                => null,
            'classstatus'              => null,
            'classdurationunits'       => null,
            'classdurationunitscode'   => null,
            'classduration'            => null,
            'classstartdate'           => null,
            'classenddate'             => null,
            'enrolmentstartdate'       => null,
            'enrolmentenddate'         => null,
            'trainingcenter'           => null,
            'location'                 => null,
            'classstarttime'           => null,
            'classendtime'             => null,
            'minimumattendees'         => null,
            'maximumattendees'         => null,
            'maximuminternalattendees' => null,
            'seatsremaining'           => null,
            'restrictedflag'           => null,
            'secureflag'               => null,
            'currencycode'             => null,
            'price'                    => null,
            'jobnumber'                => null,
            'classownerempno'          => null,
            'classownerfullname'       => null,
            'classsponsor'             => null,
            'classuserstatus'          => null,
            'classsuppliername'        => null,
            'offeringstartdate'        => null,
            'offeringenddate'          => null,
            'learningpathonlyflag'     => null,
            'timezone'                 => null,
            'usedtimezone'             => null,
            'classcost'                => null,
            'classcostcurrency'        => null,
            'archived'                 => false,
            'classhidden'              => false,
            'timemodified'             => null,
    ];

    public $classname;
    public $courseid;
    public $coursename;
    public $classtype;
    public $classstatus;
    public $classdurationunits;
    public $classdurationunitscode;
    public $classduration;
    public $classstartdate;
    public $classenddate;
    public $enrolmentstartdate;
    public $enrolmentenddate;
    public $trainingcenter;
    public $location;
    public $classstarttime;
    public $classendtime;
    public $minimumattendees;
    public $maximumattendees;
    public $maximuminternalattendees;
    public $seatsremaining;
    public $restrictedflag;
    public $secureflag;
    public $currencycode;
    public $price;
    public $jobnumber;
    public $classownerempno;
    public $classownerfullname;
    public $classsponsor;
    public $classuserstatus;
    public $classsuppliername;
    public $offeringstartdate;
    public $offeringenddate;
    public $learningpathonlyflag;
    public $timezone;
    public $usedtimezone;
    public $classcost;
    public $classcostcurrency;
    public $archived;
    public $classhidden;
    public $timemodified;

    /**
     * @param array $params
     * @return self
     */
    public static function fetch($params) {
        return self::fetch_helper('local_taps_class', __CLASS__, $params);
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all($params) {
        $ret = self::fetch_all_helper('local_taps_class', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        return $ret;
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all_visible_by_course($courseid, $extraparams = []) {
        $params = [
                'courseid'    => $courseid,
                'classhidden' => false,
                'archived'    => false
        ];

        $params = array_merge($params, $extraparams);

        $ret = self::fetch_all_helper('local_taps_class', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        return $ret;
    }
}