<?php

namespace local_custom_certification\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_certification_expired event class.
 */
class certification_expired extends \core\event\base {
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
        return 'Certification with id ' . $this->other['certifid'] . ' has expired for the user with id ' . $this->userid . ' ';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcertificationexpired', 'local_custom_certification');
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
        return [];
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
    }

    public static function get_objectid_mapping() {
        return ['db' => 'certif_completions', 'restore' => 'certif_completion'];
    }

    public static function get_other_mapping() {
        $othermapped = [];
        return $othermapped;
    }
}
