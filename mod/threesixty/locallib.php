<?php

require_once "$CFG->libdir/ddllib.php";

/**
 * List of competencies along with their skills.
 */
function threesixty_get_competency_listing($activityid){
    global $CFG, $DB;

    $ret = array();

    $sql = "
        SELECT
            s.id AS skillid,
            c.id AS competencyid,
            c.name,
            c.description,
            s.name AS skillname,
            c.showfeedback,
            c.sortorder AS competencyorder,
            s.sortorder AS skillorder
        FROM
            {threesixty_skill} s
          RIGHT OUTER JOIN
              {threesixty_competency} c
          ON
              s.competencyid = c.id
        WHERE
            c.activityid = $activityid
        ORDER BY
            c.sortorder, s.sortorder
    ";

    if ($rs = $DB->get_recordset_sql($sql)) {
        foreach ($rs as $record) {
            if (empty($ret[$record->competencyid])) {

                $competency = new stdClass();
                $competency->id = $record->competencyid;
                $competency->name = $record->name;
                $competency->description = $record->description;
                $competency->showfeedback = ($record->showfeedback == 1);
                $competency->skills = $record->skillname;
                $ret[$competency->id] = $competency;

            } else {

                $ret[$record->competencyid]->skills .= ', ' . $record->skillname;

            }
        }
    }

    return $ret;
}

/**
 * Delete the given competency from the database.
 *
 * @param integer $competencyid  The ID of the competency record
 * @param boolean $intransaction True if there is already an active transation
 * @returns boolean              True if the operation has succeeded, false otherwise
 */
function threesixty_delete_competency($competencyid, $intransaction=false){
    global $DB;

    if (!$intransaction) {
         //SCANMSG: transactions may need additional fixing
            $transaction = $DB->start_delegated_transaction();

        }
        // Delete all dependent skills
        $skills = $DB->get_records('threesixty_skill', array('competencyid' => $competencyid), '', 'id');
        if ($skills and count($skills) > 0) {
            foreach ($skills as $skill) {
                if (!threesixty_delete_skill($skill->id, true)) {
                    if (!$intransaction) {
                    }
                    return false;
                }
            }
        }
        // Delete all dependent response competencies
        if (!$DB->delete_records('threesixty_response_comp', array('competencyid' => $competencyid))) {
            if (!$intransaction) {
            }
            return false;
        }
        // Perform the deletion
        if (!$DB->delete_records('threesixty_competency', array('id' => $competencyid))) {
            if (!$intransaction) {
            }
            return false;
        }
        if (!$intransaction) {
            $transaction->allow_commit();
    }
    return true;
}

/**
 * Delete the given skill from the database.
 *
 * @param integer $skillid       The ID of the skill record
 * @param boolean $intransaction True if there is already an active transation
 * @returns boolean              True if the operation has succeeded, false otherwise
 */
function threesixty_delete_skill($skillid, $intransaction=false){
    global $DB;

    if (!$intransaction) {
         //SCANMSG: transactions may need additional fixing
            $transaction = $DB->start_delegated_transaction();

        }
        // Delete all dependent response skills
        if (!$DB->delete_records('threesixty_response_skill', array('skillid' => $skillid))) {
            if (!$intransaction) {
            }
            return false;
        }
        // Perform the deletion
        if (!$DB->delete_records('threesixty_skill', array('id' => $skillid))) {
            if (!$intransaction) {
            }
            return false;
        }
        if (!$intransaction) {
            $transaction->allow_commit();
    }
    return true;
}

/**
 * Delete the given analysis from the database.
 *
 * @param integer $analysisid    The ID of the analysis record
 * @param boolean $intransaction True if there is already an active transation
 * @returns boolean              True if the operation has succeeded, false otherwise
 */
