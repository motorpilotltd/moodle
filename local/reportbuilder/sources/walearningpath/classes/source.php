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
 * @copyright   2020 Xantico Ltd
 * @author      Aleks Daloso <aleks@xanti.co>
 * @package local_reportbuilder
 */

namespace rbsource_walearningpath;

use rb_base_source;
use rb_join;
use rb_column_option;
use rb_filter_option;
use rb_content_option;
use rb_param_option;
use rb_column;
use moodle_url;
use html_writer;

defined('MOODLE_INTERNAL') || die();

class source extends rb_base_source {
    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $requiredcolumns, $sourcetitle;

    public function __construct() {
        $this->base = '{wa_learning_path}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->paramoptions = $this->define_paramoptions();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_walearningpath');

        parent::__construct();
    }

    protected function define_columnoptions() {
        $columnoptions = parent::define_columnoptions();

        // Include some standard columns, override parent so they say certification.
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_staff_details_to_columns($columnoptions);

        $columnoptions[] = new rb_column_option(
            'learningpath',
            'title',
            get_string('title', 'rbsource_walearningpath'),
            'base.title',
            array(
                'displayfunc'  => 'plaintext',
                'dbdatatype'   => 'char',
                'outputformat' => 'text'
            )
        );

        $columnoptions[] = new rb_column_option(
            'learningpath',
            'titlelinkedlearningpath',
            get_string('titlelinkedlearningpath', 'rbsource_walearningpath'),
            'base.title',
            array(
                'displayfunc'  => 'titlelinkedlearningpath',
                'extrafields' => ['id' => 'base.id']
            )
        );

        $columnoptions[] = new rb_column_option(
            'learningpath',
            'summary',
            get_string('summary', 'rbsource_walearningpath'),
            'base.summary',
            array(
                'displayfunc'  => 'plaintext',
                'dbdatatype'   => 'char',
                'outputformat' => 'text'
            )
        );

        $columnoptions[] = new rb_column_option(
            'learningpath',
            'learningpathimage',
            get_string('learningpathimage', 'rbsource_walearningpath'),
            'base.id',
            array(
                'displayfunc' => 'learningpathimage',
            )
    );

        $columnoptions[] = new rb_column_option(
            'learningpathsubscribe',
            'userid',
            get_string('subscriber_userid', 'rbsource_walearningpath'),
            'walearningpathsubscribe.userid',
            array('joins'        => 'walearningpathsubscribe',
                'displayfunc'  => 'plaintext',
                'dbdatatype'   => 'char',
                'outputformat' => 'text')
        );

        $columnoptions[] = new rb_column_option(
            'learningpathsubscribe',
            'subscribed',
            get_string('subscription', 'rbsource_walearningpath'),
            'CASE WHEN walearningpathsubscribe.id IS NULL THEN 0 ELSE 1 END',
            array(
                'joins'        => 'walearningpathsubscribe',
                'displayfunc'  => 'subscribed',
                'dbdatatype'   => 'char',
                'outputformat' => 'text',
            )
        );

        $columnoptions[] = new rb_column_option(
            'learningpathsubscribe',
            'timecreated_subscribe',
            get_string('subscriptiondate', 'rbsource_walearningpath'),
            "walearningpathsubscribe.timecreated",
            array(
                'displayfunc' => 'nice_date',
                'dbdatatype' => 'timestamp',
            )
        );

        return $columnoptions;
    }

    public function rb_display_subscribed($data, $row) {
        if ($data == '1') {
            return 'Subscribed';
        }
        return 'Not subscribed';
    }

    protected function define_joinlist() {
        $joinlist = [];
        
        $joinlist[] = new rb_join(
            'walearningpathsubscribe',
            'LEFT',
            '{wa_learning_path_subscribe}',
            "base.id = walearningpathsubscribe.learningpathid",
            REPORT_BUILDER_RELATION_ONE_TO_MANY
        );

        $this->add_user_table_to_joinlist($joinlist, 'walearningpathsubscribe', 'userid');

         return $joinlist;
    }

    protected function define_filteroptions() {
        $filteroptions = array();

        $filteroptions[] = new rb_filter_option(
            'learningpath',
            'title',
            get_string('title', 'rbsource_walearningpath'),
            'text'
        );

        $filteroptions[] = new rb_filter_option(
            'user',
            'username',
            get_string('username', 'rbsource_walearningpath'),
            'text'
        );

        return $filteroptions;
    }

    protected function define_contentoptions() {
        $contentoptions = [];

        $contentoptions[] = new rb_content_option(
            'user',
            get_string('user', 'local_reportbuilder'),
            ['userid' => 'walearningpathsubscribe.userid']
        );

        $contentoptions[] = new rb_content_option(
            'leaver',
            get_string('leaver', 'local_reportbuilder'),
            ['leaver' => "auserstaff.LEAVER_FLAG"],
            'auserstaff'
        );
        return $contentoptions;
    }

    /**
     * define required columns
     * @return array
     */
    protected function define_requiredcolumns() {
        $requiredcolumns = array(
            // Subscriber status
                new rb_column(
                        'learningpathsubscribe',
                        'status',
                        '',
                        'walearningpathsubscribe.status',
                        array('hidden' => true, 'joins' => 'walearningpathsubscribe')
                ),
        );

        $requiredcolumns[] = new rb_column(
            'auser',
            'employeenumber',
            '',
            "auserstaff.EMPLOYEE_NUMBER",
            array('hidden' => true, 'joins' => 'auserstaff')
        );

        return $requiredcolumns;
    }

    protected function define_paramoptions() {
        $paramoptions = [
                new rb_param_option(
                    'walearningpath_userid',
                    'walearningpathsubscribe.userid'
                ),
                new rb_param_option(
                    'walearningpath_id',
                    'base.id'
                ), 
        ];

        return $paramoptions;
    }

    public function rb_display_learningpathimage($id, $row) {
        global $CFG;
        require_once($CFG->dirroot . '/local/wa_learning_path/lib/lib.php');
        \wa_learning_path\lib\load_model('learningpath');

        $image_url = \wa_learning_path\model\learningpath::get_image_url($id, false);
        $image_url = !empty($image_url) ? $image_url: new \moodle_url('/local/wa_learning_path/pix/default.svg');

        return \html_writer::empty_tag('img', array('src' => $image_url, 'class' => 'learning_path_image', 'width' => '35px', 'height' => '35px'));
    }

    public function rb_display_titlelinkedlearningpath($title, $row) {
        // /local/wa_learning_path/index.php?c=learning_path&a=matrix&id=[learning path id]
        $url = new moodle_url(
            '/local/wa_learning_path/index.php', 
            array(
                'c'  => 'learning_path',
                'a'  => 'matrix',
                'id' => $row->id
            )
        );
        return html_writer::link($url, $title);
    }
}