<?php
global $CFG;

require_once($CFG->libdir.'/tablelib.php');
/**
 * Description of delegatetable
 *
 * @author paulstanyer
 */
class delegatetable extends table_sql {

    private $helpforheaders = array();
    protected $_delegatelist;

    public function __construct($uniqueid, delegate_list $delegatelist) {
        parent::__construct($uniqueid);
        $this->_delegatelist = $delegatelist;
    }
    
    public function initialise() {
        $this->define_baseurl($this->_delegatelist->get_url());

        switch ($this->_delegatelist->get_function()) {
            case 'print' :
                $this->initialise_print();
                return;
            case 'download' :
                $this->initialise_download();
                return;
        }

        $columns = array('select', 'user', 'address', 'bookingstatus');
        if ($this->_delegatelist->has_capability('manager')) {
            array_push($columns, 'sponsorlastname');
        }
        array_push($columns, 'timeaccess');

        $this->define_columns($columns);

        // disable select as sort column
        $this->no_sorting('select');
        $this->no_sorting('phone');

        $this->column_class('select', 'mdl-align');
        $this->column_class('user', 'mdl-left');
        $this->column_class('address', 'mdl-left');
        $this->column_class('bookingstatus', 'mdl-left');
        if ($this->_delegatelist->has_capability('manager')) {
            $this->column_class('sponsor', 'mdl-left');
        }
        $this->column_class('timeaccess', 'mdl-left');
        
        // define the main sortable column
        $sortable = array(
            'firstname' => get_string('table:firstname', 'local_delegatelist'),
            'lastname' => get_string('table:lastname', 'local_delegatelist'),
            'idnumber' => get_string('table:staffid', 'local_delegatelist'),
            'icq' => get_string('table:department', 'local_delegatelist'),
            'phone' => get_string('table:phone', 'local_delegatelist')
        );

        $headers = array(
            get_string('select', 'local_delegatelist'),
            $sortable,
            get_string('table:office', 'local_delegatelist'),
            get_string('table:status', 'local_delegatelist'),
        );
        if ($this->_delegatelist->has_capability('manager')) {
            array_push($headers, get_string('table:sponsor', 'local_delegatelist'));
        }
        array_push($headers, get_string('table:lastaccess', 'local_delegatelist'));

        $this->define_headers($headers);
    }

    public function initialise_print() {
        $columns = array('user', 'address', 'bookingstatus', 'signature');
        $this->define_columns($columns);

        $this->no_sorting('phone');

        $this->column_class('user', 'mdl-left');
        $this->column_class('address', 'mdl-left');
        $this->column_class('bookingstatus', 'mdl-left');
        $this->column_class('signature', 'mdl-left');
        $this->column_style('signature', 'width', '25%');

        // define the main sortable column
        $sortable = array(
            'firstname' => get_string('table:firstname', 'local_delegatelist'),
            'lastname' => get_string('table:lastname', 'local_delegatelist'),
            'idnumber' => get_string('table:staffid', 'local_delegatelist'),
            'icq' => get_string('table:department', 'local_delegatelist'),
            'phone' => get_string('table:phone', 'local_delegatelist')
        );

        $headers = array(
            $sortable,
            get_string('table:office', 'local_delegatelist'),
            get_string('table:status', 'local_delegatelist'),
            get_string('table:signature', 'local_delegatelist')
        );
        $this->define_headers($headers);
    }