function threesixty_delete_analysis($analysisid, $intransaction=false){
    global $DB;

    if (!$intransaction) {
         //SCANMSG: transactions may need additional fixing
            $transaction = $DB->start_delegated_transaction();

        }
        // Delete all dependent responses
        $responses = $DB->get_records('threesixty_response', array('analysisid' => $analysisid), '', 'id');
        if ($responses and count($responses) > 0) {
            foreach ($responses as $response) {
                if (!threesixty_delete_response($response->id, true)) {
                    if (!$intransaction) {
                    }
                    return false;
                }
            }
        }
        // Delete all dependent respondent
        if (!$DB->delete_records('threesixty_respondent', array('analysisid' => $analysisid))) {
            if (!$intransaction) {
            }
            return false;
        }
        // Perform the deletion
        if (!$DB->delete_records('threesixty_analysis', array('id' => $analysisid))) {
            if (!$intransaction) {
            }
            return false;
        }
        if (!$intransaction) {
            $transaction->allow_commit();
    }
    return true;
}

/**
 * Delete the given response from the database.
 *
 * @param integer $responseid    The ID of the response record
 * @param boolean $intransaction True if there is already an active transation
 * @returns boolean              True if the operation has succeeded, false otherwise
 */
function threesixty_delete_response($responseid, $intransaction=false){
    global $DB;

    if (!$intransaction) {
         //SCANMSG: transactions may need additional fixing
            $transaction = $DB->start_delegated_transaction();

        }
        // Delete all dependent response competencies
        if (!$DB->delete_records('threesixty_response_comp', array('responseid' => $responseid))) {
            if (!$intransaction) {
            }
            return false;
        }
        // Delete all dependent response skills
        if (!$DB->delete_records('threesixty_response_skill', array('responseid' => $responseid))) {
            if (!$intransaction) {
            }
            return false;
        }
        // Perform the deletion
        if (!$DB->delete_records('threesixty_response', array('id' => $responseid))) {
            if (!$intransaction) {
            }
            return false;
        }
        if (!$intransaction) {
            $transaction->allow_commit();
    }
    return true;
}

/**
 * Delete the given respondent from the database.
 *
 * @param integer $respondentid  The ID of the respondent record
 * @param boolean $intransaction True if there is already an active transation
 * @returns boolean              True if the operation has succeeded, false otherwise
 */
function threesixty_delete_respondent($respondentid, $intransaction=false){
    global $DB;

    if (!$intransaction) {
         //SCANMSG: transactions may need additional fixing
            $transaction = $DB->start_delegated_transaction();

        }
        // Delete the dependent response if necessary
        if ($responseid = $DB->get_field('threesixty_response', 'id', array('respondentid' => $respondentid))) {
            if (!threesixty_delete_response($responseid, true)) {
                if (!$intransaction) {
                }
                return false;
            }
        }
        // Perform the deletion
        if (!$DB->delete_records('threesixty_respondent', array('id' => $respondentid))) {
            if (!$intransaction) {
            }
            return false;
        }
        if (!$intransaction) {
            $transaction->allow_commit();
    }
    return true;
}

/**
 * List of skills and their competency.
 */
function threesixty_get_skill_names($activityid, $questionorder, $flipped = false){
    global $DB;

    switch ($questionorder) {
        case 'alternate' :
        case 'random' :
            $skillnames = array();

            $competencies = $DB->get_records('threesixty_competency', array('activityid' => $activityid), 'sortorder');
            foreach ($competencies as $competency) {
                $competency->skills = $DB->get_records('threesixty_skill', array('competencyid' => $competency->id), 'sortorder');
            }

            while (!empty($competencies)) {
                if ($questionorder == 'alternate') {
                    $competency = array_shift($competencies);
                    $skill = array_shift($competency->skills);
                } else {
                    $competencykey = array_rand($competencies);
                    $competency = $competencies[$competencykey];
                    $skillkey = array_rand($competencies[$competencykey]->skills);
                    $skill = $competencies[$competencykey]->skills[$skillkey];
                }
                $skillname = new stdClass();
                $skillname->id = $skill->id;
                $skillname->competencyid = $competency->id;
                $skillname->competencyname = $competency->name;
                $skillname->competencycolour = $competency->colour;
                $skillname->skillname = $skill->name;
                $skillnames[$skill->id] = clone($skillname);

                if ($questionorder == 'alternate' && !empty($competency->skills)) {
                    array_push($competencies, $competency);
                } elseif ($questionorder == 'random') {
                    unset($competencies[$competencykey]->skills[$skillkey]);
                    if (empty($competencies[$competencykey]->skills)) {
                        unset($competencies[$competencykey]);
                    }
                }
            }

            return $skillnames;
        case 'competency' :
        default :
            $sortdir = 'ASC';
            if ($flipped) {
                $sortdir = 'DESC';
            }
            $sql = "
                SELECT
                    s.id,
                    c.id AS competencyid,
                    c.name AS competencyname,
                    c.colour AS competencycolour,
                    s.name AS skillname
                FROM
                    {threesixty_competency} c
                JOIN
                    {threesixty_skill} s
                    ON c.id = s.competencyid
                WHERE
                    c.activityid = {$activityid}
                ORDER BY
                    c.sortorder {$sortdir}, s.sortorder
            ";
            return $DB->get_records_sql($sql);
    }
}

