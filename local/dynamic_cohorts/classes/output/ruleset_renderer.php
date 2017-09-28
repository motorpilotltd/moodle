<?php
namespace local_dynamic_cohorts\output;

use local_dynamic_cohorts\dynamic_cohorts;

/**
 * @author Tomasz Biegun <tomasz.biegun@webanywhere.co.uk>
 */
class ruleset_renderer extends \plugin_renderer_base
{
    /**
     * Displays ruleset (with rules if this is edit action)
     * 
     * @param $rulesetid
     * @param $rulesetcount
     * @param int $operator
     * @param array $rules
     * @return string
     */
    public function display_ruleset($rulesetid, $rulesetcount, $operator = dynamic_cohorts::OPERATOR_AND, $rules = [])
    {
        global $OUTPUT;

        $output = \html_writer::start_div('ruleset');
        $output .= \html_writer::tag('input', '', ['value' => $rulesetid, 'type' => 'hidden', 'name' => 'rulesetid[]']);
        $output .= \html_writer::tag('input', '', ['value' => count($rules), 'type' => 'hidden', 'name' => 'ruleid_' . $rulesetid]);
        $counter = \html_writer::span($rulesetcount, 'counter');
        $output .= \html_writer::tag('h3', get_string('rulesetheader', 'local_dynamic_cohorts') . ' ' . $counter);

        $output .= \html_writer::start_tag('a', ['class' => 'deleteruleset', 'href' => '#']);
        $output .= \html_writer::tag('img', '', ['src' => $OUTPUT->pix_url("t/delete"), 'title' => get_string('delete', 'local_dynamic_cohorts')]);
        $output .= \html_writer::end_tag('a');

        $output .= \html_writer::start_div('fitem fitem_fgroup');
        $output .= \html_writer::start_div('fitemtitle');
        $output .= \html_writer::start_div('fgrouplabel');
        $output .= \html_writer::tag('label', get_string('ruleoperatorgrouplabel', 'local_dynamic_cohorts'));
        $output .= \html_writer::end_div();
        $output .= \html_writer::end_div();
        $output .= \html_writer::start_tag('fieldset', ['class' => 'felement fgroup']);

        $input = ['id' => 'ruleoperator_' . $rulesetid, 'value' => dynamic_cohorts::OPERATOR_AND, 'type' => 'radio', 'name' => 'ruleoperator[' . $rulesetid . ']'];
        if($operator == dynamic_cohorts::OPERATOR_AND){
            $input['checked'] = 'checked';
        }
        $output .= \html_writer::tag('input', '', $input);
        $output .= \html_writer::tag('label', get_string('ruleoperatorandlabel', 'local_dynamic_cohorts'), ['for' => 'ruleoperator_' . $rulesetid]);

        $output .= \html_writer::empty_tag('br');

        $input = ['id' => 'ruleoperator_' . $rulesetid, 'value' => dynamic_cohorts::OPERATOR_OR, 'type' => 'radio', 'name' => 'ruleoperator[' . $rulesetid . ']'];
        if($operator == dynamic_cohorts::OPERATOR_OR){
            $input['checked'] = 'checked';
        }
        $output .= \html_writer::tag('input', '', $input);
        $output .= \html_writer::tag('label', get_string('ruleoperatororlabel', 'local_dynamic_cohorts'), ['for' => 'ruleoperator_' . $rulesetid]);

        $output .= \html_writer::end_tag('fieldset');
        $output .= \html_writer::end_div();

        $output .= $this->display_rule_fieldset($rulesetid, $rules);
        $output .= $this->display_rule_form($rulesetid);

        $output .= \html_writer::end_div();
        return $output;
    }

