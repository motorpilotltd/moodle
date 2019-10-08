<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Coggins <simon.coggins@totaralms.com>
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @package mod_assign
 */

namespace rbsource_reports;
use rb_base_source;
use coding_exception;
use rb_column_option;
use rb_param_option;
use rb_content_option;
use reportbuilder;

defined('MOODLE_INTERNAL') || die();

/**
 * A report builder source for the "reportbuilder" table.
 */
class source extends rb_base_source {

    use \local_reportbuilder\rb\source\report_trait;

    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $requiredcolumns, $sourcetitle;

    private $reporturl;

    /**
     * Constructor
     */
    public function __construct() {

        $this->base = '{report_builder}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();

        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = [];
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_reports');
        list($this->sourcewhere, $this->sourceparams) = $this->define_sourcewhere();

        // Pull in all report related info via a trait so we
        // can reuse it in other report sources.
        $this->add_report_to_base();

        parent::__construct();
    }

    /**
     * Are the global report restrictions implemented in the source?
     * @return null|bool
     */
    public function global_restrictions_supported() {
        return false;
    }

    //
    //
    // Methods for defining contents of source
    //
    //

    /**
     * Creates the array of rb_join objects required for this->joinlist
     *
     * @return array
     */
    protected function define_joinlist() {

        $joinlist = [];

        return $joinlist;
    }

    /**
     * Creates the array of rb_column_option objects required for
     * $this->columnoptions
     *
     * @return array
     */
    protected function define_columnoptions() {
        $columnoptions = [];

        return $columnoptions;
    }

    /**
     * Creates the array of rb_filter_option objects required for $this->filteroptions
     * @return array
     */
    protected function define_filteroptions() {

        $filteroptions = [];

        return $filteroptions;
    }


    protected function define_defaultcolumns() {
        $defaultcolumns = [
                [
                        'type' => 'report',
                        'value' => 'namelinkview',
                ],
                [
                        'type' => 'report',
                        'value' => 'source',
                ],
                [
                        'type' => 'report',
                        'value' => 'embedded',
                ],
                [
                        'type' => 'report',
                        'value' => 'actions',
                ],
        ];
        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = [
                [
                        'type' => 'report',
                        'value' => 'name',
                ],
                [
                        'type' => 'report',
                        'value' => 'source',
                ],
                [
                        'type' => 'report',
                        'value' => 'embedded',
                ],
        ];

        return $defaultfilters;
    }
    /**
     * Creates the array of rb_content_option object required for $this->contentoptions
     * @return array
     */
    protected function define_contentoptions() {
        $contentoptions = [
                new rb_content_option(
                        'report_access',
                        get_string('access', 'local_reportbuilder'),
                        'base.id'
                )
        ];

        return $contentoptions;
    }

    protected function define_paramoptions() {
        $paramoptions = [
                new rb_param_option(
                        'embedded',
                        'base.embedded'
                ),
        ];

        return $paramoptions;
    }

    /**
     * Exclude embedded reports based on enabled features.
     *
     * Remove reports from the embedded report page when those features
     * are not enabled across the site.
     */
    protected function define_sourcewhere() {
        global $DB;
        $unwantedsources = reportbuilder::get_ignored_sources();
        $unwantedembedded = reportbuilder::get_ignored_embedded();

        $sql = '';
        $params = [];

        if (!empty($unwantedsources)) {
            list($notinsql, $notinparams) = $DB->get_in_or_equal($unwantedsources, SQL_PARAMS_NAMED, 'sourcewhere', false);
            // Exclude embedded reports from ignored sources.
            $sql .= "base.source " . $notinsql;
            $params = array_merge($params, $notinparams);
        }

        if (!empty($unwantedembedded)) {
            list($notinsql, $notinparams) = $DB->get_in_or_equal($unwantedembedded, SQL_PARAMS_NAMED, 'sourcewhere', false);
            // Exclude embedded reports that are set as ignored.
            $sql .= empty($sql) ? '' : " AND ";
            $sql .= "base.shortname " . $notinsql;
            $params = array_merge($params, $notinparams);
        }

        return array($sql, $params);
    }
}

// end of rb_source_reports class
