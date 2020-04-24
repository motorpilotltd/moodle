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

    public function search_results($resultdata, $search)
    {
        $resultdata['preloadedcourses'] = $this->courses($resultdata, $search);

        return $this->render_from_template('local_search/searchresults', $resultdata);
    }

    public function courses($resultdata, $search)
    {
        global $OUTPUT;
        $resultdata['lilimgurl'] = $OUTPUT->image_url('lil-logo', 'local_search');
        $lilsearchurl = new moodle_url('https://www.linkedin.com/learning/search', ['keywords' => $search]);
        $resultdata['lilsearchurl'] = $lilsearchurl->out(false);
        $resultdata['lilonclick'] = "_gaq.push(['masterTracker._trackEvent', 'navigation', 'quick_link', '{$resultdata['lilsearchurl']} | Launch LinkedIn Learning']);";
        return $this->render_from_template('local_search/courses', $resultdata);
    }
}