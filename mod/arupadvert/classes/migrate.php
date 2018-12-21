<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 02/05/2018
 * Time: 15:05
 */

namespace mod_arupadvert;

class migrate {

    public static function deprecate_arupadvert_tap() {
        global $DB, $CFG;

        require_once("$CFG->dirroot/lib/coursecatlib.php");
        require_once("$CFG->dirroot/course/lib.php");

        $archivecategory = $DB->get_record('course_categories', ['idnumber' => 'archivecourses']);
        if (empty($archivecategory)) {
            $coursecat = \coursecat::create(['idnumber' => 'archivecourses', 'name' => 'Archive Courses', 'visible' => false]);
            $archivecategoryid = $coursecat->id;
        } else {
            $archivecategoryid = $archivecategory->id;
        }

        //
        // Really, really grim BUT there aren't many to do... (also DON'T PUT CSVs IN DB fields in the first place!)
        // Can't use replace as there are no trailing commas.
        // Need to do it in an intrusive way as some of the course IDs may not exist until we have finished migrating.
        $multicoursecertif = $DB->get_records_sql("SELECT * FROM {certif} WHERE courseid LIKE '%,%'");
        foreach ($multicoursecertif as $certif) {
            $certif->courseid = explode(',', $certif->courseid);
        }

        // Handle tapsadverts.
        // Something is wrong with the name column...
        $sql =
                'SELECT ad.id, ad.course, ad.name, ad.altword, ad.showheadings, ad.timecreated, ad.timemodified, adtap.tapscourseid
                FROM {arupadvert} ad
                INNER JOIN {arupadvertdatatype_taps} adtap ON ad.id = adtap.arupadvertid';
        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $advert) {
            $transaction = $DB->start_delegated_transaction();

            try {
                $course = $DB->get_record('course', ['id' => $advert->course]);

                // Handle certifs with multiple courses.
                foreach ($multicoursecertif as $certif) {
                    if (in_array($advert->tapscourseid, $certif->courseid)) {
                        unset($certif->courseid[$advert->tapscourseid]);
                        $certif->courseid[] = $advert->course;

                        // Save as we go so we can stop and re-start the script.
                        $certif->courseid = implode(',', $certif->courseid);
                        $DB->update_record('certif', $certif);
                        $certif->courseid = explode(',', $certif->courseid);
                    }
                }

                $cmid = $DB->get_field_sql(
                        'SELECT cm.id FROM {course_modules} cm INNER JOIN {modules} m ON cm.module = m.id WHERE m.name = :modulename AND cm.instance = :arupadvertid',
                        ['modulename' => 'arupadvert', 'arupadvertid' => $advert->id]
                );

                $arupmetadata = new \coursemetadatafield_arup\arupmetadata();
                \coursemetadatafield_arup\arupmetadata::set_properties($arupmetadata, (array) $advert);

                if ($arupmetadata->name == 'Advert') {
                    $arupmetadata->name = $course->fullname;
                }

                // Unset the fields we don't want.
                unset($arupmetadata->id);

                $tapscourse = $DB->get_record('local_taps_course', ['courseid' => $advert->tapscourseid]);
                if (!empty($tapscourse)) {
                    // Pull in data from the taps course.
                    $arupmetadata->description = $tapscourse->coursedescription;
                    $arupmetadata->descriptionformat = FORMAT_HTML;
                    $arupmetadata->objectives = $tapscourse->courseobjectives;
                    $arupmetadata->objectivesformat = FORMAT_HTML;
                    $arupmetadata->audience = $tapscourse->courseaudience;
                    $arupmetadata->audienceformat = FORMAT_HTML;
                    $arupmetadata->keywords = $tapscourse->keywords;
                    $arupmetadata->keywordsformat = FORMAT_HTML;
                    $arupmetadata->timecreated = isset($advert->timecreated) ? $advert->timecreated : $course->timecreated;
                    $arupmetadata->timemodified =
                            isset($tapscourse->timemodified) ? $tapscourse->timemodified : $course->timemodified;
                    $arupmetadata->accredited = $tapscourse->globallearningstandards == 'Meets Global Learning Standards';
                    $arupmetadata->accreditationdate =
                            isset($tapscourse->accreditationgivendate) ? $tapscourse->accreditationgivendate : 0;
                    $arupmetadata->timecreated = $tapscourse->timemodified;
                    $arupmetadata->duration = $tapscourse->duration;
                    $arupmetadata->durationunits = $tapscourse->durationunitscode;

                    // Update the moodle course.
                    $course->startdate = $tapscourse->startdate;
                    $course->enddate = $tapscourse->enddate;

                    if (
                            empty($course->shortname)
                            && !empty($tapscourse->coursecode)
                            && !$DB->record_exists('course', ['shortname' => $tapscourse->coursecode])
                    ) {
                        $course->shortname = $tapscourse->coursecode;
                    }

                    if (!empty($tapscourse->onelinedescription)) {
                        $course->summary = $tapscourse->onelinedescription;
                        $course->summaryformat = FORMAT_HTML;
                    }

                    if (!empty($tapscourse->archived)) {
                        $course->category = $archivecategoryid;
                    }
                } else {
                    $arupmetadata->timecreated = $course->timecreated;
                    $arupmetadata->timemodified = $course->timemodified;
                }

                // Insert the record.
                $arupmetadata->insert();

                self::handle_files($cmid, $course->id);

                update_course($course);

                // Link local_taps_class to the moodle course.
                $DB->execute('UPDATE {local_taps_class} SET courseid = :courseid WHERE courseid = :tapscourseid',
                        ['courseid' => $course->id, 'tapscourseid' => $advert->tapscourseid]);

                // Link local_taps_enrolment to the moodle course.
                $DB->execute('UPDATE {local_taps_enrolment} SET courseid = :courseid WHERE courseid = :tapscourseid',
                        ['courseid' => $course->id, 'tapscourseid' => $advert->tapscourseid]);

                // Link local_certif to the moodle course.
                $DB->execute('UPDATE {certif} SET courseid = :courseid WHERE courseid = :tapscourseid',
                        ['courseid' => $course->id, 'tapscourseid' => $advert->tapscourseid]);

                course_delete_module($cmid);
                $DB->delete_records_select('local_taps_course', 'id = :tapscourseid', ['tapscourseid' => $advert->tapscourseid]);
                $DB->delete_records_select('local_taps_course_category', 'courseid = :courseid',
                        ['courseid' => $advert->tapscourseid]);
            } catch (\Exception $ex) {
                $transaction->rollback($ex);
            }
            mtrace("Processed taps advert for course $advert->course");
            $transaction->allow_commit();
        }

