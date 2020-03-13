<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 26/02/2019
 * Time: 14:54
 */

namespace local_panels;

class panelset {
    /**
     * @var panel[]
     */
    public $panels = [];

    public $context;

    private $id;

    private function __construct($contextid) {
        $this->context = \context::instance_by_id($contextid);
    }

    public static function fetchbycontextid($contextid) {
        global $DB;

        $raw = $DB->get_record('local_panels', ['contextid' => $contextid]);

        if (!$raw) {
            return false;
        }

        $panelset = new panelset($raw->contextid);
        $panelset->id = $raw->id;
        $panelset->unpickle(json_decode($raw->data));

        return $panelset;
    }

    public function save() {
        global $DB;

        if ($this->id) {
            $raw = $DB->get_record('local_panels', ['id' => $this->id]);
            $raw->data = json_encode($this->pickle());
            $DB->update_record('local_panels', $raw);
        } else {
            $raw = new \stdClass();
            $raw->contextid = $this->context->id;
            $raw->data = json_encode($this->pickle());
            $DB->insert_record('local_panels', $raw);
        }

    }

    /**
     * @return mixed
     */
    public function render() {
        global $OUTPUT;

        $renderedpanels = [];
        $previous = false;
        foreach ($this->panels as $key => $panel) {
            // Background panels need to be rendered within the previous panel.
            if ($panel->is_backgroundpanel()) {
                if ($previous) {
                    $this->panels[$previous]->background =  $panel->get_data();
                    unset($this->panels[$key]);
                }
                continue;
            }
            $previous = $key;
        }


        foreach ($this->panels as $panel) {
            if (!$panel->is_renderable()) {
                continue;
            }
            $paneldata =  $panel->get_data();
            if (isset($panel->background)) {
                $paneldata->background = $panel->background;
            }
            $renderedpanels[] = $paneldata;
        }

        $data = ['panels' => $renderedpanels];

        return $OUTPUT->render_from_template("local_panels/wrapper", $data);
    }

    /**
     * @param $data
     * @return array|panel[]
     */
    private function unpickle($data) {
        $this->panels = [];

        // Turn the form data into a neat data structure that we can use to populate the object graph.
        $paneldata = [];
        foreach ($data as $key => $value) {
            $matches = [];
            preg_match('/^panel-([0-9]+)-zone-([0-9]+)-([a-z]+)$/', $key, $matches, PREG_OFFSET_CAPTURE);

            if ($matches) {
                $panelid = $matches[1][0];
                $zoneid = $matches[2][0];
                $property = $matches[3][0];
            } else {
                preg_match('/^panel-([0-9]+)-([a-z]+)$/', $key, $matches, PREG_OFFSET_CAPTURE);

                if ($matches) {
                    $panelid = $matches[1][0];
                    $property = $matches[2][0];
                    $zoneid = null;
                }
            }

            if (empty($matches)) {
                continue;
            }

            if (!isset($paneldata[$panelid])) {
                $paneldata[$panelid] = [];
            }

            if (!isset($zoneid)) {
                $paneldata[$panelid][$property] = $value;
            } else {

                if (!isset($paneldata[$panelid]['zones'])) {
                    $paneldata[$panelid]['zones'] = [];
                }
                if (!isset($paneldata[$panelid]['zones'][$zoneid])) {
                    $paneldata[$panelid]['zones'][$zoneid] = [];
                }
                $paneldata[$panelid]['zones'][$zoneid][$property] = $value;
            }
        }

        $this->panels = [];
        foreach ($paneldata as $panelid => $rawpanel) {
            $panel = new panel($this->context, $panelid);
            $panel->unpickle($rawpanel);
            $this->panels[$panelid] = $panel;
        }

        if (is_array($data) && !empty($data['panelorder'])) {
            $this->panels = array_replace(array_flip(explode(',', $data['panelorder'])), $this->panels);
        }
    }

    private function postprocessform($data) {
        foreach ($this->panels as $panel) {
            $panel->postprocessform($data);
        }
    }

    private function pickle() {
        $retval = [];

        foreach ($this->panels as $panel) {
            $retval += $panel->pickle();
        }

        return $retval;
    }

    private function preprocessform($formdata) {

        foreach ($this->panels as $panel) {
            $formdata = $panel->preprocessform($formdata);
        }

        return $formdata;
    }

    private function getformdata() {
        $formdata = $this->pickle();
        $formdata = $this->preprocessform($formdata);
        return $formdata;
    }

    private function addnewpanel() {
        if (!empty($this->panels)) {
            $max = max(array_keys($this->panels));
        } else {
            $max = 0;
        }
        $panel = new panel($this->context, $max + 1);
        $this->panels[$panel->id] = $panel;
        return $panel->id;
    }

    /**
     * @return mixed
     */
    public static function renderforediting() {
        global $OUTPUT;

        $contextid = optional_param('contextid', null, PARAM_INT);
        $id = optional_param('id', null, PARAM_INT);

        if ($contextid) {
            $panelset = \local_panels\panelset::fetchbycontextid($contextid);

            if (!$panelset) {
                $panelset = new \local_panels\panelset($contextid);
            }
        } else {
            $panelset = new \local_panels\panelset();
        }

        // I know, I know... The problem is the shape of the form is defined in what is in the post data
        // so we build a panel set from the raw post data to get the right shape, then build the form
        // then rebuild the panel set from the properly validated form data.
        if (!empty($_POST)) {
            $panelset->unpickle($_POST);
        }

        $newpanelid = false;
        if (optional_param('addpanel', false, PARAM_BOOL)) {
            $newpanelid = $panelset->addnewpanel();
        }

        $form = new panelsetform(null, ['panelset' => $panelset, 'newpanelid' => $newpanelid]);
        $data = $form->get_data();

        if ($data && empty($data->addpanel)) {
            $panelset->unpickle($data);
            $panelset->postprocessform($data);
            $panelset->save();
        } else {
            $form->set_data($panelset->getformdata());
            $form->set_data(['id' => $panelset->id, 'contextid' => $panelset->context->id, 'panelorder' => implode(',', array_keys($panelset->panels))]);
        }

        $renderedform = $form->render();

        $renderedpanels = [];
        foreach ($panelset->panels as $panel) {
            $renderedpanels[] = $panel->get_data();
        }

        $data = ['panels' => $renderedpanels, 'form' => $renderedform, 'newpanelid' => $newpanelid];

        return $OUTPUT->render_from_template("local_panels/editpanels", $data);
    }
}