    /**
     * Display existing rules
     * @param $rulesetid
     * @param array $rules
     * @return string
     */
    public function display_rule_fieldset($rulesetid, $rules = []){
        $output = '';
        $output .= \html_writer::start_div('fitem fitem_fgroup');
        $output .= \html_writer::start_div('fitemtitle');
        $output .= \html_writer::start_div('fgrouplabel');
        $output .= \html_writer::tag('label', get_string('currentrules', 'local_dynamic_cohorts'));
        $output .= \html_writer::end_div();
        $output .= \html_writer::end_div();
        $output .= \html_writer::start_tag('fieldset', ['class' => 'felement fgroup rules_' . $rulesetid]);
        foreach($rules as $counter => $rule){
            switch ($rule->fieldtype) {
                case dynamic_cohorts::FIELD_TYPE_CUSTOM:
                    $prefix = 'custom_';
                    break;
                case dynamic_cohorts::FIELD_TYPE_HUB:
                    $prefix = 'hub_';
                    break;
                default:
                    $prefix = 'user_';
                    break;
            }
            $output .= $this->display_rule($rulesetid, ++$counter, $prefix.$rule->field, $rule->criteriatype, $rule->value);
        }
        $output .= \html_writer::end_tag('fieldset');
        $output .= \html_writer::end_div();

        return $output;
    }

    /**
     * Display inputs and dropdown for creating/editing rule
     * 
     * @param $rulesetid
     * @param string $action
     * @param int $ruleid
     * @param string $field
     * @param int $criteriatype
     * @param string $value
     * @return string
     */
    public function display_rule_form($rulesetid, $action = 'addrule', $ruleid = 0, $field = '', $criteriatype = 0, $value = '')
    {
        $output = '';
        if ($action == 'addrule') {

            $output .= \html_writer::start_div('fitem fitem_fgroup');
            $output .= \html_writer::start_div('fitemtitle');
            $output .= \html_writer::start_div('fgrouplabel');
            $output .= \html_writer::tag('label', get_string('addruletoset', 'local_dynamic_cohorts'));
            $output .= \html_writer::end_div();
            $output .= \html_writer::end_div();

            $output .= \html_writer::start_tag('fieldset');
            $output .= \html_writer::select(dynamic_cohorts::get_rule_fields(), 'field_' . $rulesetid, '', [], ['data-rulesetid' => $rulesetid, 'data-edit' => 0, 'class' => 'field_select']);
            $output .= \html_writer::select(dynamic_cohorts::get_criteria_types_by_field_type($field), 'criteriatype_' . $rulesetid, '', []);

            $output .= \html_writer::start_span('value_field', ['id' => 'value_field_'.$rulesetid]);
            $output .= $this->value_field($rulesetid, $field, '', false);
            $output .= \html_writer::end_span();

            $output .= \html_writer::tag('input', '', ['type' => 'button', 'name' => 'addrule', 'class' => 'addrule', 'data-rulesetid' => $rulesetid, 'value' => get_string('addrule', 'local_dynamic_cohorts')]);
            $output .= \html_writer::end_tag('fieldset');
            $output .= \html_writer::end_div();
        } else {
            $output .= \html_writer::start_tag('fieldset', ['class' => 'edit_' . $action]);
            $output .= \html_writer::select(dynamic_cohorts::get_rule_fields(), 'field_' . $rulesetid, $field, [], ['data-rulesetid' => $rulesetid, 'data-edit' => 1, 'class' => 'field_select']);
            $output .= \html_writer::select(dynamic_cohorts::get_criteria_types_by_field_type($field), 'criteriatype_' . $rulesetid, $criteriatype, []);

            $output .= \html_writer::start_span('value_field', ['id' => 'value_field_'.$rulesetid]);
            $output .= $this->value_field($rulesetid, $field, $value, true);
            $output .= \html_writer::end_span();

            $output .= \html_writer::tag('input', '', ['type' => 'button', 'name' => 'saverule', 'class' => 'saverule', 'data-rulesetid' => $rulesetid, 'data-ruleid' => $ruleid, 'value' => get_string('saverule', 'local_dynamic_cohorts')]);
            $output .= \html_writer::end_tag('fieldset');
        }

        return $output;
    }

    /**
     * Generate HTML for value field basing on field type (text / checkbox / datetime)
     * 
     * @param $rulesetid
     * @param $field field name
     * @param $value default value
     * @param bool $edit true if edit rule, false if add
     * @return string
     */
    public function value_field($rulesetid, $field, $value, $edit = false){
        $output = '';
        switch (dynamic_cohorts::get_field_type($field)){
            case 'date':
                $output .= $this->date_select($rulesetid, $value, false);
                break;
            case 'datetime':
                $output .= $this->date_select($rulesetid, $value, true);
                break;
            default:
                $output .= \html_writer::tag('input', '', ['type' => 'text', 'class' => ($edit ? 'edit' : 'add'), 'name' => 'value_' . $rulesetid, 'value' => $value]);
                break;
        }
        return $output;
    }

