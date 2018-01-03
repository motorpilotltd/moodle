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

defined('MOODLE_INTERNAL') || die();

/**
 * The form to edit block instance configuration.
 *
 * @package    block_yammer
 * @copyright  2014 Catalyst EU
 * @author     Chris Wharton <chris.wharton@catalyst-eu.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_yammer_edit_form extends block_edit_form {

    /**
     * Define form fields specific to this block.
     *
     * @param object $mform the form being built.
     * @return void
     */
    protected function specific_definition($mform) {

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
        // The block title.
        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_yammer'));
        $mform->setDefault('config_title', get_string('pluginname', 'block_yammer'));
        $mform->setType('config_title', PARAM_TEXT);

        // Yammer network settings.
        $mform->addElement('header', 'config_yammer', get_string('yammer_settings', 'block_yammer'));
        // The yammer network permalink.
        $mform->addElement('text', 'config_network', get_string('network', 'block_yammer'));
        $mform->addHelpButton('config_network', 'network', 'block_yammer');
        $mform->addRule('config_network', get_string('err_required', 'form'), 'required', '', 'client');
        $mform->setType('config_network', PARAM_TEXT);
        // The yammer feed type.
        $feedtypes = array(
            'my' => 'my',
            'group' => 'group',
            'user' => 'user',
            'topic' => 'topic',
            'open-graph' => 'open-graph'
        );
        $mform->addElement('select', 'config_feedtype', get_string('feedtype', 'block_yammer'), $feedtypes);
        $mform->addHelpButton('config_feedtype', 'feedtype', 'block_yammer');
        $mform->setType('config_feedtype', PARAM_TEXT);
        // The yammer feed id.
        $mform->addElement('text', 'config_feedid', get_string('feedid', 'block_yammer'));
        $mform->addHelpButton('config_feedid', 'feedid', 'block_yammer');
        $mform->addRule('config_feedid', get_string('err_numeric', 'form'), 'numeric', '', 'client');
        $mform->setType('config_feedid', PARAM_TEXT);
        // The yammer feed default group id.
        $mform->addElement('text', 'config_defaultgroupid', get_string('defaultgroupid', 'block_yammer'));
        $mform->addHelpButton('config_defaultgroupid', 'defaultgroupid', 'block_yammer');
        $mform->addRule('config_defaultgroupid', get_string('err_numeric', 'form'), 'numeric', '', 'client');
        $mform->disabledIf('config_defaultgroupid', 'config_feedtype', 'neq', 'group');
        $mform->setType('config_defaultgroupid', PARAM_TEXT);
        // Default to canonical network.
        $mform->addElement('advcheckbox', 'config_defaulttocanonical', get_string('defaulttocanonical', 'block_yammer'),
            get_string('defaulttocanonical_desc', 'block_yammer'));
        $mform->addHelpButton('config_defaulttocanonical', 'defaulttocanonical', 'block_yammer');
        $mform->setDefault('config_defaulttocanonical', 1);
        $mform->setType('config_defaulttocanonical', PARAM_BOOL);
        // Where to get the parameters from.
        $mform->addElement('static', 'description', '', get_string('config_help', 'block_yammer'));

        // Open graph settings.
        $mform->addElement('header', 'config_opengraph', get_string('opengraph_settings', 'block_yammer'));
        // The opengraph parameters.
        // opengraph url.
        $mform->addElement('text', 'config_ogurl', get_string('ogurl', 'block_yammer'));
        $mform->addHelpButton('config_ogurl', 'ogurl', 'block_yammer');
        $mform->disabledIf('config_ogurl', 'config_feedtype', 'neq', 'open-graph');
        $mform->setType('config_ogurl', PARAM_URL);
        // The opengraph object type.
        $ogtypes = array(
            'audio'  => new lang_string('og-audio', 'block_yammer'),
            'department'  => new lang_string('og-department', 'block_yammer'),
            'document'  => new lang_string('og-document', 'block_yammer'),
            'file'  => new lang_string('og-file', 'block_yammer'),
            'folder'  => new lang_string('og-file', 'block_yammer'),
            'image'  => new lang_string('og-image', 'block_yammer'),
            'page'  => new lang_string('og-page', 'block_yammer'),
            'person'  => new lang_string('og-person', 'block_yammer'),
            'place'  => new lang_string('og-place', 'block_yammer'),
            'project'  => new lang_string('og-project', 'block_yammer'),
            'team'  => new lang_string('og-team', 'block_yammer'),
            'video'  => new lang_string('og-video', 'block_yammer'),
        );
        $mform->addElement('select', 'config_ogtype', get_string('ogtype', 'block_yammer'), $ogtypes);
        $mform->addHelpButton('config_ogtype', 'ogtype', 'block_yammer');
        $mform->disabledIf('config_ogtype', 'config_feedtype', 'neq', 'open-graph');
        $mform->setType('config_feedtype', PARAM_TEXT);
        // Show or hide open graph preview.
        $mform->addElement('advcheckbox', 'config_showogpreview', get_string('showogpreview', 'block_yammer'),
            get_string('showogpreview_desc', 'block_yammer'));
        $mform->addHelpButton('config_showogpreview', 'showogpreview', 'block_yammer');
        $mform->disabledIf('config_showogpreview', 'config_feedtype', 'neq', 'open-graph');
        $mform->setType('config_showogpreview', PARAM_BOOL);
        // Fetch metadata.
        $mform->addElement('advcheckbox', 'config_fetch', get_string('fetch', 'block_yammer'),
            get_string('fetch_desc', 'block_yammer'));
        $mform->addHelpButton('config_fetch', 'fetch', 'block_yammer');
        $mform->disabledIf('config_fetch', 'config_feedtype', 'neq', 'open-graph');
        $mform->setType('config_fetch', PARAM_BOOL);
        // Mark as private.
        $mform->addElement('advcheckbox', 'config_private', get_string('private', 'block_yammer'),
            get_string('private_desc', 'block_yammer'));
        $mform->addHelpButton('config_private', 'private', 'block_yammer');
        $mform->disabledIf('config_private', 'config_feedtype', 'neq', 'open-graph');
        $mform->setType('config_private', PARAM_BOOL);
        // Ignore_canonical_url.
        $mform->addElement('advcheckbox', 'config_ignore_canonical_url', get_string('ignore_canonical_url', 'block_yammer'),
            get_string('ignore_canonical_url_desc', 'block_yammer'));
        $mform->addHelpButton('config_ignore_canonical_url', 'ignore_canonical_url', 'block_yammer');
        $mform->disabledIf('config_ignore_canonical_url', 'config_feedtype', 'neq', 'open-graph');
        $mform->setType('config_ignore_canonical_url', PARAM_BOOL);
        // Feed display settings.
        $mform->addElement('header', 'config_feed', get_string('feed_settings', 'block_yammer'));
        // Custom publisher message.
        $mform->addElement('text', 'config_prompttext', get_string('prompttext', 'block_yammer'));
        $mform->addHelpButton('config_prompttext', 'prompttext', 'block_yammer');
        $mform->setType('config_prompttext', PARAM_TEXT);
        // Show or show header.
        $mform->addElement('advcheckbox', 'config_showheader', get_string('showheader', 'block_yammer'),
            get_string('showheader_desc', 'block_yammer'));
        $mform->setDefault('config_showheader', 1);
        $mform->setType('config_showheader', PARAM_BOOL);
        // Hide network name.
        $mform->addElement('advcheckbox', 'config_hideNetworkName', get_string('hideNetworkName', 'block_yammer'),
            get_string('hideNetworkName_desc', 'block_yammer'));
        $mform->setDefault('config_hideNetworkName', 0);
        $mform->setType('config_hideNetworkName', PARAM_BOOL);
        // Show or hide footer.
        $mform->addElement('advcheckbox', 'config_showfooter', get_string('showfooter', 'block_yammer'),
            get_string('showfooter_desc', 'block_yammer'));
        $mform->setDefault('config_showfooter', 1);
        $mform->setType('config_showfooter', PARAM_BOOL);
        // Clean form inputs.
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Validate the submitted form data.
     *
     * @param array $data array of submitted data.
     * @param array $files array of uploaded files.
     * @return array
     */
    public function validation($data, $files) {
        $errors = array();

        // If open-graph is the selected feed type, a url is required.
        if (($data['config_feedtype'] === 'open-graph') && (empty($data['config_ogurl']))) {
            $errors['config_ogurl'] = get_string('err_required', 'form');
        }

        return $errors;
    }
}
