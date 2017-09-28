<?php

namespace local_custom_certification;

class activityreset {

	/**
	 * Archives user's feedback for a course
	 *
	 * @param int $userid User ID
	 * @param int $courseid Course ID
	 */
	function feedback_archive_completion($userid, $courseid) {
		global $DB;
		
		$sql = "SELECT fc.*
            FROM {feedback_completed} fc
            INNER JOIN {feedback} f ON f.id = fc.feedback AND f.course = :courseid
            WHERE fc.userid = :userid";

		if ($completeds = $DB->get_records_sql($sql, ['userid' => $userid, 'courseid' => $courseid])) {
			foreach ($completeds as $completed) {
				feedback_delete_completed($completed->id);
			}
		}

		return true;
	}


	/**
	 * Archives user's certificates for a course
	 *
     * @param int $userid User ID
     * @param int $courseid Course ID
	 * @return bool always true
	 */
	function certificate_archive_completion($userid, $courseid) {
		global $DB;
		
		$sql = "SELECT ci.*
              FROM {certificate_issues} ci
              JOIN {certificate} c ON c.id = ci.certificateid AND c.course = :courseid
             WHERE ci.userid = :userid";

		if ($certs = $DB->get_records_sql($sql, ['userid' => $userid, 'courseid' => $courseid])) {
			foreach ($certs as $cert) {
				// Delete original
				$DB->delete_records('certificate_issues', ['id' => $cert->id]);
			}
		}
		return true;
	}


	/**
	 * Archives forum completion for a course
	 *
     * @param int $userid User ID
     * @param int $courseid Course ID
	 * @return boolean
	 */
	function forum_archive_completion($userid, $courseid) {
		global $DB, $CFG;

		// All forums associated with this course and user
		$sql = "SELECT f.*, cm.idnumber as cmidnumber
            FROM {forum} f
            JOIN {modules} m ON m.name = 'forum'
            JOIN {course_modules} cm ON cm.instance = f.id AND cm.module = m.id
            WHERE f.course = :courseid
            AND EXISTS (SELECT p.id
                        FROM {forum_discussions} d
                        JOIN {forum_posts} p ON p.discussion = d.id AND p.userid = :userid1
                        WHERE d.forum = f.id
                        UNION
                        SELECT d.id
                        FROM {forum_discussions} d
                        WHERE d.forum = f.id
                        AND d.userid = :userid2)";
		if (!$forums = $DB->get_records_sql($sql,
			['courseid' => $courseid, 'userid1' => $userid, 'userid2' => $userid])) {
			return;
		}
        $modfile = $CFG->dirroot . '/rating/lib.php';
        include_once($modfile);
		foreach ($forums as $forum) {
			$params = ['userid' => $userid, 'courseid' => $courseid, 'forumid' => $forum->id];

			$course_module = get_coursemodule_from_instance('forum', $forum->id, $courseid);
			$context = \context_module::instance($course_module->id);

			// Delete ratings on posts created by this user
			$sql = "SELECT p.id
                FROM {forum_posts} p
                JOIN {forum_discussions} d ON d.id = p.discussion AND d.course = :courseid AND d.forum = :forumid
                WHERE p.userid = :userid";
			if ($posts = $DB->get_records_sql($sql, $params)) {
				$rm = new \rating_manager();
				foreach ($posts as $post) {
					$ratingdeloptions = new \stdClass;
					$ratingdeloptions->component = 'mod_forum';
					$ratingdeloptions->ratingarea = 'post';
					$ratingdeloptions->userid = $userid;
					$ratingdeloptions->itemid = $post->id;
					$ratingdeloptions->contextid = $context->id;
					$rm->delete_ratings($ratingdeloptions);
				}
			}

			$sql = "DELETE FROM {forum_posts}
                WHERE userid = :userid
                AND EXISTS (SELECT {forum_discussions}.id
                            FROM {forum_discussions}
                            WHERE {forum_discussions}.id = {forum_posts}.discussion
                            AND {forum_discussions}.course = :courseid
                            AND {forum_discussions}.forum = :forumid)";
			$DB->execute($sql, $params);

			$sql = "DELETE FROM {forum_discussions}
                WHERE userid = :userid
                AND course = :courseid
                AND forum = :forumid";
			$DB->execute($sql, $params);

			// Reset the grades
			\forum_update_grades($forum, $userid, true);
		}
	}