/**
 * List of competencyid and feedback.
 */
function threesixty_get_feedback($analysisid) {
    global $CFG, $DB;

    $sql = "
        SELECT
            trc.id,
            trc.competencyid,
            trc.feedback
        FROM
            {threesixty_response} tr
        JOIN
            {threesixty_response_comp} trc
        ON
            trc.responseid = tr.id
        WHERE tr.analysisid={$analysisid}
    ";
    $ret = $DB->get_records_sql($sql);
    if ($ret) {
        return $ret;
    } else {
        return array();
    }
}

/**
 * List of scores set by the user
 */
function threesixty_get_self_scores($analysisid, $selffilters){
    global $DB;

    $where = '';
    $params = array('analysisid' => $analysisid);
    if (!empty($selffilters)) {
        list($selffiltersql, $selffilterparams) = $DB->get_in_or_equal(array_keys($selffilters), SQL_PARAMS_NAMED);
        $where = "AND rt.type {$selffiltersql}";
        $params = array_merge($params, $selffilterparams);
    }

    $sql = <<<EOS
SELECT
    rs.id,
    rt.type,
    rs.skillid,
    rs.score
FROM
    {threesixty_respondent} rt
JOIN
    {threesixty_response} re
    ON re.respondentid = rt.id
JOIN
    {threesixty_response_skill} rs
    ON re.id = rs.responseid
WHERE
    rt.analysisid = :analysisid
    AND rt.uniquehash IS NULL
    AND re.timecompleted > 0
    {$where}
ORDER BY
    rt.type
EOS;

    $records = $DB->get_records_sql($sql, $params);

    $type = -1;
    $scores = array();
    foreach ($records as $record) {
        if ($record->type != $type) {
            $type = $record->type;
            $scores[$type] = array();
        }
        $scores[$type][$record->skillid] = $record->score;
    }

    return $scores;
}

/**
 * Returns true if the given activity has been completed by the given user.
 */
function threesixty_self_completed($threesixty, $userid){
    global $CFG, $DB;

    $sql = <<<EOS
SELECT
    r.id
FROM
    {threesixty_analysis} a
JOIN
    {threesixty_respondent} r
    ON a.id = r.analysisid
JOIN
    {threesixty_response} rs
    ON rs.analysisid = a.id AND rs.respondentid = r.id
WHERE
    a.activityid = :activityid
    AND a.userid = :userid
    AND r.uniquehash IS NULL
    AND r.type = :type
    AND rs.timecompleted > 0
EOS;
    $params = array(
        'activityid' => $threesixty->id,
        'userid' => $userid
    );
    $selftypes = explode("\n", $threesixty->selftypes);
    $completed = true;
    foreach ($selftypes as $index => $selftype) {
        $params['type'] = $index;
        $completed = $completed && $DB->get_records_sql($sql, $params);
    }
    return $completed;
}

/**
 * Return a list of users having submitted a response in this activity.
 *
 * @param object $activity Record from the threesixty table
 * @returns an array of user records.
 */
