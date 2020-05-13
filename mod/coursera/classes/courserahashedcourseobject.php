<?php

namespace mod_coursera;

abstract class courserahashedcourseobject extends \data_object {
    public function __construct($params = null, $fetch = true) {
        if (isset($params)) {
            $paramskeyset = [];
            foreach ($params as $key => $value) {
                $paramskeyset[strtolower($key)] = $value;
            }
            $params = $paramskeyset;
        }

        parent::__construct($params, $fetch);

        foreach ($this->optional_fields as $key => $val) {
            if (!isset($this->$key)) {
                $this->$key = $val;
            }
        }
    }

    public function __set($name, $value) {
        $this->$name = $value;
        if ($name !== 'hash') {
            $this->gethash();
        }
    }

    public function gethash() {
        $serializable = (array)$this->get_record_data();
        unset($serializable['id']);
        unset($serializable['courseracourseid']);
        $this->hash = md5(serialize($serializable));

        return $this->hash;
    }

    public static function savecourserahashedcourseobject($courseracourse, $courserahashedcourseobjectarray, $objecttype) {
        $classname = "\mod_coursera\\" . $objecttype;
        $apipartners = [];
        foreach ($courserahashedcourseobjectarray as $rawpartner) {
            $partner = new $classname((array) $rawpartner, false);
            $partner->courseracourseid = $courseracourse->id;
            $apipartners[$partner->gethash()] = $partner;
        }
        global $DB;
        $blankpartner = new $classname();
        $existingpartners = $DB->get_records($blankpartner->table, ['courseracourseid' => $courseracourse->id], '', 'hash, *');

        $todelete = array_diff_key($existingpartners, $apipartners);
        $DB->delete_records_list($blankpartner->table, 'hash', array_keys($todelete));

        $tocreate = array_diff_key($apipartners, $existingpartners);
        foreach ($tocreate as $newpartner) {
            $newpartner->insert();
        }
    }

    public function export_for_template(\renderer_base $output) {
        $retval = [];

        foreach (array_merge($this->required_fields, array_keys($this->optional_fields)) as $fieldname) {
            $retval[$fieldname] = $this->$fieldname;
        }
        return $retval;
    }
}