<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

/**
 *
 */
class local_kalturaview_renderer extends plugin_renderer_base
{
    public function view_video($video)
    {
        $templatedata = new stdClass;
        $templatedata->player = $this->video_player();
    }

    public function video_player(\KalturaMediaEntry $video, $session)
    {
        global $PAGE;

        $url = $PAGE->url;

        // Determine aspect ratio for styling.
        if (!is_null($video->height) && !is_null($video->width)) {
            $aspectratio = number_format(100 * ($video->height / $video->width), 2);
        } else {
            // Force 16:9.
            $aspectratio = number_format(100 * (9 / 16), 2);
        }

        $templatedata = [
            'entry_id' => $video->rootEntryId,
            'url' => $video->dataUrl,
            'type' => $video->type,
            'session' => $session,
            'returnurl' => optional_param('return', '', PARAM_URL),
            'aspect_ratio' => $aspectratio,
            'scheme' => $url->get_scheme()
        ];

        $settings = (array)get_config('local_kaltura');

        $localsettings = (array)get_config('local_kalturaview');

        return $this->render_from_template('local_kalturaview/video', array_merge($settings, $localsettings, $templatedata));
    }

    public function search_results(\templatable $results)
    {
        global $OUTPUT;

        return $this->render_from_template('local_kalturaview/search', $results->export_for_template($OUTPUT));
    }
}