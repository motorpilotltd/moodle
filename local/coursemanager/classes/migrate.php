<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 12/07/2018
 * Time: 10:59
 */

namespace local_coursemanager;

class migrate {
    public static function deprecate_coursemanager() {
        global $DB;

        $events = [
                '\local_coursemanager\event\class_created' => '\mod_tapsenrol\event\class_created',
                '\local_coursemanager\event\class_updated' => '\mod_tapsenrol\event\class_updated'
        ];

        foreach ($events as $from => $to) {
            $DB->execute("update {logstore_standard_log} set eventname = :to where eventname = :from",
                    ['from' => $from, 'to' => $to]
            );
        }
    }
}