        // Handle custom adverts.
        $sql = 'SELECT 
                        ad.id, ad.course, ad.name, ad.altword, ad.showheadings, ad.timecreated, ad.timemodified,
                        adcust.description, adcust.objectives, adcust.audience, adcust.accredited, adcust.keywords
                        FROM {arupadvert} ad
                        INNER JOIN {arupadvertdatatype_custom} adcust ON ad.id = adcust.arupadvertid';
        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $advert) {
            $transaction = $DB->start_delegated_transaction();

            try {
                $arupmetadata = new \coursemetadatafield_arup\arupmetadata();

                $cmid = $DB->get_field_sql(
                        'SELECT cm.id FROM {course_modules} cm INNER JOIN {modules} m ON cm.module = m.id WHERE m.name = :modulename AND cm.instance = :arupadvertid',
                        ['modulename' => 'arupadvert', 'arupadvertid' => $advert->id]
                );

                // Unset the fields we don't want.
                unset($advert->id);

                \coursemetadatafield_arup\arupmetadata::set_properties($arupmetadata, (array) $advert);

                $arupmetadata->save();

                self::handle_files($cmid, $advert->course);

                course_delete_module($cmid);
            } catch (\Exception $ex) {
                $transaction->rollback($ex);
            }
            mtrace("Processed custom advert for course $advert->course");
            $transaction->allow_commit();
        }

        // Handle un-linked taps courses.
        $sql = "select ltc.*, aadtt.timecreated
                        from {local_taps_course} ltc
                        left join {arupadvertdatatype_taps} aadtt on ltc.courseid = aadtt.tapscourseid
                        where aadtt.id is null";
        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $tapscourse) {
            $transaction = $DB->start_delegated_transaction();
            try {
                $course = new \stdClass();
                $course->fullname = $tapscourse->coursename;
                $course->startdate = $tapscourse->startdate;
                $course->enddate = $tapscourse->enddate;

                if (!empty($tapscourse->coursecode)) {
                    $course->shortname = self::find_course_field_suffix('shortname', $tapscourse->coursecode);
                } else {
                    $course->shortname = '';
                }

                $course->summary = $tapscourse->onelinedescription;
                $course->summaryformat = FORMAT_HTML;

                if (!empty($tapscourse->courseid)) {
                    $course->idnumber = self::find_course_field_suffix('idnumber', $tapscourse->courseid);
                } else {
                    $course->idnumber = '';
                }

                // Put the course in the archive category.
                $course->category = $archivecategoryid;

                $course = create_course($course);
                \local_admin\courseformmoddifier::post_creation($course);

                $arupmetadata = new \coursemetadatafield_arup\arupmetadata();
                $arupmetadata->name = $tapscourse->coursename;
                $arupmetadata->description = $tapscourse->coursedescription;
                $arupmetadata->descriptionformat = FORMAT_HTML;
                $arupmetadata->objectives = $tapscourse->courseobjectives;
                $arupmetadata->objectivesformat = FORMAT_HTML;
                $arupmetadata->audience = $tapscourse->courseaudience;
                $arupmetadata->audienceformat = FORMAT_HTML;
                $arupmetadata->keywords = $tapscourse->keywords;
                $arupmetadata->keywordsformat = FORMAT_HTML;
                $arupmetadata->timecreated = $tapscourse->timecreated;
                $arupmetadata->timemodified = $tapscourse->timemodified;
                $arupmetadata->accredited = $tapscourse->globallearningstandards == 'Meets Global Learning Standards';
                $arupmetadata->accreditationdate =
                        isset($tapscourse->accreditationgivendate) ? $tapscourse->accreditationgivendate : 0;
                $arupmetadata->timecreated = $tapscourse->timemodified;
                $arupmetadata->duration = $tapscourse->duration;
                $arupmetadata->durationunits = $tapscourse->durationunitscode;

                // Link local_taps_class to the moodle course.
                $DB->execute('UPDATE {local_taps_class} SET courseid = :moodlecourseid WHERE courseid = :tapscourseid',
                        ['moodlecourseid' => $course->id, 'tapscourseid' => $tapscourse->courseid]);

                // Link local_taps_enrolment to the moodle course.
                $DB->execute('UPDATE {local_taps_enrolment} SET courseid = :moodlecourseid WHERE courseid = :tapscourseid',
                        ['moodlecourseid' => $course->id, 'tapscourseid' => $tapscourse->courseid]);

                // Link local_certif to the moodle course.
                $DB->execute('UPDATE {certif} SET courseid = :courseid WHERE courseid = :tapscourseid',
                        ['courseid' => $course->id, 'tapscourseid' => $tapscourse->courseid]);

                $DB->delete_records_select('local_taps_course', 'id = :tapscourseid', ['tapscourseid' => $tapscourse->id]);
                $DB->delete_records_select('local_taps_course_category', 'courseid = :courseid',
                        ['courseid' => $tapscourse->id]);
            } catch (\Exception $ex) {
                $transaction->rollback($ex);
            }

            // Handle certifs with multiple courses.
            foreach ($multicoursecertif as $certif) {
                if (in_array($tapscourse->courseid, $certif->courseid)) {
                    unset($certif->courseid[$tapscourse->courseid]);
                    $certif->courseid[] = $course->id;

                    // Save as we go so we can stop and re-start the script.
                    $certif->courseid = implode(',', $certif->courseid);
                    $DB->update_record('certif', $certif);
                    $certif->courseid = explode(',', $certif->courseid);
                }
            }

            mtrace("Processed unlinked taps course for taps course $tapscourse->id");
            $transaction->allow_commit();
        }
    }

    private static function handle_files($cmid, $courseid) {
        $fs = get_file_storage();

        $advertcontext = \context_module::instance($cmid);
        $coursecontext = \context_course::instance($courseid);

        $oldfiles = $fs->get_area_files($advertcontext->id, 'mod_arupadvert', 'blockimage');
        foreach ($oldfiles as $oldfile) {
            $filerecord = new \stdClass();
            $filerecord->contextid = $coursecontext->id;
            $filerecord->component = 'coursemetadatafield_arup';
            $filerecord->filearea = 'blockimage';

            $fs->create_file_from_storedfile($filerecord, $oldfile);

            $existingsummary = $fs->get_area_files($coursecontext->id, 'course', 'overviewfiles');
            if (empty($existingsummary)) {
                $filerecord = new \stdClass();
                $filerecord->contextid = $coursecontext->id;
                $filerecord->component = 'course';
                $filerecord->filearea = 'overviewfiles';

                $fs->create_file_from_storedfile($filerecord, $oldfile);
            }
        }

        $oldfiles = $fs->get_area_files($advertcontext->id, 'mod_arupadvert', 'originalblockimage');
        foreach ($oldfiles as $oldfile) {
            $filerecord = new \stdClass();
            $filerecord->contextid = $coursecontext->id;
            $filerecord->component = 'coursemetadatafield_arup';
            $filerecord->filearea = 'originalblockimage';

            $fs->create_file_from_storedfile($filerecord, $oldfile);
        }


    }

    private static function find_course_field_suffix($field, $value) {
        global $DB;

        $found = false;

        $i = 0;
        $suffixedvalue = $value;
        while ($found == false) {
            if ($i > 0) {
                $suffixedvalue = $value . '_' . $i;
            }
            $found = !$DB->record_exists('course', [$field => $suffixedvalue]);

            $i++;
        }

        return $suffixedvalue;
    }
}