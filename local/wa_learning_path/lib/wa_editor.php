<?php
/**
 * Editor with autosave support
 *
 * @package     local_wa_learning_path
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */
require_once($CFG->dirroot.'/lib/form/editor.php');

class MoodleQuickForm_waeditor extends MoodleQuickForm_editor
{
    /** @var array options provided to initalize filepicker */
    protected $_options = array('subdirs' => 0, 'autosave' => false, 'maxbytes' => 0, 'maxfiles' => 0, 'changeformat' => 0,
        'areamaxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED, 'context' => null, 'noclean' => 0, 'trusttext' => 0,
        'return_types' => 7, 'enable_filemanagement' => true);
}

