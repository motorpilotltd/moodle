<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

/**
 *
 */
class local_search_renderer extends plugin_renderer_base
{
    public function search_form($formdata)
    {
        return $this->render_from_template('local_search/searchform', $formdata);
    }

    public function search_results($resultdata)
    {
        $resultdata['preloadedcourses'] = $this->courses($resultdata);

        return $this->render_from_template('local_search/searchresults', $resultdata);
    }

    public function courses($resultdata)
    {
        return $this->render_from_template('local_search/courses', $resultdata);
    }
}