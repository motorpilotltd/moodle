<?php
namespace local_custom_certification\form;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

class certification_messages_form extends \moodleform
{
    function definition()
    {
        global $PAGE;
        $mform =& $this->_form;
        $certif = $this->_customdata['certif'];
        $messagetypes = $this->_customdata['messagetypes'];
        $messages = $this->_customdata['messages'];
        $renderer = $PAGE->get_renderer('local_custom_certification');
        $mform->addElement('header', 'programdetails', get_string('certificationmesssageheader', 'local_custom_certification'));

        $messagebox = \html_writer::tag('p', get_string('instructions:certificationinfo', 'local_custom_certification'), ['class' => 'instructions']);

        $messagebox .= \html_writer::start_div('message-container');
        foreach ($messages as $message) {
            $messagebox .= $renderer->display_message_box($message->id, $messagetypes[$message->messagetype], $message->messagetype, $message->recipient, $message->recipientemail, $message->subject, $message->body, $message->triggertime);
        }

        $messagebox .= \html_writer::end_div();

        $mform->addElement('html', $messagebox);

        $section[] = $mform->createElement('select', 'messagetypes', get_string('messagetypes', 'local_custom_certification'), $messagetypes, []);
        $section [] = $mform->createElement('button', 'addmessage', get_string("addmessage", 'local_custom_certification'), ['class' => 'form-submit']);
        $mform->addGroup($section, 'addmessagegroup', false, ' ', false);
        $mform->addElement('button', 'savemessage', get_string("savemessage", 'local_custom_certification'), ['class'=>'form-submit', 'id' => 'savemessage', 'data-certifid' => $certif->id]);
        $mform->closeHeaderBefore('select');
    }
}
