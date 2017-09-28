<?php


require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/lib/formslib.php');

class arup_category_select_form extends moodleform {
    public function __construct($actionurl) {
        parent::moodleform($actionurl);
    }
    function definition() {
        $mform = $this->_form;

        $this->add_category_selector();
        
        $this->add_action_buttons(true, get_string('submit'));
    }
    public function add_category_selector ($required=true) {
        global $DB;
        $mform =& $this->_form;

        if ( is_siteadmin() ) {
            $categories = $DB->get_records('course_categories', array('parent' => 0));
            $categoryoptions = array();
            foreach ($categories as $category) {
                $categoryoptions[$category->id] = $category->name;
            }
            

            if (count($categories) == 1 ) {
                $mform->addElement('html', get_string('nocategories', 'theme_arup'));
                return false;
            } else {
                $select = $mform->addElement('select', 'categoryid', get_string('category'), $categoryoptions);
                $mform->setType('categoryid', PARAM_INT);
                if ($required) {
                    $mform->addRule('categoryid', get_string('missingcategory', 'theme_arup'),
                                    'required', null, 'client');
                }
                $select->setSelected($this->_customdata['categoryid']);
            }
        } 
        return true;
    }
}

class arup_category_settings_form extends moodleform {
    public $categoryid;

    public function __construct($actionurl, $categoryid) {
        $this->categoryid = $categoryid;
        parent::moodleform($actionurl);
    }
    function definition() {
        $mform = $this->_form;

        $categoryid = $this->_customdata['categoryid'];
        $categoryname = $this->_customdata['categoryname'];
        $imagefile = 'categoryimage' . $this->categoryid ;
        $logofile = 'logoimage' . $this->categoryid ;


        $mform->addElement('header', 'categoryname', $categoryname);
        $mform->addElement('hidden', 'categoryid', $categoryid);
        $mform->setType('categoryid', PARAM_INT);

        $mform->addElement('editor', 'categorydescription', get_string('categorydescription', 'theme_arup'));

        $mform->addElement('filepicker', $logofile,
            get_string('logoimage', 'theme_arup'), null,
                array('subdirs' => 0,
                     'maxbytes' => 0,
                     'maxfiles' => 1,
                     'accepted_types' => array('*.jpg', '*.gif', '*.png')));

        $mform->addElement('filepicker', $imagefile,
            get_string('categoryimage', 'theme_arup'), null,
                array('subdirs' => 0,
                     'maxbytes' => 0,
                     'maxfiles' => 1,
                     'accepted_types' => array('*.jpg', '*.gif', '*.png')));


        $this->add_colour_picker('maincolor', '');
        $mform->setType('maincolor', PARAM_RAW);

        $this->add_colour_picker('secondarycolor', '');
        $mform->setType('secondarycolor', PARAM_RAW);

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    public function add_colour_picker($name, $previewconfig) {
        global $PAGE, $OUTPUT;
        $mform =& $this->_form;
        $id = "id_" . $name;

        // Variable $cptemplate is adapted from the 'default' template in formslib.php's MoodleQuickForm_Renderer
        // function in MoodleQuickForm_Renderer class.
        // It is adds a {colourpicker} and {preview} tag that is replaced with the $colourpicker and $preview
        // variables below before being passed to the renderer the {advancedimg} {help} bits have been taken
        // out as the rendered doesn't appear to use them in this case.
        $cptemplate = "\n\t\t".'<div class="fitem {advanced}<!-- BEGIN required --> required<!-- END required -->">
                       <div class="fitemtitle"><label>{label}<!-- BEGIN required -->{req}<!-- END required -->
                       </label></div><div class="felement {type}<!-- BEGIN error --> error<!-- END error -->">
                       {colourpicker}<!-- BEGIN error --><span class="error">{error}</span><br />
                       <!-- END error -->{element}{preview}</div></div>';

        // Variable $colourpicker contains the colour picker bits that are to be displayed above the input box.
        $colourpicker = html_writer::start_tag('div', array('class' => 'form-colourpicker defaultsnext'));
        $colourpicker .= html_writer::tag('div', $OUTPUT->pix_icon('i/loading', get_string('loading', 'admin'),
                                          'moodle', array('class' => 'loadingicon')),
                                          array('class' => 'admin_colourpicker clearfix'));

        // Preview contains the bits that are to be displayed below the input box (may just be a div end tag).
        $preview = '';
        if (!empty($previewconfig)) {
            $preview .= html_writer::empty_tag('input', array('type' => 'button',
                                                              'id' => $id.'_preview',
                                                              'value' => get_string('preview'),
                                                              'class' => 'admin_colourpicker_preview'));
        }
        $preview .= html_writer::end_tag('div');

        // Replace {colourpicker} and {preview} in $cptemplate.
        $cptemplate = preg_replace('/\{colourpicker\}/', $colourpicker, $cptemplate);
        $cptemplate = preg_replace('/\{preview\}/', $preview, $cptemplate);

        // Add the input element to the form.
        $PAGE->requires->js_init_call('M.util.init_colour_picker', array($id, $previewconfig));
        $mform->addElement('text', $name, get_string($name, 'theme_arup'), array('size' => 7, 'maxlength' => 7));
        $mform->defaultRenderer()->setElementTemplate($cptemplate, $name);
        $mform->setType('shortname', PARAM_NOTAGS);
        $mform->addRule($name, get_string('css_color_format', 'theme_arup'), 'regex', '/^#([A-F0-9]{3}){1,2}$/i');
    }
}

require_login(1, true);
$systemcontext = context_system::instance();
$home = new moodle_url('/');

$configuresettings = optional_param('configuresettings', FALSE, PARAM_BOOL);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$selectcategory = true;

if (!$configuresettings) {
    $selectcategory = true;
} else {
    $selectcategory = false;
}

$company = new arup_category_select_form($CFG->wwwroot . '/theme/arup/category-template.php');

if ($company->is_cancelled()) {
    redirect($home);
} else if ($data = $company->get_data()) {
    if ($data->categoryid) {
        $selectcategory = false;
        $configuresettings = true;
        $categoryid = $data->categoryid;
    }
}

if ($configuresettings) {
    
    $categoryrecord = $DB->get_record('course_categories', array('id' => $categoryid));
    $arupsettings = $DB->get_records('config_plugins', array('plugin' => 'theme_arup'));

    $imagefile = 'categoryimage' . $categoryid ;
    $draftitemid1 = file_get_submitted_draft_itemid($imagefile);
    file_prepare_draft_area($draftitemid1,
                        $systemcontext->id,
                        'theme_arup',
                        $imagefile, 0,
                        array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));