    public function initialise_download() {
        $columns = array('firstname', 'lastname', 'staffid', 'department', 'icq', 'phone1', 'address', 'bookingstatus');
        if ($this->_delegatelist->has_capability('manager')) {
            array_push($columns, 'sponsorfirstname', 'sponsorlastname', 'sponsoremail');
        }
        array_push($columns, 'timeaccess');

        $this->define_columns($columns);

        // define the main sortable column
        $headers = array(
            get_string('table:firstname', 'local_delegatelist'),
            get_string('table:lastname', 'local_delegatelist'),
            get_string('table:staffid', 'local_delegatelist'),
            get_string('table:department', 'local_delegatelist'),
            get_string('table:icq', 'local_delegatelist'),
            'Phone',
            get_string('table:office', 'local_delegatelist'),
            get_string('table:status', 'local_delegatelist'),
        );
        if ($this->_delegatelist->has_capability('manager')) {
            array_push($headers, get_string('table:sponsorfirstname', 'local_delegatelist'));
            array_push($headers, get_string('table:sponsorlastname', 'local_delegatelist'));
            array_push($headers, get_string('table:sponsoremail', 'local_delegatelist'));
        }
        array_push($headers, get_string('table:lastaccess', 'local_delegatelist'));
        $this->define_headers($headers);
    }

    /**
     * Override this function to support user_column that contains group of sort links
     *
     * Displays table header of the delegates list
     */
    public function print_headers()
    {

        global $CFG, $OUTPUT;

        echo html_writer::start_tag('thead');
        echo html_writer::start_tag('tr');
        foreach ($this->columns as $column => $index) {

            $icon_hide = '';
            if ($this->is_collapsible) {
                $icon_hide = $this->show_hide_link($column, $index);
            }

            $primarysortcolumn = '';
            $primarysortorder  = '';
            if (reset($this->prefs['sortby'])) {
                $primarysortcolumn = key($this->prefs['sortby']);
                $primarysortorder  = current($this->prefs['sortby']);
            }

            switch ($column) {
                case 'fullname':
                    // Check the full name display for sortable fields.
                    $nameformat = $CFG->fullnamedisplay;
                    if ($nameformat == 'language') {
                        $nameformat = get_string('fullnamedisplay');
                    }
                    $requirednames = order_in_string(get_all_user_name_fields(), $nameformat);

                    if (!empty($requirednames)) {
                        if ($this->is_sortable($column)) {
                            // Done this way for the possibility of more than two sortable full name display fields.
                            $this->headers[$index] = '';
                            foreach ($requirednames as $name) {
                                $sortname = $this->sort_link(get_string($name),
                                    $name, $primarysortcolumn === $name, $primarysortorder);
                                $this->headers[$index] .= $sortname . ' / ';
                            }
                            $helpicon = '';
                            if (isset($this->helpforheaders[$index])) {
                                $helpicon = $OUTPUT->render($this->helpforheaders[$index]);
                            }
                            $this->headers[$index] = substr($this->headers[$index], 0, -3). $helpicon;
                        }
                    }
                    break;

                case 'userpic':
                    // do nothing, do not display sortable links
                    break;
                case 'user':
                    if(isset($this->headers[$index]) && is_array($this->headers[$index])) {
                        $user_column = '';
                        foreach ($this->headers[$index] as $item => $val) {
                            if ($this->is_sortable($item)) {
                                $user_column .= $this->sort_link($val,
                                        $item, $item == $primarysortcolumn, $primarysortorder) . ' / ';
                            } else {
                                $user_column .= $val . ' / ';
                            }
                        }

                        $user_column = rtrim(trim($user_column), '/');

                        $this->headers[$index] = $user_column;
                    }
                    break;
                default:
                    if ($this->is_sortable($column)) {
                        $helpicon = '';
                        if (isset($this->helpforheaders[$index])) {
                            $helpicon = $OUTPUT->render($this->helpforheaders[$index]);
                        }
                        $this->headers[$index] = $this->sort_link($this->headers[$index],
                                $column, $primarysortcolumn == $column, $primarysortorder) . $helpicon;
                    }
            }

            $attributes = array(
                'class' => 'header c' . $index . $this->column_class[$column],
                'scope' => 'col',
            );
            if ($this->headers[$index] === NULL) {
                $content = '&nbsp;';
            } else if (!empty($this->prefs['collapse'][$column])) {
                $content = $icon_hide;
            } else {
                if (is_array($this->column_style[$column])) {
                    $attributes['style'] = $this->make_styles_string($this->column_style[$column]);
                }
                $helpicon = '';
                if (isset($this->helpforheaders[$index]) && !$this->is_sortable($column)) {
                    $helpicon  = $OUTPUT->render($this->helpforheaders[$index]);
                }
                $content = $this->headers[$index] . $helpicon . html_writer::tag('div',
                        $icon_hide, array('class' => 'commands'));
            }
            echo html_writer::tag('th', $content, $attributes);
        }

        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thead');
    }

