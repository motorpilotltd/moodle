<?php

require_once $CFG->dirroot.'/lib/formslib.php';

class mod_threesixty_respondents_form extends moodleform {

    protected $_threesixty;

    function definition() {

        $mform =& $this->_form;

        $this->_threesixty = $this->_customdata['threesixty'];
        $typelist = $this->_customdata['typelist'];
        $remaininginvitations = $this->_customdata['remaininginvitations'];

        $mform->addElement('hidden', 'a', $this->_customdata['a']);
        $mform->setType('a', PARAM_INT);
        $mform->addElement('hidden', 'userid', $this->_customdata['userid']);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('html', get_string('requestrespondentexplanation', 'threesixty'));
        if ($remaininginvitations > 0) {
            $a = new stdClass();
            $a->remaining = $remaininginvitations;
            $a->s = '';
            if ($remaininginvitations > 1) {
                $a->s = 's';
            }
            $mform->addElement('html', get_string('requestrespondentremaining', 'threesixty', $a));
        }

        $mform->addElement('select', 'type', get_string('respondenttype', 'threesixty'), $typelist);
        $mform->setType('type', PARAM_INT);

        $mform->addElement('html', html_writer::empty_tag('br').html_writer::empty_tag('br'));

        $peers = false;
        switch ($this->_threesixty->respondentselection) {
            case 'saml' :
                $peers = array('' => '') + $this->_get_ad_users();
                break;
            case 'moodle' :
                $peers = array('' => '') + $this->_get_moodle_users();
            default :
                break;
        }

        if ($peers) {
            $mform->addElement(
                'select',
                'respondentuserid',
                get_string('respondentuserid', 'threesixty'),
                $peers,
                array(
                    'class' => 'chosen-select',
                    'data-placeholder' => get_string('choosedots')
                )
            );
        }
        if (!$this->_threesixty->allowexternalrespondents) {
            $mform->addRule('respondentuserid', null, 'required', null, 'client');
        } else {
            if ($peers) {
                $mform->addElement('static', 'static', get_string('or', 'threesixty'));
            }

            $mform->addElement('text', 'firstname', get_string('firstname'));
            $mform->setType('firstname', PARAM_TEXT);

            $mform->addElement('text', 'lastname', get_string('lastname'));
            $mform->setType('lastname', PARAM_TEXT);

            $mform->addElement('text', 'email', get_string('email'));
            $mform->setType('email', PARAM_EMAIL);
            $mform->addRule('email', get_string('invalidemail'), 'email', null, 'client');
        }

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'send', get_string('sendemail', 'threesixty'), array('class' => 'btn-primary'));
        if ($remaininginvitations <= 0) {
            $buttonarray[] = &$mform->createElement('submit', 'done', get_string('done', 'threesixty'), array('class' => 'btn-default'));
        }
        $mform->addGroup($buttonarray, 'buttonarray');
        $mform->closeHeaderBefore('buttonarray');

    }

    function validation($data, $files) {
        global $DB;

        if (isset($data['buttonarray']['done'])) {
            return array();
        }

        $errors = parent::validation($data, $files);
        $analysisid = $this->_customdata['analysisid'];

        if ($this->_threesixty->allowexternalrespondents && empty($data['respondentuserid'])) {
            if (empty($data['firstname'])) {
                $errors['firstname'] = get_string('requiredifnouser', 'threesixty');
            }
            if (empty($data['lastname'])) {
                $errors['lastname'] = get_string('requiredifnouser', 'threesixty');
            }
            if (empty($data['email'])) {
                $errors['email'] = get_string('requiredifnouser', 'threesixty');
            } elseif ($DB->get_field('threesixty_respondent', 'id', array('analysisid' => $analysisid, 'email' => strtolower($data['email'])))) {
                $errors['email'] = get_string('validation:emailnotunique', 'threesixty');
            }
        } else {
            $selectedrespondent = explode('|||', $data['respondentuserid']);
            if ($DB->get_field('threesixty_respondent', 'id', array('analysisid' => $analysisid, 'email' => strtolower($selectedrespondent[0])))) {
                $errors['respondentuserid'] = get_string('validation:respondentalreadyselected', 'threesixty');
            }
        }

        return $errors;
    }

    protected function _get_moodle_users() {
        global $CFG, $DB;

        $concat1 = $DB->sql_concat('email', "'|||'", 'MAX(firstname)', "'|||'", 'MAX(lastname)');
        $concat2 = $DB->sql_concat('MAX(firstname)', "' '", 'MAX(lastname)', "' ('", 'email', "')'");
        $sql = <<<EOS
SELECT
    {$concat1} as value, {$concat2} as text
FROM
    {user}
WHERE
    id <> :guestid
    AND id != :userid
    AND deleted = 0
    AND confirmed = 1
    AND email != :email
GROUP BY
    email
ORDER BY
    MAX(lastname) ASC
EOS;
        $params = array(
            'guestid' => $CFG->siteguest,
            'userid' => $this->_customdata['userid'],
            'email' => '',
        );
        return $DB->get_records_sql_menu($sql, $params);
    }

    protected function _get_ad_users() {
        $samlauth = get_auth_plugin('saml');
        $mappings = array(
            'email' => '',
            'firstname' => '',
            'lastname' => '',
        );
        $mappingerrors = array();
        foreach ($mappings as $field => $mapping) {
            $config = 'ldap_map_'.$field;
            if (empty($samlauth->config->{$config})) {
                $mappingerrors[''] = "Error: {$field} not mapped.";
            } else {
                $mappings[$field] = strtolower($samlauth->config->{$config});
            }
        }
        if (!empty($mappingerrors)) {
            return $mappingerrors;
        }

        $entries = array();
        $users = array();

        $filter = '(&';
        foreach ($mappings as $mapping) {
            $filter .= "({$mapping}=*)";
        }
        $filter .= ')';

        $ldapconnection = $samlauth->ldap_connect();
        $ldap_pagedresults = ldap_paged_results_supported($samlauth->config->ldap_version);
        $ldap_cookie = '';
        $contexts = explode(';', $samlauth->config->contexts);

        foreach ($contexts as $context) {
            $context = trim($context);
            if (empty($context)) {
                continue;
            }
            do {
                if ($ldap_pagedresults) {
                    ldap_control_paged_result($ldapconnection, 30, false, $ldap_cookie);
                }
                $ldap_result = ldap_search($ldapconnection, $context, $filter, array_values($mappings), 0, 30);
                if(!$ldap_result) {
                    continue;
                }
                if ($ldap_pagedresults) {
                    ldap_control_paged_result_response($ldapconnection, $ldap_result, $ldap_cookie);
                }
                $entries[] = ldap_get_entries($ldapconnection, $ldap_result);
                unset($ldap_result); // Free mem.
            } while ($ldap_pagedresults && !empty($ldap_cookie));
        }

        foreach ($entries as $entry) {
            if ($entry) {
                foreach ($entry as $user) {
                    $index = $user[$mappings['email']][0] . '|||' . $user[$mappings['firstname']][0] . '|||' . $user[$mappings['lastname']][0];
                    $users[$index] =
                        $user[$mappings['firstname']][0] .
                        ' ' .
                        $user[$mappings['lastname']][0] .
                        ' ('.
                        $user[$mappings['email']][0] .
                        ')';
                }
            }
        }

        if ($ldap_pagedresults) {
            $samlauth->ldap_close(true);
        }

        return $users;
    }
}