    $logofile = 'logoimage' . $categoryid ;
    $draftitemid2 = file_get_submitted_draft_itemid($logofile);
    file_prepare_draft_area($draftitemid2,
                        $systemcontext->id,
                        'theme_arup',
                        $logofile, 0,
                        array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));

    $entry = new stdClass;
    $entry->$imagefile = $draftitemid1;
    $entry->$logofile = $draftitemid2;
    $entry->categoryid = $categoryid;
    $entry->categoryname = $categoryrecord->name;
    $entry->categorydescription['text'] = $categoryrecord->description;

    $catmain = 'maincolor' . $categoryid;
    $catsec = 'secondarycolor' . $categoryid;

    foreach ($arupsettings as $arupsetting) {

        if ($arupsetting->name == $catmain) {
            $entry->maincolor = $arupsetting->value;
        }
        if ($arupsetting->name == $catsec) {
            $entry->secondarycolor = $arupsetting->value;
        }
    }
    

    if (!isset($entry->maincolor)) {
        $entry->maincolor = '';
    }
    if (!isset($entry->secondarycolor)) {
        $entry->secondarycolor = '';
    }

    $settings = new arup_category_settings_form($CFG->wwwroot . '/theme/arup/category-template.php?configuresettings=true', $categoryid);
    $settings->set_data($entry);

    if ($settings->is_cancelled()) {
        redirect($CFG->wwwroot . '/theme/arup/category-template.php?configuresettings=false');
    } else if ($data = $settings->get_data()) {
        file_save_draft_area_files($data->$imagefile,
            $systemcontext->id,
            'theme_arup',
            $imagefile,
            0,
            array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 200));
        file_save_draft_area_files($data->$logofile,
            $systemcontext->id,
            'theme_arup',
            $logofile,
            0,
            array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 200));
        if ($data->maincolor) {
            if ($existing = $DB->get_record('config_plugins', array('plugin' => 'theme_arup', 'name' => $catmain))) {
                $existing->value = $data->maincolor;
                $DB->update_record('config_plugins', $existing);
            } else {
                $catsettings = new Object;
                $catsettings->plugin = 'theme_arup';
                $catsettings->name = $catmain;
                $catsettings->value = $data->maincolor;
                $DB->insert_record('config_plugins', $catsettings);
            }
        }
        if ($data->secondarycolor) {
            if ($existing = $DB->get_record('config_plugins', array('plugin' => 'theme_arup', 'name' => $catsec))) {
                $existing->value = $data->secondarycolor;
                $DB->update_record('config_plugins', $existing);
            } else {
                $catsettings = new Object;
                $catsettings->plugin = 'theme_arup';
                $catsettings->name = $catsec;
                $catsettings->value = $data->secondarycolor;
                $DB->insert_record('config_plugins', $catsettings);
            }
        }
        if ($data->categorydescription) {
            $categoryrecord->description = $data->categorydescription['text'];
            $DB->update_record('course_categories', $categoryrecord);
        }
        redirect($CFG->wwwroot . '/theme/arup/category-template.php?configuresettings=false');
    } 
}

$PAGE->set_url('/theme/arup/category-template.php');
$PAGE->set_title(format_string('Configure Category Template'));
$PAGE->set_heading(format_string('Configure Category Template'));
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add('Configure Category Template');


$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

if ($selectcategory) {
    $company->display();
}
if ($configuresettings) {
    $settings->display();
}

echo $OUTPUT->footer();

?>