    /**
     * Override this function for print_header function works properly as it was overridden also
     *
     * Always use this function if you need to create header with sorting and help icon.
     *
     * @param renderable[] $helpicons An array of renderable objects to be used as help icons
     */
    public function define_help_for_headers($helpicons) {
        $this->helpforheaders = $helpicons;
    }

    public function other_cols($column, $row) {
        global $OUTPUT;
        switch ($column) {
            case 'select' :
                return html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'user[]', 'value' => $row->userid, 'data-email' => urlencode($row->email)));

            case 'user' :
                $user = new stdClass;
                $user->id = $row->userid;
                $usernamefields = get_all_user_name_fields();
                foreach($usernamefields as $usernamefield) {
                    $user->{$usernamefield} = $row->{$usernamefield};
                }
                $user->picture = $row->picture;
                $user->email = $row->email;
                $user->imagealt = '';
                $content =  html_writer::start_div('col-xs-12 p-0');
                $content .=  html_writer::div($OUTPUT->user_picture($user, array('size' => 100, 'link' => false)), 'picture pull-left');
                $content .= fullname($row) . html_writer::empty_tag('br');
                $content .= $row->staffid;
                if (!empty($row->icq) || !empty($row->department)) {
                    $content .= html_writer::empty_tag('br') . $row->icq . ' ' . $row->department;
                }
                if (!empty($row->phone1)) {
                    $content .= html_writer::empty_tag('br') . $row->phone1;
                }
                $content .= html_writer::end_div();
                return $content;
                
            case 'timeaccess':
                if (empty($row->timeaccess)) {
                    return get_string('notaccessed', 'local_delegatelist');
                }
                return date('dS M Y H:i', $row->timeaccess);

            case 'bookingstatus' :
                return $this->_delegatelist->get_status_type_string($row->bookingstatus)->string;

            case 'signature' :
                return '';

