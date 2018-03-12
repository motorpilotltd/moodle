<?php
namespace local_dynamic_cohorts\output;

use local_dynamic_cohorts\dynamic_cohorts;


class role_renderer extends \plugin_renderer_base
{

    /**
     * Display role form
     *
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function display_role_form(){
        $output = '';
        $output .= \html_writer::start_div('fitem fitem_fgroup');
        $output .= \html_writer::start_div('fitemtitle');
        $output .= \html_writer::start_div('fgrouplabel');
        $output .= \html_writer::tag('label', get_string('addrole', 'local_dynamic_cohorts'));
        $output .= \html_writer::end_div();
        $output .= \html_writer::end_div();
        
        $output .= \html_writer::select(dynamic_cohorts::get_context_list('local/dynamic_cohorts:edit'), 'context', 0, [], [ 'class' => 'context_select']);
        $output .= \html_writer::select(dynamic_cohorts::get_roles(1), 'role', 0, []);

        $output .= \html_writer::tag('input', '', ['type' => 'button', 'name' => 'addrole', 'class' => 'addrole', 'value' => get_string('addrule', 'local_dynamic_cohorts')]);
        $output .= \html_writer::end_div();

        return $output;
    }

    /**
     * Display roles
     *
     * @param $roles
     * @param bool $viewonly
     * @return string
     * @throws \coding_exception
     */
    public function display_roles($roles, $viewonly = false){
        
        $output = '';
        $output .= \html_writer::start_div('fitem fitem_fgroup');
        $output .= \html_writer::start_div('fitemtitle');
        $output .= \html_writer::start_div('fgrouplabel');
        $output .= \html_writer::tag('label', get_string('selectedroles', 'local_dynamic_cohorts'));
        $output .= \html_writer::end_div();
        $output .= \html_writer::end_div();
        $output .= \html_writer::start_tag('fieldset', ['class' => 'rolelist']);

        foreach($roles as $role){
            $output .= $this->display_role($role->roleid, $role->contextid, $viewonly);
        }

        $output .= \html_writer::end_tag('fieldset');
        $output .= \html_writer::end_div();

        return $output;
    }

    /**
     * Display single role record
     *
     * @param $roleid
     * @param $contextid
     * @param bool $viewonly
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function display_role($roleid, $contextid, $viewonly = false){
        global $OUTPUT;

        $output = '';
        $output .= \html_writer::start_span();
        $output .= \html_writer::tag('input', '', ['type' => 'hidden', 'name' => 'roles[]', 'value' => $roleid]);
        $output .= \html_writer::tag('input', '', ['type' => 'hidden', 'name' => 'contexts[]', 'value' => $contextid]);
        $output .= dynamic_cohorts::get_role_name($roleid).' '.get_string('in', 'local_dynamic_cohorts').' '.dynamic_cohorts::get_context_name($contextid).' ';
        if(!$viewonly){
            $output .= \html_writer::start_tag('a', ['class' => 'deleterole', 'href' => '#']);
            $output .= $OUTPUT->pix_icon("t/delete", get_string('delete', 'local_dynamic_cohorts'));
            $output .= \html_writer::end_tag('a');
        }
        $output .= \html_writer::empty_tag('br');
        $output .= \html_writer::end_span();
        
        return $output;
    }
    
}