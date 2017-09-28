<?php

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
use local_custom_certification\certification;

// Send the correct headers.
send_headers('text/html; charset=utf-8', false);

$PAGE->set_context(context_system::instance());

$renderer = $PAGE->get_renderer('local_custom_certification');

$param = optional_param('param', null, PARAM_RAW);
$action = optional_param('action', null, PARAM_RAW);
$certifid = optional_param('certifid', null, PARAM_INT);
$certifpath = optional_param('certifpath', null, PARAM_INT);
$searchword = optional_param('word', null, PARAM_RAW);
$type = optional_param('type', null, PARAM_RAW);
$coursesetarraykey = optional_param('coursesetarraykey', null, PARAM_INT);
$coursearraykey = optional_param('coursearraykey', null, PARAM_INT);
$courseids = optional_param('courseids', null, PARAM_RAW);
$courseid = optional_param('courseid', null, PARAM_INT);
$coursesetid = optional_param('coursesetid', null, PARAM_INT);
$certificationtype = optional_param('certificationtype', null, PARAM_RAW);
$destination = optional_param('destination', null, PARAM_RAW);
$coursefullname = optional_param('coursefullname', null, PARAM_RAW);
$userecertification = optional_param('userecertification', true, PARAM_BOOL);
if (isset($_POST['coursesets'])) {
    $coursesets = $_POST['coursesets'];
}

if (!empty($certifid)) {
    $certif = new \local_custom_certification\certification($certifid, false);
}