            case 'sponsorlastname' :
                $content = $row->sponsorfirstname . ' ' . $row->sponsorlastname;
                $content .= html_writer::empty_tag('br');
                $content .= $row->sponsoremail;
                return $content;
            case 'sponsor' :
                $content = $row->sponsorfirstname . ' ' . $row->sponsorlastname;
                $content .= html_writer::empty_tag('br');
                $content .= $row->sponsoremail;
                return $content;
        }
        return NULL;
    }

    public function print_nothing_to_display() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_delegatelist');
        echo $renderer->alert(get_string('noresults', 'local_delegatelist'), 'alert-warning', false);
    }
    
    
    /* Need to override this whole method to change sorting */
    /**
     * Must be called after table is defined. Use methods above first. Cannot
     * use functions below till after calling this method.
     * @return type?
     */
    public function setup() {
        global $SESSION;

        if (empty($this->columns) || empty($this->uniqueid)) {
            return false;
        }

        // Load any existing user preferences.
        if ($this->persistent) {
            $this->prefs = json_decode(get_user_preferences('flextable_' . $this->uniqueid), true);
        } else if (isset($SESSION->flextable[$this->uniqueid])) {
            $this->prefs = $SESSION->flextable[$this->uniqueid];
        }

        // Set up default preferences if needed.
        if (!$this->prefs or optional_param($this->request[TABLE_VAR_RESET], false, PARAM_BOOL)) {
            $this->prefs = array(
                'collapse' => array(),
                'sortby'   => array(),
                'i_first'  => '',
                'i_last'   => '',
                'textsort' => $this->column_textsort,
            );
        }
        $oldprefs = $this->prefs;

        if (($showcol = optional_param($this->request[TABLE_VAR_SHOW], '', PARAM_ALPHANUMEXT)) &&
                isset($this->columns[$showcol])) {
            $this->prefs['collapse'][$showcol] = false;

        } else if (($hidecol = optional_param($this->request[TABLE_VAR_HIDE], '', PARAM_ALPHANUMEXT)) &&
                isset($this->columns[$hidecol])) {
            $this->prefs['collapse'][$hidecol] = true;
            if (array_key_exists($hidecol, $this->prefs['sortby'])) {
                unset($this->prefs['sortby'][$hidecol]);
            }
        }

        // Now, update the column attributes for collapsed columns
        foreach (array_keys($this->columns) as $column) {
            if (!empty($this->prefs['collapse'][$column])) {
                $this->column_style[$column]['width'] = '10px';
            }
        }

        if (($sortcol = optional_param($this->request[TABLE_VAR_SORT], '', PARAM_ALPHANUMEXT)) &&
                $this->is_sortable($sortcol) && empty($this->prefs['collapse'][$sortcol]) &&
                $this->validcolumn($sortcol)) {

            if (array_key_exists($sortcol, $this->prefs['sortby'])) {
                // This key already exists somewhere. Change its sortorder and bring it to the top.
                $sortorder = $this->prefs['sortby'][$sortcol] == SORT_ASC ? SORT_DESC : SORT_ASC;
                unset($this->prefs['sortby'][$sortcol]);
                $this->prefs['sortby'] = array_merge(array($sortcol => $sortorder), $this->prefs['sortby']);
            } else {
                // Key doesn't exist, so just add it to the beginning of the array, ascending order
                $this->prefs['sortby'] = array_merge(array($sortcol => SORT_ASC), $this->prefs['sortby']);
            }

            // Finally, make sure that no more than $this->maxsortkeys are present into the array
            $this->prefs['sortby'] = array_slice($this->prefs['sortby'], 0, $this->maxsortkeys);
        }

        // MDL-35375 - If a default order is defined and it is not in the current list of order by columns, add it at the end.
        // This prevents results from being returned in a random order if the only order by column contains equal values.
        if (!empty($this->sort_default_column))  {
            if (!array_key_exists($this->sort_default_column, $this->prefs['sortby'])) {
                $defaultsort = array($this->sort_default_column => $this->sort_default_order);
                $this->prefs['sortby'] = array_merge($this->prefs['sortby'], $defaultsort);
            }
        }

        $ilast = optional_param($this->request[TABLE_VAR_ILAST], null, PARAM_RAW);
        if (!is_null($ilast) && ($ilast ==='' || strpos(get_string('alphabet', 'langconfig'), $ilast) !== false)) {
            $this->prefs['i_last'] = $ilast;
        }

        $ifirst = optional_param($this->request[TABLE_VAR_IFIRST], null, PARAM_RAW);
        if (!is_null($ifirst) && ($ifirst === '' || strpos(get_string('alphabet', 'langconfig'), $ifirst) !== false)) {
            $this->prefs['i_first'] = $ifirst;
        }

        // Save user preferences if they have changed.
        if ($this->prefs != $oldprefs) {
            if ($this->persistent) {
                set_user_preference('flextable_' . $this->uniqueid, json_encode($this->prefs));
            } else {
                $SESSION->flextable[$this->uniqueid] = $this->prefs;
            }
        }
        unset($oldprefs);

        if (empty($this->baseurl)) {
            debugging('You should set baseurl when using flexible_table.');
            global $PAGE;
            $this->baseurl = $PAGE->url;
        }

        $this->currpage = optional_param($this->request[TABLE_VAR_PAGE], 0, PARAM_INT);
        $this->setup = true;

        // Always introduce the "flexible" class for the table if not specified
        if (empty($this->attributes)) {
            $this->attributes['class'] = 'flexible';
        } else if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = 'flexible';
        } else if (!in_array('flexible', explode(' ', $this->attributes['class']))) {
            $this->attributes['class'] = trim('flexible ' . $this->attributes['class']);
        }
    }

    public function validcolumn($col)
    {
        if (!empty($this->columns[$col])) {
            return true;
        }
        if (!empty($this->sql->fields)) {
            $fields = array_map('trim', explode(',', $this->sql->fields));
            foreach($fields as $field) {
                if ($field === $col) {
                    return true;
                }
                if (false !== preg_match('/\w\.'.$col.'/i', $field)) {
                    return true;
                }
                if (false !== preg_match('/as\s+'.$col.'/', $field)) {
                    return true;
                }
            }
        }
        return false;
    }
    
     /**
     * Get the columns to sort by, in the form required by {@link construct_order_by()}.
     * @return array column name => SORT_... constant.
     */
    public function get_sort_columns() {
        if (!$this->setup) {
            throw new coding_exception('Cannot call get_sort_columns until you have called setup.');
        }

        if (empty($this->prefs['sortby'])) {
            return array();
        }

        foreach ($this->prefs['sortby'] as $column => $notused) {
            if ($this->validcolumn($column)) {
                continue; // This column is OK.
            }
            // This column is not OK.
            unset($this->prefs['sortby'][$column]);
        }

        return $this->prefs['sortby'];
    }

    public function wrap_html_start() {
        if ($this->_delegatelist->get_function() == 'display') {
            // Start form
            $actionurl = $this->_delegatelist->get_url();
            echo html_writer::start_tag('form', array('id' => 'delegatelistform', 'action' => s($actionurl->out_omit_querystring()), 'method' => 'post'));
            foreach ($actionurl->params() as $param => $value) {
                echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $param, 'value' => $value));
            }
        }
    }

    public function wrap_html_finish() {
        if ($this->_delegatelist->get_function() == 'display') {
            // Add buttons and end form
            echo html_writer::start_div('buttons');
            echo html_writer::tag('button', get_string('selectall'), array('id' => 'checkall', 'class' => 'btn'));
            echo html_writer::tag('button', get_string('deselectall'), array('id' => 'checknone', 'class' => 'btn'));
            echo html_writer::end_div();
            echo html_writer::start_div('buttons');
            echo html_writer::tag('button', get_string('sendemail', 'local_delegatelist'), array('id' => 'sendemail', 'class' => 'btn btn-primary'));
            echo html_writer::end_div();
            echo html_writer::end_tag('form');
        }
    }

    public function custom_page_size() {
        global $DB;
        if ($this->countsql === NULL) {
                $this->countsql = 'SELECT COUNT(1) FROM '.$this->sql->from.' WHERE '.$this->sql->where;
                $this->countparams = $this->sql->params;
        }
        return $DB->count_records_sql($this->countsql, $this->countparams);
    }

    public function download_buttons() {
        if ($this->is_downloadable() && !$this->is_downloading() && $this->_delegatelist->has_capability('manager')) {
            $downloadoptions = $this->get_download_menu();

            $downloadelements = new stdClass();
            $downloadelements->formatsmenu = html_writer::select($downloadoptions,
                    'download', $this->defaultdownloadformat, false);
            $downloadelements->downloadbutton = html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('download')));
            $url = str_ireplace('/index.php', '/download.php', $this->baseurl);
            $html = html_writer::start_tag('form', array('action' => $url, 'method' => 'post'));
            $activeclass = $this->_delegatelist->get_active_class();
            $activeclassid = empty($activeclass)? 0 : $activeclass->classid;
            $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'classid', 'value' => $activeclassid));
            foreach ($this->_delegatelist->get_filters() as $name => $value) {
                $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $name, 'value' => $value));
            }
            $html .= html_writer::start_div('mdl-right');
            $html .= html_writer::tag('label', get_string('downloadas', 'table', $downloadelements));
            $html .= html_writer::end_div();
            $html .= html_writer::end_tag('form');

            return $html;
        } else {
            return '';
        }
    }
}
