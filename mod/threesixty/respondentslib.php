<?php

/** End of screen : Starting local lib **/

function print_participants_listing($activity, $groupids, $baseurl){
    global $CFG;

    if ($users = threesixty_users($activity, $groupids)) {
        $table = new html_table();
        $table->head = array(get_string('name'), get_string('numberrespondents:sent', 'threesixty'), get_string('numberrespondents:received', 'threesixty'));
        $table->head[] = get_string('self:responseoptions', 'threesixty');
        $table->data = array();
        $viewstr = get_string('view', 'threesixty');
        foreach ($users as $user) {
            $name = format_string(fullname($user));
            $userurl = "<a href=".$CFG->wwwroot."/user/view.php?id={$user->id}&course={$activity->course}>".$name."</a>";
            $selectlink = "<a href=\"$baseurl&amp;userid=$user->id\">$viewstr</a>";

            $numrespondents = count_respondents($user->id, $activity->id);
            $table->data[] = array($userurl, $numrespondents['sent'], $numrespondents['received'], $selectlink);
        }
        return get_string('selectuser', 'threesixty').html_writer::table($table, true);
    } else {
        return get_string('nousersfound', 'threesixty');
    }
}

function generate_uniquehash($email){
    $timestamp = time();
    $salt = mt_rand();
    return sha1("$salt $email $timestamp");
}

function send_email($respondent, $user, $messageid, $extrainfo){
    $to = false;
    if (!empty($respondent->respondentuserid)) {
        $to = threesixty_user::get_user($respondent->respondentuserid);
    }
    if (!$to) {
        $to = threesixty_user::get_dummy_threesixty_user($respondent->email, $respondent->firstname, $respondent->lastname);
    }

    $extrainfo->respondentfullname = fullname($to);
    $extrainfo->respondentemail = $to->email;
    $extrainfo->userfullname = fullname($user);
    $extrainfo->useremail = $user->email;
    $subject = get_string("email:{$messageid}subject", 'threesixty', $extrainfo);
    $messagehtml = get_string("email:{$messageid}body", 'threesixty', $extrainfo);
    $messagetext = html_to_text($messagehtml);

    return email_to_user($to, $user, $subject, $messagetext, $messagehtml);
}

function request_respondent($formfields, $activity, $analysisid, $user, $context){
    global $DB;

    $respondent = new StdClass;
    $respondent->userid = $user->id;
    $respondent->activityid = $activity->id;
    $respondent->analysisid = $analysisid;
    $respondent->type = $formfields->type;

    $selectedrespondent = null;
    if (!empty($formfields->respondentuserid)) {
        $selectedrespondent = explode('|||', $formfields->respondentuserid);
    }
    $respondentemail = !empty($formfields->respondentuserid) ? $selectedrespondent[0] : $formfields->email;
    $respondentuser = $DB->get_record('user', array('email' => $respondentemail), 'id, firstname, lastname, email', IGNORE_MULTIPLE);

    if ($respondentuser) {
        $respondent->respondentuserid = $respondentuser->id;
        $respondent->firstname = $respondentuser->firstname;
        $respondent->lastname = $respondentuser->lastname;
        $respondent->email = $respondentuser->email;
    } elseif ($selectedrespondent) {
        $respondent->firstname = $selectedrespondent[1];
        $respondent->lastname = $selectedrespondent[2];
        $respondent->email = $selectedrespondent[0];
    } else {
        $respondent->firstname = $formfields->firstname;
        $respondent->lastname = $formfields->lastname;
        $respondent->email = $formfields->email;
    }

    $respondent->uniquehash = generate_uniquehash($respondent->email);
    while ($DB->get_record('threesixty_respondent', array('uniquehash' => $respondent->uniquehash), 'id')) {
        // Re hash!
        $respondent->uniquehash = generate_uniquehash($respondent->email);
    }
    $responsehashedurl = RESPONSE_BASEURL . $respondent->uniquehash;

    $extrainfo = new stdClass();
    $extrainfo->url =  $responsehashedurl;

    if (!$respondent->id = $DB->insert_record('threesixty_respondent', $respondent)) {
        error_log("threesixty: cannot insert respondent email=$respondent->email");
        return -1;
    }

    if (!send_email($respondent, $user, 'request', $extrainfo)) {
        error_log("threesixty: could not send request email to $respondent->email");
        return 0;
    }

    $event = \mod_threesixty\event\respondent_request_sent::create(array(
        'context' => $context,
        'objectid' => $respondent->id,
        'relateduserid' => $user->id,
        'other' => array(
            'to' => $respondent->email,
            'hash' => $respondent->uniquehash,
            'url' => $extrainfo->url
        )
    ));
    $event->trigger();

    return 1;
}