switch ($action) {
    case 'modal':
        if ($certificationtype == 'certification') {
            $sessioncoursesetcourses = isset($SESSION->certifcontent[$certifid]->certification[$coursesetid]->courses) ? $SESSION->certifcontent[$certifid]->certification[$coursesetid]->courses : [];
        } else {
            $sessioncoursesetcourses = isset($SESSION->certifcontent[$certifid]->recertification[$coursesetid]->courses) ? $SESSION->certifcontent[$certifid]->recertification[$coursesetid]->courses : [];
        }

        $existingcourses = [];
        foreach ($sessioncoursesetcourses as $course) {
            $existingcourses[] = $course->courseid;
        }
        $courseinsql = '';
        if (!empty($existingcourses)) {
            list($courseinsql, $courseparams) = $DB->get_in_or_equal($existingcourses, SQL_PARAMS_NAMED, 'param', false);
            $courseinsql = 'AND c.id ' . $courseinsql;
        }

        $basicwheresql = '
            WHERE
                c.visible=:coursevisible AND 
                c.category <> :categorynullid 
        ';
        $courseparams['coursesetid'] = $coursesetid;
        $courseparams['coursevisible'] = 1;
        $courseparams['categorynullid'] = 0;

        if ($type == 'append') {
            if (!empty($searchword)) {
                list($insql, $params) = $DB->get_in_or_equal(json_decode($searchword), SQL_PARAMS_NAMED, null, false);
            }
            $params['coursesetid'] = $coursesetid;
            $params['coursevisible'] = 1;
            $params['categorynullid'] = 0;
            $sql = "
                SELECT 
                  id,
                  fullname 
                FROM 
                  {course} c 
                $basicwheresql AND
                  c.fullname $insql;
            ";
            $courses = $DB->get_records_sql($sql, $params);

            $html = $renderer->display_assignment_modal($courses, $certifid, $param, $coursesetid, $certificationtype);

            if (!empty($searchword)) {
                echo json_encode($html);
                die();
            }
        } elseif ($type == 'search') {
            $searchword = strtolower($searchword);
            $sql = "
               SELECT 
                  c.id,
                  c.fullname
               FROM   
                  {certif_courseset_courses} ccc
               RIGHT JOIN 
                  {course} c
               ON 
                  c.id = ccc.courseid AND 
                  ccc.coursesetid = :coursesetid
               $basicwheresql AND
                  ccc.coursesetid IS NULL AND 
                  LOWER(c.fullname) LIKE '%$searchword%'
                  $courseinsql
            ";

            $courses = $DB->get_records_sql($sql, $courseparams);
            $html = $renderer->display_searched_rows($courses, $certifid, $param, $coursesetid, $certificationtype);
            echo json_encode(['content' => $html, 'counter' => count($courses)]);
            die();
        } elseif ($type == 'reset') {

            $sql = "
               SELECT 
                  c.id,
                  c.fullname
               FROM 
                  {certif_courseset_courses} ccc
               RIGHT JOIN 
                  {course} c
               ON 
                  c.id = ccc.courseid AND 
                  ccc.coursesetid = :coursesetid
               $basicwheresql AND
                  ccc.coursesetid IS NULL 
                  $courseinsql
            ";

            $courses = $DB->get_records_sql($sql, $courseparams);

            $html = $renderer->display_searched_rows($courses, $certifid, $param, $coursesetid, $certificationtype);
            echo json_encode(['content' => $html, 'counter' => count($courses)]);
            die();
        }
        $sql = "
            SELECT 
                id,
                fullname
            FROM 
                {course} c 
            $basicwheresql;
        ";
        $courses = $DB->get_records_sql($sql, $courseparams);
        $html = $renderer->display_assignment_modal($courses, $certifid, $param, $coursesetid, $certificationtype);
        echo $html;

        break;
    case 'savedata':
        $sessioncoursesets = array_merge($SESSION->certifcontent[$certifid]->certification, $SESSION->certifcontent[$certifid]->recertification);
        $sessioncoursesetids = [];

        foreach ($sessioncoursesets as $sessioncourseset) {

            $sessioncoursesetid = local_custom_certification\certification::set_courseset_details(
                $sessioncourseset->id,
                $sessioncourseset->label,
                $sessioncourseset->certifid,
                $sessioncourseset->sortorder,
                $sessioncourseset->certifpath,
                $sessioncourseset->completiontype,
                !empty($sessioncourseset->mincourses) ? $sessioncourseset->mincourses : 0,
                $sessioncourseset->nextoperator
            );

            foreach ($sessioncourseset->courses as $sessioncourse) {

                local_custom_certification\certification::set_courseset_course_details(
                    $sessioncourse->courseid,
                    $sessioncoursesetid,
                    $sessioncourse->certifid,
                    $sessioncourse->sortorder
                );
                $sessioncourseids[$sessioncourseset->id][] = $sessioncourse->courseid;
            }
            $sessioncoursesetids[] = $sessioncourseset->id;
        }

        $dbcoursesets = array_merge($certif->certificationcoursesets, $certif->recertificationcoursesets);
        foreach ($dbcoursesets as $dbcourseset) {

            if (!in_array($dbcourseset->id, $sessioncoursesetids)) {
                local_custom_certification\certification::delete_courseset($dbcourseset->id);
            }

            foreach ($dbcourseset->courses as $dbcourse) {
                if (!isset($sessioncourseids[$dbcourseset->id]) || !in_array($dbcourse->courseid, $sessioncourseids[$dbcourseset->id])) {
                    local_custom_certification\certification::delete_course_from_courseset($dbcourseset->id, $dbcourse->courseid);
                }
            }
        }
        //Necessary to refresh coursesets id
        unset($SESSION->certifcontent[$certifid]->certification);
        unset($SESSION->certifcontent[$certifid]->recertification);
        unset($SESSION->certifcontent[$certifid]->changed);
        \local_custom_certification\notification::add(get_string('certificationsaved', 'local_custom_certification'), \local_custom_certification\notification::TYPE_SUCCESS);
        echo json_encode('success');
        break;
    case 'addtotable':
        $certificationtype == 'certification' ? $certifpath = certification::CERTIFICATIONPATH_BASIC : $certifpath = certification::CERTIFICATIONPATH_RECERTIFICATION;
        if ($coursesetid == 0) {
            $maxid = 0;
            $count = 1;
            if ($certifpath == certification::CERTIFICATIONPATH_BASIC) {
                if(isset($SESSION->certifcontent[$certifid]->certification) && count($SESSION->certifcontent[$certifid]->certification) > 0){
                    $maxid = max(array_keys($SESSION->certifcontent[$certifid]->certification));
                }
                $count = count($SESSION->certifcontent[$certifid]->certification) + 1;
            } else {
                if(isset($SESSION->certifcontent[$certifid]->recertification) && count($SESSION->certifcontent[$certifid]->recertification) > 0){
                    $maxid = max(array_keys($SESSION->certifcontent[$certifid]->recertification));
                }
                $count = count($SESSION->certifcontent[$certifid]->recertification) + 1;
            }
            $newcourseset = new \stdClass();
            $newcourseset->id = rand()+$maxid;
            $newcourseset->label = get_string('coursesetdefaultname', 'local_custom_certification').' '.$count;
            $newcourseset->certifid = $certifid;
            $newcourseset->sortorder = 0;
            $newcourseset->certifpath = $certifpath;
            $newcourseset->completiontype = local_custom_certification\certification::COMPLETION_TYPE_ALL;
            $newcourseset->mincourses = 0;
            $newcourseset->nextoperator = local_custom_certification\certification::NEXTOPERATOR_AND;

            $coursesetid = $newcourseset->id;

            $newcourse = new \stdClass();
            $newcourse->courseid = $courseid;
            $newcourse->coursesetid = $coursesetid;
            $newcourse->certifid = $certifid;
            $newcourse->sortorder = 0;
            $newcourse->fullname = $coursefullname;

            $newcourseset->courses[$newcourse->courseid] = $newcourse;
            if ($certifpath == certification::CERTIFICATIONPATH_BASIC) {
                $SESSION->certifcontent[$certifid]->certification[$newcourseset->id] = $newcourseset;
            } else {
                $SESSION->certifcontent[$certifid]->recertification[$newcourseset->id] = $newcourseset;
            }

        } else {
            $newcourse = new \stdClass();
            $newcourse->courseid = $courseid;
            $newcourse->coursesetid = $coursesetid;
            $newcourse->certifid = $certifid;
            $newcourse->sortorder = 0;
            $newcourse->fullname = $coursefullname;

            if ($certifpath == certification::CERTIFICATIONPATH_BASIC) {
                $SESSION->certifcontent[$certifid]->certification[$coursesetid]->courses[$newcourse->courseid] = $newcourse;
            } else {
                $SESSION->certifcontent[$certifid]->recertification[$coursesetid]->courses[$newcourse->courseid] = $newcourse;
            }
        }
        $SESSION->certifcontent[$certifid]->changed = true;
        echo json_encode(['status' => 'success', 'coursesetid' => $coursesetid]);
        break;

    case 'coursesetbox':

        list($insql, $params) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $sql = "
               SELECT 
                   c.id,
                   c.fullname 
               FROM 
                   {course} c 
               WHERE 
                   c.id $insql;
        ";
        $courses = $DB->get_records_sql($sql, $params);
        if ($param == 'create') {
            echo $renderer->get_coursesetbox($courses, $certifid, $coursesetid);
        } elseif ($param == 'append') {
            echo $renderer->get_courses_rows($courses, $coursesetid);
        }
        break;

    case 'sort':

        if ($certificationtype == 'certification') {
            $certiftype = 'certification';
        } else {
            $certiftype = 'recertification';
        }
        $coursesets = $SESSION->certifcontent[$certifid]->$certiftype;

        if ($param == 'coursesetsort') {
            if ($destination == 'movedown') {

                $currentcourseset = $coursesets[$coursesetid];
                do {
                    $courseset = current($coursesets);
                    if ($courseset->id == $currentcourseset->id) {
                        $nextcourseset = next($coursesets);
                        $sortedarray[$nextcourseset->id] = $nextcourseset;
                        $sortedarray[$currentcourseset->id] = $currentcourseset;
                    } else {
                        $sortedarray[$courseset->id] = $courseset;
                    }
                } while (next($coursesets) != false);
                $SESSION->certifcontent[$certifid]->$certiftype = $sortedarray;
            } else {
                $currentcourseset = $coursesets[$coursesetid];
                do {
                    $courseset = current($coursesets);
                    if ($courseset->id == $currentcourseset->id) {
                        $previouscourseset = prev($coursesets);
                        unset($sortedarray[$previouscourseset->id]);
                        $sortedarray[$currentcourseset->id] = $currentcourseset;
                        $sortedarray[$previouscourseset->id] = $previouscourseset;
                        next($coursesets);
                    } else {
                        $sortedarray[$courseset->id] = $courseset;
                    }
                } while (next($coursesets) != false);
                $SESSION->certifcontent[$certifid]->$certiftype = $sortedarray;
            }
            $SESSION->certifcontent[$certifid]->changed = true;
            echo json_encode('success');
            die();
        } elseif ($param == 'coursesort') {
            if ($destination == 'movedown') {
                $currentcourse = $coursesets[$coursesetid]->courses[$courseid];
                do {
                    $course = current($coursesets[$coursesetid]->courses);
                    if ($course->courseid == $currentcourse->courseid) {
                        $nextcourse = next($coursesets[$coursesetid]->courses);
                        $sortedarray[$nextcourse->courseid] = $nextcourse;
                        $sortedarray[$currentcourse->courseid] = $currentcourse;
                    } else {
                        $sortedarray[$course->courseid] = $course;
                    }
                } while (next($coursesets[$coursesetid]->courses) != false);
                $coursesets[$coursesetid]->courses = $sortedarray;
            } else {
                $currentcourse = $coursesets[$coursesetid]->courses[$courseid];
                do {
                    $course = current($coursesets[$coursesetid]->courses);
                    if ($course->courseid == $currentcourse->courseid) {
                        $previouscourse = prev($coursesets[$coursesetid]->courses);
                        unset($sortedarray[$previouscourse->courseid]);
                        $sortedarray[$currentcourse->courseid] = $currentcourse;
                        $sortedarray[$previouscourse->courseid] = $previouscourse;
                        next($coursesets[$coursesetid]->courses);
                    } else {
                        $sortedarray[$course->courseid] = $course;
                    }
                } while (next($coursesets[$coursesetid]->courses) != false);
                $coursesets[$coursesetid]->courses = $sortedarray;
            }
            $SESSION->certifcontent[$certifid]->changed = true;
            echo json_encode('success');
            die();
        }

        break;
    case 'session':
        $coursesets = json_decode($coursesets);
        foreach ($coursesets as $courseset) {
            $coursesortorder = 0;
            $courses = [];
            foreach ($courseset->courses as $course) {

                $course->sortorder = $coursesortorder;
                $coursesortorder++;
                $courses[$course->courseid] = $course;
            }
            $courseset->courses = $courses;

            if ($courseset->certifpath == certification::CERTIFICATIONPATH_BASIC) {
                $SESSION->certifcontent[$certifid]->certification[$courseset->id] = $courseset;
            } else {
                $SESSION->certifcontent[$certifid]->recertification[$courseset->id] = $courseset;
            }
        }

        echo json_encode('success');
        break;
    case 'deletecourseset':

        if ($certifpath == certification::CERTIFICATIONPATH_BASIC) {
            unset($SESSION->certifcontent[$certifid]->certification[$coursesetid]);
        } else {
            unset($SESSION->certifcontent[$certifid]->recertification[$coursesetid]);
        }

        $SESSION->certifcontent[$certifid]->changed = true;

        echo json_encode('success');
        break;
    case 'deletecourse':
        if ($certifpath == certification::CERTIFICATIONPATH_BASIC) {
            unset($SESSION->certifcontent[$certifid]->certification[$coursesetid]->courses[$courseid]);
            $coursescount = count($SESSION->certifcontent[$certifid]->certification[$coursesetid]->courses);
            if ($coursescount == 0) {
                unset($SESSION->certifcontent[$certifid]->certification[$coursesetid]);
            }
        } else {
            unset($SESSION->certifcontent[$certifid]->recertification[$coursesetid]->courses[$courseid]);
            $coursescount = count($SESSION->certifcontent[$certifid]->recertification[$coursesetid]->courses);
            if ($coursescount == 0) {
                unset($SESSION->certifcontent[$certifid]->recertification[$coursesetid]);
            }
        }

        $SESSION->certifcontent[$certifid]->changed = true;

        echo json_encode('success');
        break;
    case 'clonecertificationcontent':
        $coursesets = json_decode($coursesets);
        $lastrand = 0;
        foreach ($coursesets as $courseset) {

            $coursestosave = [];
            $coursesetid = rand() + $lastrand;
            $lastrand = $coursesetid;
            $coursesetstosave = $courseset;
            foreach ($courseset->courses as $course) {
                $coursestosave[$course->courseid] = $course;
                $coursestosave[$course->courseid]->coursesetid = $coursesetid;
            }
            $coursesetstosave->courses = $coursestosave;
            $coursesetstosave->id = $coursesetid;
            $coursesetstosave->certifpath = certification::CERTIFICATIONPATH_RECERTIFICATION;
            $SESSION->certifcontent[$certifid]->recertification[$courseset->id] = $coursesetstosave;
        }

        $SESSION->certifcontent[$certifid]->changed = true;
        echo json_encode('success');
        break;
    case 'checkifchanged':
        if ($SESSION->certifcontent[$certifid]->changed == true) {
            echo json_encode(['resp' => 'success']);
        }
        break;
    case 'discardchanges':
        unset($SESSION->certifcontent[$certifid]->certification);
        unset($SESSION->certifcontent[$certifid]->recertification);
        unset($SESSION->certifcontent[$certifid]->changed);
        echo json_encode('success');
        break;
    case 'removerecertificationpath':
        $SESSION->certifcontent[$certifid]->recertification = [];
        $SESSION->certifcontent[$certifid]->changed = true;
        echo json_encode('success');
        break;
}

