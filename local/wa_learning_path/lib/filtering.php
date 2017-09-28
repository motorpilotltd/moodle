<?php

namespace wa_learning_path\lib;

/*
 * Filtering class.
 *
 * @package     wa_learning_path
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */
global $CFG;
require_once($CFG->dirroot . '/user/filters/lib.php');

/**
 * Contructor
 * @param array $fieldnames array of visible user fields
 * @param string $baseurl base url used for submission/return, null if the same of current page
 * @param array $extraparams extra page parameters
 */
class wa_filtering extends \user_filtering {

    /**
     * Main data session key: $SESSION->{$this->sessionkey}.
     * @var string
     */
    private $sessionkey = 'wa_learning_path';

    /**
     * Custtom session key in main data container: $SESSION->{$this->sessionkey}[$key].
     * @var string
     */
    public $key = null;

    public function __construct($fieldnames = null, $baseurl = null, $extraparams = null, $key = null) {
        global $SESSION;

        $this->key = trim($key);

        // Prepare data container.
        if (!empty($this->key) && !isset($SESSION->{$this->sessionkey})) {
            $SESSION->{$this->sessionkey} = array($this->key => array());
        }
        
        // Set filters from separated array to current.
        $this->set_current_filters();

        parent::__construct($fieldnames, $baseurl, $extraparams);

        // Save filters in separated array.
        $this->save_current_filters();
    }

    /**
     * Save filters in separated array.
     * @global type $SESSION
     */
    public function save_current_filters() {
        global $SESSION;

        if (!empty($this->key)) {
            $SESSION->{$this->sessionkey}[$this->key] = $SESSION->user_filtering;
        }
    }

    /**
     * Set filters from separated array to current
     * @global \wa_learning_path\lib\type $SESSION
     */
    public function set_current_filters() {
        global $SESSION;

        if (!empty($this->key)) {
            if (!isset($SESSION->{$this->sessionkey}[$this->key])) {
                $SESSION->{$this->sessionkey}[$this->key] = array();
            }
            $SESSION->user_filtering = $SESSION->{$this->sessionkey}[$this->key];
        }
    }

    /**
     * Creates known user filter if present
     * @param string $fieldname
     * @param boolean $advanced
     * @return object filter
     */
    public function get_field($fieldname, $advanced) {
        $filter = parent::get_field($fieldname, $advanced);

        if (is_null($filter)) {
            return $this->get_external_field($fieldname, $advanced);
        }

        return $filter;
    }

    /**
     * Creates addition/external filter if present
     * @param string $fieldname
     * @param boolean $advanced
     * @return object filter
     */
    public function get_external_field($fieldname, $advanced) {
        global $CFG, $DB;

        switch ($fieldname) {
            case 'title':
                require_once($CFG->dirroot . '/user/filters/text.php');
                return new \user_filter_text('title',
                        get_string('title', 'local_wa_learning_path'), $advanced, 'a.title');
                break;
            
            case 'lp_title':
                require_once($CFG->dirroot . '/user/filters/text.php');
                return new \user_filter_text('title',
                        get_string('title', 'local_wa_learning_path'), $advanced, 'c.title');
                break;

            case 'type':
                \wa_learning_path\lib\load_form('addactivity');
                $form = new \wa_learning_path\form\addactivity_form();
                require_once($CFG->dirroot . '/user/filters/select.php');
                
                return new \user_filter_select('type', get_string('activity_type', 'local_wa_learning_path'),
                    $advanced, 'a.type', $form->get_activity_type(false), '');
                break;

            case 'region':
                require_once($CFG->dirroot . '/user/filters/select.php');
                return new \user_filter_simpleselect('region', get_string('region', 'local_wa_learning_path'),
                    $advanced, 'ar.regionid', \wa_learning_path\lib\get_regions(), '');
                break;
            
            case 'lp_region':
                require_once($CFG->dirroot . '/user/filters/select.php');
                return new \user_filter_simpleselect('region', get_string('region', 'local_wa_learning_path'),
                    $advanced, 'lr.regionid', \wa_learning_path\lib\get_regions(), '');
                break;
            
            case 'published_region':
                require_once($CFG->dirroot . '/user/filters/select.php');
                return new \user_filter_simpleselect('region', get_string('region', 'local_wa_learning_path'),
                    $advanced, 'lpr.regionid', \wa_learning_path\lib\get_regions(), '');
                break;
            
            case 'lp_status':
                require_once($CFG->dirroot . '/user/filters/select.php');
                $pluginname = 'local_wa_learning_path';
                $options = array(
                    WA_LEARNING_PATH_PUBLISH => get_string('publish', $pluginname),
                    WA_LEARNING_PATH_PUBLISH_NOT_VISIBLE => get_string('publish_not_visible', $pluginname),
                    WA_LEARNING_PATH_DRAFT => get_string('draft', $pluginname)
                );
                return new \user_filter_select('status', get_string('is_published', 'local_wa_learning_path'),
                    $advanced, 'c.status', $options, '');
                break;

            default:
                return null;
                break;
        }
    }

}
