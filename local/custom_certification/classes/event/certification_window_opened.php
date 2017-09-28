<?php

namespace local_custom_certification\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The certification_window_opened certification event class.
 */
class certification_window_opened extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'certif_completions';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return 'Window for user id ' . $this->userid . ', certification id ' . $this->other['certifid'] . ' has been opened';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcertificationwindowopened', 'local_custom_certification');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return null;
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        return null;
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
    }

    public static function get_objectid_mapping() {
        return ['db' => 'certif_completions', 'restore' => 'certif_completion'];
    }

    public static function get_other_mapping() {
        $othermapped = [];
        return $othermapped;
    }
}
