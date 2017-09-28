<?php

namespace local_custom_certification\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_certification_user_assignment_deleted event class.
 */
class user_assignment_deleted extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return 'The user with id ' . $this->userid . ' has been unassigned from certitifaction with id ' . $this->other['certifid'] . ' ';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventuserassignmentdeleted', 'local_custom_certification');
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
        return ['db' => 'certif_user_assignments', 'restore' => 'certif_user_assignment'];
    }

    public static function get_other_mapping() {
        $othermapped = [];
        return $othermapped;
    }
}