function threesixty_users($activity, $groupids = array()){
    global $DB;

    $groupjoin = '';
    $groupwhere = '';
    $groupparams = array();
    if (!empty($groupids)) {
        $groupjoin = 'JOIN {groups_members} gm ON gm.userid = u.id';
        list($groupsql, $groupparams) = $DB->get_in_or_equal($groupids, SQL_PARAMS_QM);
        $groupwhere = "AND gm.groupid {$groupsql}";
    }
    $usernamefields = get_all_user_name_fields(true, 'u');
    $sql = "
        SELECT DISTINCT
            u.id,
            {$usernamefields}
        FROM
            {threesixty_analysis} a
        JOIN
            {threesixty_response} r ON r.analysisid = a.id
        JOIN
            {user} u ON a.userid = u.id
        $groupjoin
        WHERE
            a.activityid = ? AND
            r.timecompleted > 0
            {$groupwhere}
        ORDER BY
            u.lastname ASC
    ";

    $records = $DB->get_records_sql($sql, array_merge(array($activity->id), $groupparams));
    return $records;
}
/**
 * Returns a list of all of the users who are eligible to participate in the
 * 360 activity.
 * @author eleanor.martin
 * @param <type> $context
 * @return an array of user records with id, firstname, lastname.
 */
function threesixty_get_possible_participants($threesixty, $context, $groupids, $sort="u.lastname"){
    global $DB;

    $fields = 'u.id, u.firstname, u.lastname';
    $capusers = get_users_by_capability($context, 'mod/threesixty:participate', $fields, $sort);
    $where = '';
    $params = array();
    if ($capusers) {
        list($insql, $inparams) =  $DB->get_in_or_equal(array_keys($capusers), SQL_PARAMS_NAMED);
        $where = "OR u.id {$insql}";
        $params = array_merge($params, $inparams);
    }

    $groupjoin = '';
    $groupwhere = '';
    $groupparams = array();
    if (!empty($groupids)) {
        $groupjoin = 'JOIN {groups_members} gm ON gm.userid = u.id';
        list($groupsql, $groupparams) = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED);
        $groupwhere = "AND gm.groupid {$groupsql}";
        $params = array_merge($params, $groupparams);
    }

    $sql = <<<EOS
SELECT
    u.id, u.firstname, u.lastname
FROM
    {user} u
    {$groupjoin}
WHERE
    (
        u.id IN (
            SELECT userid FROM {threesixty_analysis} WHERE activityid = :activityid
        )
        {$where}
    )
    {$groupwhere}
ORDER BY {$sort}
EOS;
    $params['activityid'] = $threesixty->id;
    $users = $DB->get_records_sql($sql, $params);
    return $users;
 }
 /**
 * Return an html table listing the users.
 *
 * @param object $activity Record from the threesixty table
 * @param string $url      URL of the page to open once the 'userid' param has been added
 * @returns string The HTML to print out on the page (either a table or error message)
 */
function threesixty_user_listing($activity, $groupids, $url){
    global $CFG, $DB, $OUTPUT;

    require_once($CFG->dirroot.'/mod/threesixty/respondentslib.php');

    if ($records = threesixty_users($activity, $groupids)) {
        $table = new html_table();
        $table->head = array(get_string('name'), get_string('numberrespondents:sent', 'threesixty'), get_string('numberrespondents:received', 'threesixty'), get_string('actions', 'threesixty'));
        $table->align = array ('left', 'center', 'center', 'center');
        $table->data = array();

        $userids = array();
        $printurl = new moodle_url('/mod/threesixty/print.php', array('a' => $activity->id));
        $printicon = $OUTPUT->pix_icon('t/print', get_string('printreport', 'threesixty'));
        foreach ($records as $r) {
            $userids[] = $r->id;
            $name = format_string(fullname($r));
            $selectlink = "<a href=\"$url&amp;userid=$r->id\">$name</a>";
            $numrespondents = count_respondents($r->id, $activity->id);
            $printurl->param('userid', $r->id);
            $table->data[] = array($selectlink, $numrespondents['sent'], $numrespondents['received'], html_writer::link($printurl, $printicon));
        }

        $printurl = new moodle_url('/mod/threesixty/printall.php');
        $printbutton = html_writer::start_tag('form', array('action' => $printurl, 'method' => 'post'));
        $printbutton .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'a', 'value' => $activity->id));
        foreach ($groupids as $groupid) {
            $printbutton .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'groupids[]', 'value' => $groupid));
        }
        $printbutton .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'submit', 'value' => get_string('printreport:all', 'threesixty'), 'class' => 'btn-primary'));

        return
            get_string('selectuser', 'threesixty')
            . html_writer::table($table, true)
            . $printbutton;
    }

    return get_string('nousersfound', 'threesixty');
}