    /**
     * Create dropdown for selecting date
     * 
     * @param $rulesetid
     * @param $currenttime
     * @param bool $withtime
     * @return string
     */
    public function date_select($rulesetid, $currenttime, $withtime = false){
        if(!is_numeric($currenttime)) {
            try{
                $date = new \DateTime($currenttime);
                $currenttime = $date->getTimestamp();
            }catch(\Exception $e){
                $currenttime = 0;
            }
        }
        $output = '';
        $output .= \html_writer::select_time('years', 'value_year_'.$rulesetid, $currenttime);
        $output .= \html_writer::select_time('months', 'value_month_'.$rulesetid, $currenttime);
        $output .= \html_writer::select_time('days', 'value_day_'.$rulesetid, $currenttime);
        if($withtime){
            $output .= ' ';
            $output .= \html_writer::select_time('hours', 'value_hour_'.$rulesetid, $currenttime);
            $output .= ':';
            $output .= \html_writer::select_time('minutes', 'value_minute_'.$rulesetid, $currenttime);
        }
        return $output;
    }

    /**
     * Display rule converted to string i.e. "Firstname contains 'John' "
     * 
     * @param $rulesetid
     * @param $ruleid
     * @param $field
     * @param $criteriatype
     * @param $value
     * @return string
     */
    public function display_rule($rulesetid, $ruleid, $field, $criteriatype, $value)
    {
        global $OUTPUT;
        $showvalue = true;
        if(in_array($criteriatype, [dynamic_cohorts::CRITERIA_TYPE_IS_NOT_CHECKED, dynamic_cohorts::CRITERIA_TYPE_IS_CHECKED, dynamic_cohorts::CRITERIA_TYPE_IS_EMPTY])){
            $showvalue = false;
        }
        if(dynamic_cohorts::get_field_type($field) == 'date' && is_numeric($value)){
            $value = date('Y-m-d', $value);
        }
        if(dynamic_cohorts::get_field_type($field) == 'datetime' && is_numeric($value)){
            $value = date('Y-m-d H:i', $value);
        }
        $output = '';
        $output .= \html_writer::start_span();
        $output .= \html_writer::tag('input', '', ['type' => 'hidden', 'name' => 'field[' . $rulesetid . '][' . $ruleid . ']', 'value' => $field]);
        $output .= \html_writer::tag('input', '', ['type' => 'hidden', 'name' => 'criteriatype[' . $rulesetid . '][' . $ruleid . ']', 'value' => $criteriatype]);
        $output .= \html_writer::tag('input', '', ['type' => 'hidden', 'name' => 'value[' . $rulesetid . '][' . $ruleid . ']', 'value' => ($showvalue ? $value : '') ]);
        $output .= dynamic_cohorts::get_rule_fields()[$field] . ' ' . dynamic_cohorts::get_criteria_types('all')[$criteriatype] . ($showvalue ? " '" . $value . "' " : " ");

        $output .= \html_writer::start_tag('a', ['class' => 'deleterule', 'href' => '#']);
        $output .= \html_writer::tag('img', '', ['src' => $OUTPUT->pix_url("t/delete"), 'title' => get_string('delete', 'local_dynamic_cohorts')]);
        $output .= \html_writer::end_tag('a');

        $output .= \html_writer::start_tag('a', ['class' => 'editrule', 'href' => '#', 'data-rulesetid' => $rulesetid, 'data-ruleid' => $ruleid]);
        $output .= \html_writer::tag('img', '', ['src' => $OUTPUT->pix_url('i/edit'), 'title' => get_string('edit', 'local_dynamic_cohorts')]);
        $output .= \html_writer::end_tag('a');

        $output .= \html_writer::empty_tag('br');
        $output .= \html_writer::end_span();

        return $output;
    }
}