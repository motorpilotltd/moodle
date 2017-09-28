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
        global $PAGE;

        $PAGE->requires->css(new moodle_url('/blocks/certification_report/styles/certification_report.css'));
        $PAGE->requires->js(new moodle_url('/blocks/certification_report/js/certification_report.js'));

        if($this->content !== NULL) {
            return $this->content;
        }


        $this->content = new stdClass();
        $this->content->text = '';;
        $this->content->footer = '';

        /**
         * Verify capabilities
         */
        if(!has_capability('block/certification_report:view', context_system::instance())){
            $this->content->text = get_string('nopermissions', 'block_certification_report');
        } else {
            $reporturl = new moodle_url('/blocks/certification_report/report.php');
            $this->content->text = html_writer::link($reporturl, get_string('viewreport', 'block_certification_report'));
            $this->content->text .= $this->get_demo_report_link();
        }

        return $this->content;
    }

    private function get_demo_report_link() {
        global $DB, $OUTPUT, $USER;

        if (has_capability('block/certification_report:view_all_costcentres', context_system::instance())) {
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
                costcentre::LEARNING_REPORTER,
            ]);
        }

        if (!empty($costcentres)) {
            $config = get_config('block_certification_report');
            $params = [];
            if (!empty($config->report_certifications)) {
                $certifications = explode(',', $config->report_certifications);
                $i = 0;
                foreach ($certifications as $certification) {
                    $params["certifications[{$i}]"] = (int) trim($certification);
                    $i++;
                }
            }
            $url = new moodle_url('/blocks/certification_report/report.php', $params);
            $select = new single_select($url, 'costcentres[]', $costcentres);
            $select->set_label('Choose cost centre');
            $title = !empty($config->report_title) ? $config->report_title : 'Featured Report';
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

}
