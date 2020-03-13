<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 26/02/2019
 * Time: 14:54
 */

namespace local_panels;

require_once("$CFG->dirroot/cohort/lib.php");

class panel {
    const DATASOURCEMODE_ZONE = 10; // Data sources are linked to specific zones.
    const DATASOURCEMODE_PANEL = 20; // Connect one data source at panel level, fill zones sequentially.

    /**
     * @var layout
     */
    public $layout;
    /**
     * @var datasource[]
     */
    public $datasources = [];
    /**
     * @var
     */
    public $datasourcemode;

    public $publishstartdate;
    public $publishenddate;
    public $cohorts;

    public $title;
    public $subtitle;
    public $linktext;
    public $linkurl;

    public $context;
    /**
     * @var
     */
    public $id;

    public function __construct($context, $panelid) {

        $this->datasourcemode = \local_panels\panel::DATASOURCEMODE_PANEL;
        $this->layout = new \panellayout_foursquare\layout();
        $this->id = $panelid;
        $this->context = $context;
    }

    public function render() {
        $data = $this->get_layout_data();
        return $this->layout->render($data);
    }

    /**
     * @param $mform \MoodleQuickForm
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function addtoform($mform) {
        global $DB;

        $modes = [
                panel::DATASOURCEMODE_ZONE  => get_string('datasourcemodezone', 'local_panels'),
                panel::DATASOURCEMODE_PANEL => get_string('datasourcemodepanel', 'local_panels')
        ];
        $datasourcetypes = array_keys(\core_component::get_plugin_list('datasource'));
        $datasourcetypes = array_combine($datasourcetypes, $datasourcetypes);

        $id = $this->id;

        $prefix = "panel-$id";

        $layouttypes = array_keys(\core_component::get_plugin_list('panellayout'));
        $layouttypes = array_combine($layouttypes, $layouttypes);
        $mform->addElement('header', $prefix, $prefix);

        $mform->setExpanded($prefix, false);

        $mform->addElement('text', "$prefix-title", get_string('paneltitle', 'local_panels'));
        $mform->setType("$prefix-title", PARAM_TEXT);

        $mform->addElement('text', "$prefix-subtitle", get_string('panelsubtitle', 'local_panels'));
        $mform->setType("$prefix-subtitle", PARAM_TEXT);

        $mform->addElement('text', "$prefix-linktext", get_string('panellinktext', 'local_panels'));
        $mform->setType("$prefix-linktext", PARAM_TEXT);

        $mform->addElement('text', "$prefix-linkurl", get_string('panellinkurl', 'local_panels'));
        $mform->setType("$prefix-linkurl", PARAM_URL);

        $mform->addElement('select', "$prefix-layout", get_string('panellayouttype', 'local_panels'),
                $layouttypes);

        $options = [];
        $cohorts = \cohort_get_available_cohorts($this->context);
        foreach ($cohorts as $cohort) {
            $options[$cohort->id] = $cohort->name;
        }
        $size = min(array(count($options), 10));
        $cohorts = &$mform->addElement('select', "$prefix-cohorts", get_string('cohorts', 'local_panels'), $options,
                array('size' => $size, 'style' => 'min-width:200px'));
        $cohorts->setMultiple(true);
        $mform->setDefault( "$prefix-cohorts", 0);

        $mform->addElement('date_time_selector', "$prefix-publishstartdate", get_string('publishstartdate', 'local_panels'), array('optional' => true));

        $mform->addElement('date_time_selector', "$prefix-publishenddate", get_string('publishenddate', 'local_panels'), array('optional' => true));


        $getzonecount = $this->layout->getzonecount();

        if ($getzonecount) {
            $mform->addElement('select', "$prefix-datasourcemode", get_string('datasourcemode', 'local_panels'),
                $modes, ['class' => 'datasourcemodeselector', 'data-panelid' => $id]);
        }

        for ($zoneid = 0; $zoneid < $getzonecount; $zoneid++) {
            $zoneprefix = "$prefix-zone-$zoneid";
            $mform->addElement('html', \html_writer::start_span("panelzonewrapper $zoneprefix"));

            $mform->addElement('header', $zoneprefix, $zoneprefix);

            $mform->addElement('select', "$zoneprefix-datasourcetype", get_string('paneldatasourcetype', 'local_panels'),
                    $datasourcetypes, ['class' => 'datasourcetypeselector', 'data-panelid' => $id, 'data-zoneid'=> $zoneid]);


            foreach ($datasourcetypes as $datasourcetype) {
                $mform->addElement('html', \html_writer::start_span("datasourceconfig $zoneprefix-$datasourcetype"));
                $modifierclass = "\\datasource_$datasourcetype\\datasource";
                $modifierclass::addtoform($mform, $zoneprefix, $this->layout->zonecantakearray($zoneid));
                $mform->addElement('html', \html_writer::end_span());
            }
            $mform->addElement('html', \html_writer::end_span());
        }
    }

    /**
     * @param $mform \MoodleQuickForm
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function pickle() {

        $id = $this->id;

        $prefix = "panel-$id";

        $values = [
                "$prefix-layout" => $this->layout->getmachinename(),
                "$prefix-cohorts" => $this->cohorts,
                "$prefix-publishstartdate" => $this->publishstartdate,
                "$prefix-publishenddate" => $this->publishenddate,
                "$prefix-datasourcemode" => $this->datasourcemode,
                "$prefix-title" => $this->title,
                "$prefix-subtitle" => $this->subtitle,
                "$prefix-linktext" => $this->linktext,
                "$prefix-linkurl" => $this->linkurl,
        ];
        for ($zoneid = 0; $zoneid < $this->layout->getzonecount(); $zoneid++) {
            if (!isset($this->datasources[$zoneid])) {
                continue;
            }

            $zoneprefix = "$prefix-zone-$zoneid";
            $values["$zoneprefix-datasourcetype"] = $this->datasources[$zoneid]->getmachinename();

            $values += $this->datasources[$zoneid]->pickle($zoneprefix);
        }

        return $values;
    }

    public function preprocessform($formdata) {
        $id = $this->id;
        $prefix = "panel-$id";

        foreach ($this->datasources as $zoneid => $datasource) {
            $formdata = $datasource->preprocessform($formdata);
        }

        return $formdata;
    }

    public function postprocessform($formdata) {
        $id = $this->id;
        $prefix = "panel-$id";

        foreach ($this->datasources as $zoneid => $datasource) {
            $formdata = $datasource->postprocessform();
        }

        return $formdata;
    }

    public function unpickle($data) {
        $id = $this->id;
        $prefix = "panel-$id";

        foreach ($data as $key => $value) {
            if ($key == 'zones') {
                foreach ($value as $zoneid => $zonedata) {
                    if (!isset($zonedata['datasourcetype'])) {
                        continue;
                    }

                    $class = "\datasource_" . $zonedata['datasourcetype'] . "\datasource";
                    $zonedatasource = new $class($this->context, "$prefix-zone-$zoneid");
                    $zonedatasource->id = $zoneid;
                    $zonedatasource->unpickle($zonedata);
                    $this->datasources[$zoneid] = $zonedatasource;
                }
            } else if ($key == 'layout') {
                $class = "\panellayout_" . $value . "\layout";
                $this->layout = new $class();
            } else if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function get_data() {
        $data = new \stdClass();

        $data->panelid = $this->id;
        $data->title = $this->title;
        $data->subtitle = $this->subtitle;
        $data->linktext = $this->linktext;
        $data->linkurl = $this->linkurl;
        $data->panelcontent = $this->render();
        $data->backgroundpanel = $this->is_backgroundpanel();

        return $data;
    }

    public function is_backgroundpanel() {
        return $this->layout->is_backgroundpanel();
    }

    private function get_layout_data() {
        $data = new \stdClass();

        $data->panelid = $this->id;

        if (empty($this->datasources)) {
            return $data;
        }

        $getzonecount = $this->layout->getzonecount();
        switch ($this->datasourcemode) {
            case self::DATASOURCEMODE_PANEL:
                $datasource = reset($this->datasources);

                for ($i = 0; $i < $getzonecount; $i++) {
                    if ($datasource) {
                        $content =  $datasource->rendernextitem($this->layout->getzonesize($i));
                    } else {
                        $content = '';
                    }
                    $data->{"zone" . $i . "content"} = $content;
                }

                break;
            case self::DATASOURCEMODE_ZONE:
                for ($i = 0; $i < $getzonecount; $i++) {
                    if (!isset($this->datasources[$i])) {
                        $data->{"zone" . $i . "content"} = '';
                        continue;
                    }

                    $datasource = $this->datasources[$i];
                    if ($this->layout->zonecantakearray($i)) {
                        $data->{"zone" . $i . "content"} = $datasource->renderallitems($this->layout->getzonesize($i));
                    } else {
                        $data->{"zone" . $i . "content"} = $datasource->renderitem($this->layout->getzonesize($i));
                    }
                }

                break;
        }

        return $data;
    }

    public function is_renderable() {
        global $USER;

        if (!empty($this->cohorts)) {
            $usercohorts = cohort_get_user_cohorts($USER->id);
            $usercohortids = array_keys($usercohorts);
            if (empty(array_intersect($usercohortids, $this->cohorts))) {
                return false;
            }
        }

        $now = time();
        return
                (empty($this->publishstartdate) || $this->publishstartdate <= $now)
                &&
                (empty($this->publishenddate) || $this->publishenddate >= $now)
                ;
    }
}