/**
 * Return the heading to print out to show the currently selected user.
 *
 * @param object $user     Record from the user table
 * @param int    $courseid ID of the current course
 * @param string $url      URL of the current page without the userid parameter
 */
function threesixty_selected_user_heading($user, $courseid, $url, $selectanother=true, $printreport=false){
    global $OUTPUT;

    $data = new stdClass();
    $userurl = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $courseid));
    $data->fullname = html_writer::link($userurl, format_string(fullname($user)));

    $actionurl = new moodle_url($url);
    $printbutton = '';
    if ($printreport) {
        $printurl = new moodle_url('/mod/threesixty/print.php', array('a' => $actionurl->get_param('a'), 'userid' => $user->id));
        $printbutton = html_writer::link($printurl, get_string('printreport', 'threesixty'), array('class' => 'btn btn-primary', 'style' => 'display: inline-block;margin-left: 10px;'));
    }
    $dropdown = '';
    if ($selectanother) {
        $activity = new stdClass();
        $activity->id = $actionurl->get_param('a');
        $otherusers = threesixty_users($activity);
        // Drop down to select another user...
        $dropdown .= html_writer::start_tag('form', array('action' => $actionurl, 'method' => 'post', 'style' => 'display: inline-block;margin-left: 20px;'));
        $dropdown .= html_writer::start_tag('select', array('name' => 'userid'));
        $dropdown .= html_writer::tag('option', get_string('selectanotheruser', 'threesixty'), array('value' => 0));
        foreach ($otherusers as $otheruser) {
            $dropdown .= html_writer::tag('option', fullname($otheruser), array('value' => $otheruser->id));
        }
        $dropdown .= html_writer::end_tag('select');
        $dropdown .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'submit', 'value' => get_string('go'), 'class' => 'btn-primary'));
        $dropdown .= html_writer::end_tag('form');

        $text = get_string('selecteduser', 'threesixty', $data) . $printbutton . $dropdown;
    } else {
        $text = get_string('reportforuser', 'threesixty', $data) . $printbutton;
    }

    return $OUTPUT->heading($text, 2, 'main');
}

/**
 * Return the page where the first incomplete competency is or 1 if it's complete.
 */
function threesixty_get_first_incomplete_competency($activityid, $userid, $respondent){
    global $CFG, $DB;

    $respondentclause = 'r.respondentid IS NULL';
    if ($respondent != null) {
        $respondentclause = "r.respondentid = $respondent->id";
    }

    $sql = "
        SELECT
            r.id
        FROM
            {threesixty_analysis} a
        JOIN
            {threesixty_response} r
        ON
            r.analysisid = a.id
        WHERE
            a.activityid = ? AND
            a.userid = ? AND
            $respondentclause AND
            r.timecompleted = 0
    ";

    if (!$response = $DB->get_record_sql($sql, array($activityid, $userid))) {
        return 1; // activity is either not started or completed already
    }

    // get just the first one
    $sql = "
        SELECT
            c.id,
            c.sortorder
        FROM
            {threesixty_response} r
        JOIN
            {threesixty_response_skill} rs
        ON
            rs.responseid = r.id
          RIGHT OUTER JOIN
              {threesixty_skill} s
          ON
              rs.skillid = s.id
        JOIN
            {threesixty_competency} c
        ON
            c.id = s.competencyid
        WHERE
            (r.id IS NULL or r.id = $response->id) AND
            (score IS NULL OR score = 0)
        ORDER BY
            c.sortorder
    ";

    if ($record = $DB->get_record_sql($sql, array(), IGNORE_MULTIPLE)) {
        $competencyid = $record->id;

        // Figure out which page this competency is in
        return $record->sortorder + 1;
    }

    // All skills have been scored, form has not been submitted, go to last page
    return $DB->count_records('threesixty_competency', array('activityid' => $activityid));
}