function send_reminder($respondentid, $user){
    global $DB;

    if (!$respondent = $DB->get_record('threesixty_respondent', array('id' => $respondentid))) {
        error_log("threesixty: cannot find respondent id=$respondentid");
        return false;
    }

    $extrainfo = new stdClass();
    $extrainfo->url = RESPONSE_BASEURL. $respondent->uniquehash;
    if (!send_email($respondent, $user, 'reminder', $extrainfo)) {
        error_log("threesixty: could not send reminder email to $respondent->email");
        return false;
    }

    return true;
}

function print_respondent_table($activityid, $analysisid, $userid, $canremind=false, $candelete=false){
    global $typelist, $OUTPUT;

      $respondents = threesixty_get_external_respondents($analysisid);
      if ($respondents) {
        $table = new html_table();
        $table->head = array(get_string('fullname'), get_string('email'), get_string('respondenttype', 'threesixty'), get_string('completiondate', 'threesixty'));
        if ($candelete or $canremind) {
            $table->head[] = get_string('actions', 'threesixty');
        }
        $table->data = array();
        foreach ($respondents as $respondent) {
            $data = array();
            $data[] = $respondent->firstname.' '.$respondent->lastname;
            $data[] = format_string($respondent->email);
            if (empty($typelist[$respondent->type])) {
                $data[] = get_string('unknown', 'threesixty');
            } else {
                $data[] = $typelist[$respondent->type];
            }
            if (empty($respondent->timecompleted)) {
                $data[] = get_string('none');
            } else {
                $data[] = userdate($respondent->timecompleted, get_string('strftimedate'));
            }
            // Action buttons
            $buttons = array();
            if ($canremind and empty($respondent->timecompleted)) {
                $link = 'respondents.php';
                $options = array('a' => $activityid, 'remind' => $respondent->id,'userid' => $userid, 'sesskey' => sesskey());
                $buttons[] = $OUTPUT->single_button(new moodle_url($link, $options), get_string('remindbutton', 'threesixty'), 'post', array('class' => 'btn btn-small'));
            }
            if ($candelete) {
                $link = 'respondents.php';
                $options = array('a' => $activityid, 'delete' => $respondent->id,'userid' => $userid, 'sesskey' => sesskey());
                $buttons[] = $OUTPUT->single_button(new moodle_url($link, $options), get_string('delete'), 'post', array('class' => 'btn btn-small'));
            }
            if (!empty($buttons)) {
                $data[] = implode('&nbsp;', $buttons);
            }

            $table->data[] = $data;
          }
          echo html_writer::table($table);
    } else {
      echo $OUTPUT->box_start();
      echo get_string('norespondents', 'threesixty');
      echo $OUTPUT->box_end();
    }
}

function threesixty_get_external_respondents($analysisid){
    global $CFG, $DB;

      $sql = "
          SELECT
              rt.id,
            rt.firstname,
            rt.lastname,
              rt.email,
              rt.type,
              re.timecompleted
          FROM
              {threesixty_respondent} rt
           LEFT OUTER JOIN
               {threesixty_response} re
           ON
               re.respondentid = rt.id
        WHERE
            rt.analysisid = ? AND
            rt.uniquehash IS NOT NULL
        ORDER BY
            rt.email
    ";

      $respondents = $DB->get_records_sql($sql, array($analysisid));
      return $respondents;
}

function count_respondents($userid, $activityid){
    global $CFG, $DB;

    $numrespondents = array();
    $sql = "
        SELECT
            COUNT(1)
        FROM
            {threesixty_respondent} r
        JOIN
            {threesixty_analysis} a
        ON
            r.analysisid = a.id
        WHERE
            a.userid = ? AND
            a.activityid = ? AND
            r.uniquehash IS NOT NULL
    ";
    $numrespondents['sent'] = $DB->count_records_sql($sql, array($userid, $activityid));
    $sql2 = "
        SELECT
            COUNT(1)
        FROM
            {threesixty_respondent} r
        JOIN
            {threesixty_analysis} a
        ON
            r.analysisid = a.id
        JOIN
            {threesixty_response} rr
        ON
            r.id = rr.respondentid AND a.id = rr.analysisid
        WHERE
            a.userid = ? AND
            a.activityid = ? AND
            r.uniquehash IS NOT NULL AND
            rr.timecompleted > 0
    ";
    $numrespondents['received'] = $DB->count_records_sql($sql2, array($userid, $activityid));

    return $numrespondents;
}

class threesixty_user extends \core_user {
    public static function get_dummy_threesixty_user($email = '', $firstname = '', $lastname = '') {
        $user = self::get_dummy_user_record();
        $user->maildisplay = true;
        $user->mailformat = 1;
        $user->email = $email;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->username = 'threesixtyuser';
        $user->timezone = date_default_timezone_get();
        return $user;
    }
}