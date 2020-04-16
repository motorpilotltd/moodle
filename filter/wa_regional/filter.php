<?php

class filter_wa_regional extends moodle_text_filter
{
    public function filter($text, array $options = array())
    {
        if (stripos($text, 'region') === false) {
            return $text;
        }

        $pattern = '/{region\s+([a-z0-9_ -]+\s*)}(.*?){\/region}/is';
        $result = preg_replace_callback($pattern, array($this, 'filter_tags'), $text);

        if (is_null($result)) {
            return $text;
        } else {
            return $result;
        }
    }

    protected function get_user_region()
    {
        global $DB, $USER;
        $sql = '
            SELECT
                lrr.name
            FROM {local_regions_reg} lrr
            INNER JOIN {local_regions_use} lru
                ON lrr.id = lru.regionid
            WHERE
                lru.userid = :userid';

        $region = $DB->get_field_sql($sql, ['userid' => $USER->id]);

        return $region;
    }

    protected function filter_tags($block)
    {
        $userRegion = $this->get_user_region();

        $region = trim($block[1]);
        $text = trim($block[2]);
        
        if ($userRegion == $region || $region == 'Global')
            return $text;
    }
}