<?php

namespace block_my_cohort_cert;
use local_custom_certification\certification;
use local_dynamic_cohorts\dynamic_cohorts;

/**
 * @package block_my_cohort_cert
 */

class my_cohort_cert {

    /**
     * Build tree basing on provided cohorts contexts
     *
     * @param $contexts
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_cohort_tree($contexts){
        global $DB;
        list($insql, $params) = $DB->get_in_or_equal($contexts, SQL_PARAMS_NAMED, 'param', true, null);
        $params['contextcoursecat'] = CONTEXT_COURSECAT;

        $sql = "
            SELECT 
                cc.id, 
                cc.sortorder, 
                cc.name, 
                cc.visible, 
                cc.parent, 
                cc.path, 
                ctx.id as contextid
            FROM {course_categories} cc
            JOIN {context} ctx ON cc.id = ctx.instanceid AND ctx.contextlevel = :contextcoursecat
            WHERE ctx.id ".$insql."
            ORDER BY cc.sortorder 
            ";
        $categories = $DB->get_records_sql($sql, $params);
        $parent = 0;
        /**
         * Add System context
         */
        if(in_array(1, $contexts)){
            $parent = -1;
            $system = new \stdClass();
            $system->id = 0;
            $system->parent = -1;
            $system->name = get_string('system_context', 'local_dynamic_cohorts');
            $system->contextid = 1;
            $categories[] = $system;
        }
        $categories = self::prepare_cohort_urls($categories);

        return self::build_tree($categories, $parent);
    }

    /**
     * Build tree basing on provided certifiation categories
     *
     * @param $contexts
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_certification_tree($categories){
        global $DB;
        list($insql, $params) = $DB->get_in_or_equal($categories, SQL_PARAMS_NAMED, 'param', true, null);

        $sql = "
            SELECT 
                cc.id, 
                cc.sortorder, 
                cc.name, 
                cc.visible, 
                cc.parent, 
                cc.path
            FROM {course_categories} cc
            WHERE cc.id ".$insql."
            ORDER BY cc.sortorder 
            ";
        $categories = $DB->get_records_sql($sql, $params);
        $categories = self::prepare_certification_urls($categories);

        return self::build_tree($categories, 0);
    }

    /**
     * Get cohorts contexts tree that I can edit
     *
     * @return array
     */
    public static function get_editable_cohort_tree(){
        $editablecohorts = dynamic_cohorts::get_editable_context_list();
        return self::get_cohort_tree($editablecohorts);
    }

    /**
     * Get cohorts contexts tree that I can only view
     *
     * @return array
     */
    public static function get_viewable_cohort_tree(){
        $viewablecohorts = array_diff(dynamic_cohorts::get_viewable_context_list(), dynamic_cohorts::get_editable_context_list());
        return self::get_cohort_tree($viewablecohorts);
    }

    /**
     * Get certifcications tree that I can edit
     *
     * @return array
     */
    public static function get_editable_certification_tree(){
        $editablecertifications = certification::get_editable_categories();
        return self::get_certification_tree($editablecertifications);
    }

    /**
     * Get certifcications tree that I can view
     *
     * @return array
     */
    public static function get_viewable_certification_tree(){
        $viewablecertifications = array_diff(certification::get_viewable_categories(), certification::get_editable_categories());
        return self::get_certification_tree($viewablecertifications);
    }

    /**
     * Get block data
     *
     * @return array
     * @throws \coding_exception
     */
    public static function get_data(){
        $data = ['content' => 'menu', 'children' => []];

        $editablecohorts = self::get_editable_cohort_tree();
        if(count($editablecohorts)>0){
            $data['children'][] = [
                'content' => get_string('cohortsmanage', 'block_my_cohort_cert'),
                'children' => $editablecohorts
            ];
        }

        $viewablecohorts = self::get_viewable_cohort_tree();
        if(count($viewablecohorts)>0){
            $data['children'][] = [
                'content' => get_string('cohortsview', 'block_my_cohort_cert'),
                'children' => $viewablecohorts
            ];
        }

        $editablecertifications = self::get_editable_certification_tree();
        if(count($editablecertifications)>0){
            $data['children'][] = [
                'content' => get_string('certificationsmanage', 'block_my_cohort_cert'),
                'children' => $editablecertifications
            ];
        }

        $viewablecertifications = self::get_viewable_certification_tree();
        if(count($viewablecertifications)>0){
            $data['children'][] = [
                'content' => get_string('certificationsview', 'block_my_cohort_cert'),
                'children' => $viewablecertifications
            ];
        }

        return $data;
    }

    /**
     * Prepare URL to cohort
     *
     * @param $data
     * @return mixed
     * @throws \moodle_exception
     */
    public static function prepare_cohort_urls($data){
        global $CFG;
        foreach($data as &$record){
            $url = new \moodle_url($CFG->wwwroot.'/cohort/index.php', ['contextid' => $record->contextid]);
            $record->content = \html_writer::link($url->out(), $record->name);
        }
        return $data;
    }

    /**
     * Prepare URL to certification
     *
     * @param $data
     * @return mixed
     * @throws \moodle_exception
     */
    public static function prepare_certification_urls($data){
        global $CFG;
        foreach($data as &$record){
            $url = new \moodle_url($CFG->wwwroot.'/local/custom_certification/index.php', ['categoryid' => $record->id]);
            $record->content = \html_writer::link($url->out(), $record->name);
        }
        return $data;
    }

    /**
     * Build tree basing on flat array
     *
     * @param array $elements
     * @param int $parentid
     * @return array
     */
    public static function build_tree(array &$elements, $parentid = -1) {
        $branch = array();
        foreach ($elements as &$element) {
            $element = (array)$element;
            if ($element['parent'] == $parentid) {
                $children = self::build_tree($elements, $element['id']);
                $element['children'] = $children;
                $branch[$element['id']] = $element;
                unset($element);
            }
        }
        return $branch;
    }
}