function threesixty_get_respondent_scores($analysisid, $respondentfilters){
    global $DB;
    list($respondentfiltersql, $respondentfilterparams) = $DB->get_in_or_equal(array_keys($respondentfilters), SQL_PARAMS_NAMED, 'param', true, -1);
    $where = "AND rt.type {$respondentfiltersql}";
    $params = array_merge(array('analysisid' => $analysisid), $respondentfilterparams);

    $sql = <<<EOS
SELECT
    rs.id,
    rt.id as respondentid,
    rt.type,
    rt.email,
    rt.firstname,
    rt.lastname,
    rs.skillid,
    rs.score
FROM
    {threesixty_respondent} rt
JOIN
    {threesixty_response} re
    ON re.respondentid = rt.id
JOIN
    {threesixty_response_skill} rs
    ON re.id = rs.responseid
WHERE
    rt.analysisid = :analysisid
    AND rt.uniquehash IS NOT NULL
    AND re.timecompleted > 0
    {$where}
ORDER BY
    rt.type
EOS;

    $records = $DB->get_records_sql($sql, $params);

    $type = -1;
    $scores = array();
    $user = new stdClass();
    $user->email = null;
    $user->scores = array();
    foreach ($records as $record) {
        if ($record->email != $user->email) {
            if (!is_null($user->email)) {
                $scores[$type][] = clone($user);
            }
            if ($record->type != $type) {
                $type = $record->type;
                $scores[$type] = array();
            }
            $user->id = $record->respondentid;
            $user->email = $record->email;
            $user->firstname = $record->firstname;
            $user->lastname = $record->lastname;
            $user->scores = array();
        }

        $user->scores[$record->skillid] = $record->score;
    }
    // Ensure final one added if it wasn't empty
    if (!empty($records)) {
        $scores[$type][] = clone($user);
    }

    return $scores;
}
/*
 * Redo the sort orders of the competencies in a given activity.
 *
 * @param $activityid - the id of the activity to reorder the competencies for.
 */
function threesixty_reorder_competencies($activityid){
    global $DB;

      //Get the remaining competencies, ordered correctly, and reset the sortorder from 0.
      $competencies = $DB->get_records('threesixty_competency', array('activityid' => $activityid), 'sortorder');
      if($competencies){
        $neworder = 0;
            foreach($competencies as $competency){
              if($competency->sortorder != $neworder){
                $competency->sortorder = $neworder;
                $DB->update_record('threesixty_competency', $competency);
              }
              $neworder++;
        }
      }
}

/**
* checks if current user has request from other Moodle users.
*
*/
function threesixty_has_requests($activityid, $userid = 0){
    global $CFG, $USER, $DB;

    if (!$userid){
        $userid = $USER->id;
    }
    // find where valid user has no response
    $sql = "
        SELECT
            tr.*,
            u.firstname,
            u.lastname
        FROM
            {user} u,
            {threesixty_respondent} tr
        LEFT JOIN
            {threesixty_response} r
        ON
            r.respondentid = tr.id
        WHERE
            u.id = tr.userid AND
            u.deleted = 0 AND
            tr.declined = 0 AND
            tr.respondentuserid = ? AND
            tr.activityid = ? AND
            r.id IS NULL
    ";
    if (!$requests = $DB->get_records_sql($sql, array($userid, $activityid))){
        return array();
    }
    return $requests;
}

function threesixty_get_respondent_types(&$activity){
    return explode("\n", $activity->respondenttypes);
}

function threesixty_get_alternative_word($threesixty, $baseword, $plural = '') {
    $wordfield = $baseword.'alternative';
    if (!empty($threesixty->$wordfield)) {
        $alternatives = explode('|||', $threesixty->$wordfield);
        if ($plural) {
            return (isset($alternatives[1])) ? $alternatives[1] : $alternatives[0] . 's';
        } else {
            return $alternatives[0];
        }
    } else {
        $identifier = ($plural) ? $plural : $baseword;
        return get_string($identifier, 'threesixty');
    }
}