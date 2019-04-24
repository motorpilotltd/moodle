<?php

use block_certification_report\certification_report;
use local_costcentre\costcentre;
require_once(dirname(__FILE__) . '/form/filter_form.php');

/**
 * certification report block
 */
class block_certification_report extends block_base {

    /**
     * Block initialization
     */
    public function init() {
        $this->title = get_string('header', 'block_certification_report');
    }

    /**
     * Return contents of block
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $PAGE, $DB;

        $PAGE->requires->css(new moodle_url('/blocks/certification_report/styles/certification_report_2019031800.css'));

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';;
        $this->content->footer = '';

        // Get renderer.
        $renderer = $PAGE->get_renderer('block_certification_report');

        /**
         * Verify capabilities
         */
        if(!certification_report::can_view_report()){
            $this->content->text = get_string('nopermissions', 'block_certification_report');
        } else {
            $reporturl = new moodle_url('/blocks/certification_report/report.php');
            $this->content->text = html_writer::link($reporturl, get_string('viewreport', 'block_certification_report'));
            $this->content->text .= $this->get_main_report_link();

            $reportlinks = $this->get_report_links();
            if ($reportlinks) {
                $this->content->text .= html_writer::tag('h3', get_string('heading:reportlinks', 'block_certification_report'));
                $this->content->text .= $renderer->print_reportlink_lists(['reportlinks' => array_values($reportlinks)]);
            }
        }

        // Add manage links for admin
        if (certification_report::is_admin()) {
            $this->content->footer .= $renderer->print_reportlink_manage();
        }

        return $this->content;
    }

    private function get_main_report_link() {
        global $DB, $OUTPUT, $USER;

        if (has_capability('block/certification_report:view_all_costcentres', context_system::instance())
                || has_capability('block/certification_report:view_all_regions', context_system::instance())) {
                $concat = $DB->sql_concat('u.icq', "' - '", 'u.department');
                $sql = <<<EOS
SELECT
    u.icq, {$concat}
FROM
    {user} u
INNER JOIN
    (SELECT
        MAX(id) maxid
    FROM
        {user} inneru
    INNER JOIN
        (SELECT
            icq, MAX(timemodified) as maxtimemodified
        FROM
            {user}
        GROUP BY
            icq) groupedicq
        ON inneru.icq = groupedicq.icq AND inneru.timemodified = groupedicq.maxtimemodified
    GROUP BY
        groupedicq.icq) groupedid
    ON u.id = groupedid.maxid
WHERE
    u.icq != ''
ORDER BY
    u.icq ASC
EOS;
            $distinctusercostcentres = $DB->get_records_menu('local_costcentre_user', array(), 'costcentre ASC', 'DISTINCT costcentre as id, costcentre as value');
            $costcentres = $DB->get_records_sql_menu($sql) + $distinctusercostcentres;
            ksort($costcentres);
        } else {
            $costcentres = costcentre::get_user_cost_centres($USER->id, [
                costcentre::GROUP_LEADER,
                costcentre::HR_LEADER,
                costcentre::HR_ADMIN,
                costcentre::LEARNING_REPORTER,
            ]);
        }

        if (!empty($costcentres)) {
            $url = new moodle_url('/blocks/certification_report/report.php');
            $select = new single_select($url, 'costcentres[]', $costcentres);
            $select->set_label('Choose cost centre');
            $title = get_string('viewcostcentrereport', 'block_certification_report');
            return html_writer::tag('h3', $title) . $OUTPUT->render($select);
        }

        return '';
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }

    private function get_report_links() {
        global $DB, $USER;

        $userregions = $DB->get_record('local_regions_use', ['userid' => $USER->id]);
        if ($userregions) {
            $params = ['georegionid' => $userregions->geotapsregionid, 'actregionid' => $userregions->acttapsregionid];
        } else {
            $params = ['georegionid' => -1, 'actregionid' => -1];
        }
        $select = "(geographicregionid = :georegionid OR geographicregionid = 0) AND (actualregionid = :actregionid OR actualregionid = 0)";

        return $DB->get_records_select('certif_links', $select, $params);
    }

}
