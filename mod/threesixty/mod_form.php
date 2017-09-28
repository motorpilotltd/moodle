<?php

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_threesixty_mod_form extends moodleform_mod {

    protected $_similargroups = array(
        'title', 'label', 'axis', 'legend'
    );

    function definition() {
        global $CFG, $OUTPUT, $PAGE;

        $mform =& $this->_form;

        // Initialise font options
        $fontfiles = get_directory_list("{$CFG->dirroot}/mod/threesixty/lib/pChart/fonts", '', false, false, true);
        $fonts = array();
        foreach ($this->_similargroups as $which) {
            $fonts[$which] = &$mform->createElement('select', $which.'font', get_string('spiderweb:font:'.$which, 'threesixty'), array_combine($fontfiles, $fontfiles));
            $mform->setDefault($which.'font', 'Arimo-Regular.ttf');
        }
        $fontsizes = array();
        foreach ($this->_similargroups as $which) {
            $fontsizes[$which] = &$mform->createElement('text', $which.'fontsize', get_string('spiderweb:fontsize:'.$which, 'threesixty'));
            $mform->setType($which.'fontsize', PARAM_INT);
        }
        $mform->setDefault('titlefontsize', 16);
        $mform->setDefault('labelfontsize', 12);
        $mform->setDefault('axisfontsize', 12);
        $mform->setDefault('legendfontsize', 11);

        // Initialise colour pickers
        $colourpickerhtml = html_writer::tag(
            'div',
            $OUTPUT->pix_icon('i/loading',
                get_string('loading', 'admin'),
                'moodle',
                array('class'=>'loadingicon')
            ),
            array(
                'class'=>'admin_colourpicker clearfix'
            )
        );
        $colourpickers = array();
        foreach ($this->_similargroups as $which) {
            $colourpickers[$which][] = &$mform->createElement('static', $which.'colourpicker', null, $colourpickerhtml);
            $colourpickers[$which][] = &$mform->createElement('text', $which.'colour', get_string('spiderweb:colour:'.$which, 'threesixty'));
            $mform->setType($which.'colour', 'text');
            $mform->setDefault($which.'colour', '#000000');
            $PAGE->requires->js_init_call('M.util.init_colour_picker', array("id_{$which}colour", null));
        }

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        $mform->setType('name', PARAM_CLEANHTML);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('intro', 'threesixty'));

        $requiredrespondents = array();
        for ($i = 0; $i <= 20; $i += 1) {
            $requiredrespondents[$i] = $i;
        }
        $mform->addElement('select', 'requiredrespondents', get_string('requiredrespondents', 'threesixty'), $requiredrespondents);
        $mform->setDefault('requiredrespondents', 3);
        $mform->addHelpButton('requiredrespondents', 'requiredrespondents', 'threesixty');

        $respondentselection = array ('external' => get_string('externalrespondentsonly', 'threesixty'), 'moodle' => 'Moodle');
        if (is_enabled_auth('saml')) {
            $respondentselection['saml'] = 'Active Directory';
        }

        $mform->addElement('select', 'respondentselection', get_string('respondentselection', 'threesixty'), $respondentselection);
        $mform->setDefault('respondentselection', '');

        $mform->addElement('selectyesno', 'allowexternalrespondents', get_string('allowexternalrespondents', 'threesixty'));
        $mform->setDefault('allowexternalrespondents', 1);
        $mform->disabledIf('allowexternalrespondents', 'respondentselection', 'eq', 'external');

        $mform->addElement('textarea', 'selftypes', get_string('selftypes', 'threesixty'));

        $mform->addElement('textarea', 'respondenttypes', get_string('respondenttypes', 'threesixty'));

        $mform->addElement('modgrade', 'skillgrade', get_string('skillgrade', 'threesixty', core_text::strtolower(get_string('competencies', 'threesixty'))));
        $mform->setDefault('skillgrade', 4);

        $mform->addElement('modgrade', 'grade', get_string('grade'));
        $mform->setDefault('grade', 100);

        $mform->addElement('select', 'divisor', get_string('divisor', 'threesixty'), array_combine(range(1, 100, 1), range(1, 100, 1)));
        $mform->setDefault('divisor', 10);

        $questionorders = array(
            'competency' => get_string('questionorder:competency', 'threesixty', core_text::strtolower(get_string('competency', 'threesixty'))),
            'alternate' => get_string('questionorder:alternate', 'threesixty', core_text::strtolower(get_string('competencies', 'threesixty'))),
            'random' => get_string('questionorder:random', 'threesixty')
        );
        $mform->addElement('select', 'questionorder', get_string('questionorder', 'threesixty'), $questionorders);
        $mform->setDefault('questionorder', 'competency');

        $mform->addElement('selectyesno', 'competencylabels', get_string('competencylabels', 'threesixty', core_text::strtolower(get_string('competency', 'threesixty'))));
        $mform->setDefault('competencylabels', 1);

        $mform->addElement('text', 'competencyalternative', get_string('alternative', 'threesixty', get_string('competency', 'threesixty')));
        $mform->setType('competencyalternative', PARAM_TEXT);

        $mform->addElement('text', 'competencyalternativeplural', get_string('alternative', 'threesixty', get_string('competencies', 'threesixty')));
        $mform->setType('competencyalternativeplural', PARAM_TEXT);
        $mform->addHelpButton('competencyalternativeplural', 'alternativepluralhelp', 'threesixty');

        $mform->addElement('text', 'skillalternative', get_string('alternative', 'threesixty', get_string('skill', 'threesixty')));
        $mform->setType('skillalternative', PARAM_TEXT);

        $mform->addElement('text', 'skillalternativeplural', get_string('alternative', 'threesixty', get_string('skills', 'threesixty')));
        $mform->setType('skillalternativeplural', PARAM_TEXT);
        $mform->addHelpButton('skillalternativeplural', 'alternativepluralhelp', 'threesixty');

        // This utilises custom core modifications that allow the use of timezones
        $mform->addElement('date_time_selector', 'reportsavailable', get_string('reportsavailable', 'threesixty'), array('startyear' => date('Y'), 'showtz' => true, 'optional' => false));
        $mform->setDefault('reportsavailable', array('timestart' => time(), 'timezone' => 'Europe/London'));

        $mform->addElement('header', 'spiderweb', get_string('spiderweb', 'threesixty'));
        $mform->setExpanded('spiderweb');

        $fileoptions = array();
        $fileoptions['mainfile'] = false;
        $fileoptions['subdirs'] = false;
        $fileoptions['maxfiles'] = 1;
        $fileoptions['accepted_types'] = array('.jpg','.jpeg','.gif','.png');
        $fileoptions['return_types'] = FILE_INTERNAL;
        $mform->addElement('filemanager', 'spiderbackground', get_string('spiderbackground' , 'threesixty'), null, $fileoptions);
        $mform->setType('spiderbackground', PARAM_FILE);

        foreach ($this->_similargroups as $which) {
            if ($which == 'label') {
                $mform->addElement('advcheckbox', 'labelshow', get_string('spiderweb:show:label', 'threesixty'));
                $mform->setDefault('labelshow', 1);

                $mform->disabledIf($fonts[$which], 'labelshow', 'notchecked');
                $mform->disabledIf($fontsizes[$which], 'labelshow', 'notchecked');
                $mform->disabledIf($which.'colourpickerar', 'labelshow', 'notchecked');
            }
            $mform->addElement($fonts[$which]);
            $mform->addElement($fontsizes[$which]);
            $mform->addGroup($colourpickers[$which], $which.'colourpickerar', get_string('spiderweb:colour:'.$which, 'threesixty'), '', false);
        }

        $mform->addElement('text', 'legendboxsize', get_string('spiderweb:boxsize:legend', 'threesixty'));
        $mform->setType('legendboxsize', PARAM_INT);
        $mform->setDefault('legendboxsize', 10);

        $mform->addElement('text', 'serieslineweight', get_string('spiderweb:lineweight:series', 'threesixty'));
        $mform->setType('serieslineweight', PARAM_INT);
        $mform->setDefault('serieslineweight', 2);

        $mform->addElement('text', 'seriespointradius', get_string('spiderweb:pointradius:series', 'threesixty'));
        $mform->setType('seriespointradius', PARAM_INT);
        $mform->setDefault('seriespointradius', 4);

        $features = new stdClass;
        $features->groups = false;
        $features->groupings = false;
        $features->groupmembersonly = false;
        $features->outcomes = true;
        $features->gradecat = true;
        $features->idnumber = true;
        $this->standard_coursemodule_elements($features);
        $this->add_action_buttons();

    }

    function set_data($defaults){
        global $CFG;
        if (empty($defaults->selftypes)){
            $defaults->selftypes = $CFG->threesixty_selftypes;
        }

        if (empty($defaults->respondenttypes)){
            $defaults->respondenttypes = $CFG->threesixty_respondenttypes;
        }

        if (isset($this->current) && !empty($this->current->spiderwebsettings)) {
            $spiderwebsettings = unserialize(base64_decode($this->current->spiderwebsettings));
            foreach ($spiderwebsettings as $field => $setting) {
                $defaults->{$field} = $setting;
            }
        }

        parent::set_data($defaults);
    }

    function data_preprocessing(&$default_values) {
        parent::data_preprocessing($default_values);
        $competencyalternative = empty($default_values['competencyalternative']) ? array() : explode('|||', $default_values['competencyalternative']);;
        $skillalternative = empty($default_values['skillalternative']) ? array() : explode('|||', $default_values['skillalternative']);
        $default_values['competencyalternative'] = !empty($competencyalternative[0]) ? $competencyalternative[0] : '';
        $default_values['competencyalternativeplural'] = !empty($competencyalternative[1]) ? $competencyalternative[1] : '';
        $default_values['skillalternative'] = !empty($skillalternative[0]) ? $skillalternative[0] : '';
        $default_values['skillalternativeplural'] = !empty($skillalternative[1]) ? $skillalternative[1] : '';

        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('spiderbackground');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_threesixty', 'spiderbackground', 0);
            $default_values['spiderbackground'] = $draftitemid;
        }

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $colourfields = array(
            'title',
            'label',
            'axis',
            'legend'
        );
        foreach ($colourfields as $field) {
            $colourok =
                empty($data[$field.'colour'])
                || preg_match('/^#[[:xdigit:]]{6}$/', $data[$field.'colour']);

            if (!$colourok) {
                $errors[$field.'colourpickerar'] = 'Invalid hexadecimal colour entered (e.g. #000000)';
            }
        }

        return $errors;
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }

        // Alternative words
        if (!empty($data->competencyalternative)) {
            $data->competencyalternative = $data->competencyalternative . '|||' . $data->competencyalternativeplural;
        } else {
            $data->competencyalternative = '';
        }
        if (!empty($data->skillalternative)) {
            $data->skillalternative = $data->skillalternative . '|||' . $data->skillalternativeplural;
        } else {
            $data->skillalternative = '';
        }

        // Respondent selection options
        if ($data->respondentselection == 'external') {
            $data->allowexternalrespondents = 1;
        }

        // Spiderweb settings
        $settings = array();
        $elements = array(
            'labelshow',
            'legendboxsize',
            'serieslineweight',
            'seriespointradius',
        );
        foreach ($elements as $element) {
            if (isset($data->{$element})) {
                $settings[$element] = $data->{$element};
            }
        }
        foreach ($this->_similargroups as $group) {
            foreach (array('font', 'fontsize', 'colour') as $element) {
                $which = $group.$element;
                if (isset($data->{$which})) {
                    $settings[$which] = $data->{$which};
                }
            }
        }
        $data->spiderwebsettings = base64_encode(serialize($settings));

        return $data;
    }
}
