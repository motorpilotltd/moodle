<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * @package    block_yammer
 * @copyright  2014 Catalyst EU
 * @author     Chris Wharton <chris.wharton@catalyst-eu.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The block_yammer class definition.
 */
class block_yammer extends block_base {

    /**
     * Class constructor.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_yammer');
    }

    /**
     * Which page types this block may appear on.
     *
     * @return array
     */
    public function applicable_formats() {
/* BEGIN CORE MOD */
        return array(
            'site-index' => true,
            'course-view' => true
            );
/* END CORE MOD */
    }

    /**
     * Return true if the block has a settings.php file.
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /*
     * Get the block content.
     *
     * @return stdObject
     */
    public function get_content() {

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        if (empty($this->config->feedtype)) {
            $this->content->text = get_string('notconfigured', 'block_yammer');
            return $this->content;
        }

        // Get config settings for script.
        $scriptsource = get_config('yammer', 'scriptsource');

        // Yammer network settings.
        $params = array(
            'container' => '#embedded-feed',
            'network' => $this->config->network,
            'config' => array(),
        );
        // The "my" feed doesn't use the feedType parameter.
        if ($this->config->feedtype !== 'my') {
            $params['feedType'] = $this->config->feedtype;
        }
        if (!empty($this->config->feedid)) {
            $params['feedId'] = $this->config->feedid;
        }
        if (!empty($this->config->defaultgroupid)) {
            $params['config']['defaultGroupId'] = $this->config->defaultgroupid;
        }
        $params['config']['defaultToCanonical'] = $this->get_setting('defaulttocanonical');
        $params['config']['use_sso'] = $this->get_setting('usesso');

        // Open graph settings.
        if ($this->config->feedtype === 'open-graph') {
            $params['objectProperties'] = array(
                'url' => $this->config->ogurl,
                'type' => $this->config->ogtype,
                'fetch' => $this->get_setting('fetch'),
                'private' => $this->get_setting('private'),
                'ignore_canonical_url' => $this->get_setting('ignore_canonical_url'),
            );
            $params['config']['showOpenGraphPreview'] = $this->get_setting('showogpreview');
        }

        // Feed display settings.
        if (!empty($this->config->prompttext)) {
            $params['config']['promptText'] = $this->config->prompttext;
        }
        $params['config']['header'] = $this->get_setting('showheader');
        $params['config']['hideNetworkName'] = $this->get_setting('hideNetworkName');
        $params['config']['footer'] = $this->get_setting('showfooter');

        // Encode the parameters for the yammer javascript to use.
        $params = json_encode($params, JSON_PRETTY_PRINT);

        $this->content->text = html_writer::tag('div', '', array('class' => 'block_yammer', 'id' => 'embedded-feed'));
        $this->content->text .= html_writer::tag('script', '',
            array('type' => 'text/javascript', 'src' => $scriptsource));
        $this->content->text .= html_writer::tag('script', "yam.connect.embedFeed({$params});");

        return $this->content;
    }

    /**
     * Hide the block header.
     *
     * @return bool
     */
    public function hide_header() {
        return false;
    }

    /**
     * Allow multiple instances of block.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Set the block title and config parameters before display.
     *
     * @return void
     */
    public function specialization() {
        if (!isset($this->config)) {
            $this->config = new stdClass();
        }
        // Set the block title.
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        } else {
            $this->title = get_string('pluginname', 'block_yammer');
        }
        // Set the default yammer network.
        if (empty($this->config->network)) {
            $this->config->network = get_config('yammer', 'defaultnetwork');
        }
    }

    /**
     * Shortcut function to retrieve checkbox type config settings.
     *
     * @param string $setting The setting to check.
     * @return bool
     */
    private function get_setting($setting) {
        return isset($this->config->$setting) ? (bool) $this->config->$setting : false;
    }
}