    /**
     * Delete quiz completion records
     *
     * @global object $DB
     * @param int $userid User ID
     * @param int $courseid Course ID
     */
    function quiz_archive_completion($userid, $courseid) {
        global $DB, $CFG;

        $sql = 'SELECT q.*
            FROM {quiz} q
            WHERE q.course = :courseid
            AND EXISTS (SELECT qa.id
                        FROM {quiz_attempts} qa
                        WHERE qa.quiz = q.id
                        AND userid = :userid)';
        if ($quizs = $DB->get_records_sql($sql, ['courseid' => $courseid, 'userid' => $userid])) {

            $modfile = $CFG->dirroot . '/question/engine/lib.php';
            include_once($modfile);
            $modfile = $CFG->dirroot . '/mod/quiz/locallib.php';
            include_once($modfile);
            foreach ($quizs as $quiz) {
                // Deletion code copied from function quiz_delete_attempt() in /mod/quiz/locallib.php.
                // Looks like quiz_delete_attempt() will try to redo the grading if there are several attempts.
                // So deleting all the attempts here first and then resetting the grade.
                // Delete attempts.
                if ($attempts = $DB->get_records('quiz_attempts', ['quiz' => $quiz->id, 'userid' => $userid])) {
                    foreach ($attempts as $attempt) {
                        \question_engine::delete_questions_usage_by_activity($attempt->uniqueid);
                        $DB->delete_records('quiz_attempts', ['id' => $attempt->id]);
                    }
                }
                // Delete overrides.
                if ($overrides = $DB->get_records('quiz_overrides', ['quiz' => $quiz->id, 'userid' => $userid], 'id')) {
                    foreach ($overrides as $override) {
                        \quiz_delete_override($quiz, $override->id);
                    }
                }
                // Reset grades - this will delete the quiz grades and grade grades because there are no attempts.
                \quiz_save_best_grade($quiz, $userid);
            }
        }
    }



    /**
     * Archives user's assignments for a course
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     */
    function assign_archive_completion($userid, $courseid) {
        global $CFG, $DB;

        // Required for assign class.
        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $sql = "SELECT s.id AS submissionid,
                    a.id AS assignid
            FROM {assign_submission} s
            JOIN {assign} a ON a.id = s.assignment AND a.course = :courseid
            WHERE s.userid = :userid";
        $params = ['userid' => $userid, 'courseid' => $courseid];
        if ($submissions = $DB->get_records_sql($sql, $params)) {
            $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);


            // Create the reset grade.
            $grade = new \stdClass();
            $grade->userid   = $userid;
            $grade->rawgrade = null;

            foreach ($submissions as $submission) {
                $cm = get_coursemodule_from_instance('assign', $submission->assignid, $course->id);
                $context = \context_module::instance($cm->id);

                // Delete assignment files and assignment grade.
                $assignment = new \assign($context, $cm, $course);
                $grade = $assignment->get_user_grade($userid, false);

                if ($usersubmission = $assignment->get_user_submission($userid, false)) {
                    // Create file storage object.
                    $fs = get_file_storage();
                    // Dbman to check if a plugin table exists.
                    $dbman = $DB->get_manager();

                    // Delete files associated with this assignment for this user.
                    $assignmentid = $assignment->get_instance()->id;
                    $plugintypes = [];
                    $plugintypes['submissionplugins'] = ['fieldname' =>'submission', 'itemid' => $usersubmission->id];
                    if ($grade) {
                        $plugintypes['feedbackplugins'] = ['fieldname' => 'grade', 'itemid' => $grade->id];
                    }
                    $assignment_plugins = [
                        'submissionplugins' => $assignment->get_submission_plugins(),
                        'feedbackplugins' => $assignment->get_feedback_plugins()
                    ];
                    foreach ($plugintypes as $plugintype => $params) {
                        $deleteparams = ['assignment' => $assignmentid, $params['fieldname'] => $params['itemid']];
                        foreach ($assignment_plugins[$plugintype] as $plugin) {
                            $plugincomponent = $plugin->get_subtype() . '_' . $plugin->get_type();
                            $fileareas = $plugin->get_file_areas();
                            foreach ($fileareas as $filearea) {
                                $fs->delete_area_files($assignment->get_context()->id, $plugincomponent, $filearea, $params['itemid']);
                            }
                            // If a plugin component table exists then delete the records for this submission/feedback.
                            if ($dbman->table_exists($plugincomponent) &&
                                $DB->record_exists($plugincomponent, $deleteparams)) {
                                $DB->delete_records($plugincomponent, $deleteparams);
                            }
                        }
                    }

                    $DB->delete_records('assign_submission', ['id' => $usersubmission->id]);
                }

                // Remove assignment grade.
                if ($grade) {
                    $DB->delete_records('assign_grades', ['id' => $grade->id]);
                }

                // Reset grade.
                $assign = $DB->get_record('assign', ['id' => $submission->assignid]);
                $assign->cmidnumber = $cm->id;
                $assign->courseid = $courseid;
                \assign_grade_item_update($assign, $grade);

            }
        }

        return true;
    }

    /**
     * Archives user's assignments for a course
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     */
    function choice_archive_completion($userid, $courseid) {
        global $DB;

        $sql = "SELECT ca.id AS answerid,
                    c.id AS choiceid
            FROM {choice_answers} ca
            JOIN {choice} c ON c.id = ca.choiceid AND c.course = :courseid
            WHERE ca.userid = :userid";
        $params = ['userid' => $userid, 'courseid' => $courseid];

        if ($submissions = $DB->get_records_sql($sql, $params)) {
            // Answers to be deleted.
            $deleteanswers = [];
            foreach ($submissions as $submission) {
                $deleteanswers[] = $submission->answerid;
            }
            $DB->delete_records_list('choice_answers', 'id', $deleteanswers);
        }

        return true;
    }

    /**
     * Deletion archive completion records
     *
     * @global object $DB
     * @param int $userid User ID
     * @param int $courseid Course ID
     */
    function scorm_archive_completion($userid, $courseid) {
        global $DB;
        
        $sql = 'SELECT s.*
            FROM {scorm} s
            WHERE s.course = :courseid
            AND EXISTS (SELECT t.id
                        FROM {scorm_scoes_track} t
                        WHERE t.scormid = s.id
                        AND t.userid = :userid)';
        $scorms = $DB->get_records_sql($sql, ['courseid' => $courseid, 'userid' => $userid]);
        foreach ($scorms as $scorm) {
            $DB->delete_records('scorm_scoes_track', ['userid' => $userid, 'scormid' => $scorm->id]);
            // Resets the grades and completion to incomplete.
            $grade = new \stdClass();
            $grade->userid   = $userid;
            $grade->rawgrade = null;
            \scorm_grade_item_update($scorm, $grade);
        }
    }


    /**
     * Delete survey answers
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     */
    function survey_archive_completion($userid, $courseid)
    {
        global $DB;
        $surveys = $DB->get_records('survey', ['course' => $courseid]);
        foreach($surveys as $survey){
            $DB->delete_records('survey_answers', ['survey' => $survey->id, 'userid' => $userid]);
            $DB->delete_records('survey_analysis', ['survey' => $survey->id, 'userid' => $userid]);
        }
    }

    /**
     * Reset tapscompletion activity.
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     * @global \moodle_database $DB
     */
    public function tapscompletion_archive_completion($userid, $courseid) {
        global $DB;
        
        $now = time();

        $taps = new \local_taps\taps();
        
        $tcs = $DB->get_records('tapscompletion', ['course' => $courseid]);
        $staffid = $DB->get_field('user', 'idnumber', ['id' => $userid]);
        foreach ($tcs as $tc) {
            // Delete completion records.
            $DB->delete_records('tapscompletion_completion', ['tapscompletionid' => $tc->id, 'userid' => $userid]);

            // Mark associated active (cancelled/completed) enrolments as not active.
            list($in, $inparams) = $DB->get_in_or_equal(
                array_merge($taps->get_statuses('cancelled'), $taps->get_statuses('attended')),
                SQL_PARAMS_NAMED, 'status'
            );
            $compare = $DB->sql_compare_text('bookingstatus');
            $params = [
                'staffid' => $staffid,
                'courseid' => $tc->tapscourse,
                'active' => 1
            ];
            $enrolments = $DB->get_records_select('local_taps_enrolment', "staffid = :staffid AND courseid = :courseid AND active = :active AND {$compare} {$in}", array_merge($params, $inparams));
            
            foreach ($enrolments as $enrolment) {
                $enrolment->active = 0;
                $enrolment->timemodified = $now;
                $DB->update_record('local_taps_enrolment', $enrolment);
            }
        }
    }

    /**
     * Reset tapsenrol activity.
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     * @global \moodle_database $DB
     */
    public function tapsenrol_archive_completion($userid, $courseid) {
        global $DB;

        $now = time();

        $taps = new \local_taps\taps();

        $tes = $DB->get_records('tapsenrol', ['course' => $courseid]);
        $staffid = $DB->get_field('user', 'idnumber', ['id' => $userid]);
        foreach ($tes as $te) {
            // Delete completion records.
            $DB->delete_records('tapsenrol_completion', ['tapsenrolid' => $te->id, 'userid' => $userid]);

            // Mark associated active (cancelled/completed) enrolments as not active.
            list($in, $inparams) = $DB->get_in_or_equal(
                array_merge($taps->get_statuses('cancelled'), $taps->get_statuses('attended')),
                SQL_PARAMS_NAMED, 'status'
            );
            $compare = $DB->sql_compare_text('bookingstatus');
            $params = [
                'staffid' => $staffid,
                'courseid' => $te->tapscourse,
                'active' => 1
            ];
            $enrolments = $DB->get_records_select('local_taps_enrolment', "staffid = :staffid AND courseid = :courseid AND active = :active AND {$compare} {$in}", array_merge($params, $inparams));

            foreach ($enrolments as $enrolment) {
                $enrolment->active = 0;
                $enrolment->timemodified = $now;
                $DB->update_record('local_taps_enrolment', $enrolment);
            }
        }
    }
}