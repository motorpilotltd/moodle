<?php
// This file is part of the arup theme for Moodle
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
 * Theme arup config file.
 *
 * @package    theme_arup
 * @copyright  2016 Arup
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_arup_core_renderer extends theme_bootstrap_core_renderer {

    private $blockcount;

    public function body_attributes($additionalclasses = array()) {
        if (isset($this->page->theme->settings->notice)) {
            $additionalclasses[] = 'hasnotice';
        }
        // Make it easier to find pages that should have a white background.
        if ($this->page->pagelayout != 'frontpage') {
            $additionalclasses[] = 'nofront';
        }
        return parent::body_attributes($additionalclasses);
    }

    /**
     * Page header including breadcrumb and navbar button.
     */
    public function full_header() {
        if ($this->page->bodyid == 'page-site-index') {
            return '';
        }

        $template = new stdClass();

        $catalogue = ''; // Temporarily disabled: optional_param('catalogue', '', PARAM_ALPHA);
        $template->hasctbtns = false;
        if ($catalogue) {
            $catid = defined('LOCAL_ACCORDION_ID') ? LOCAL_ACCORDION_ID : 0;
            $template->hasctbtns = true;
            $card = new stdClass();
            $card->url = new moodle_url('/local/accordion/index.php', array('id' => $catid, 'catalogue' => 'card'));
            $card->icon = '<i class="glyphicon glyphicon-th"></i>';
            $card->active = '';
            if ($catalogue == 'card') {
                $card->active = 'active';
            }

            $accordion = new stdClass();
            $accordion->url = new moodle_url('/local/accordion/index.php', array('id' => $catid, 'catalogue' => 'accordion'));
            $accordion->icon = '<i class="glyphicon glyphicon-th-list"></i>';
            $accordion->active = '';
            if ($catalogue !== 'card') {
                $accordion->active = 'active';
            }

            $template->ctbtns = array($card, $accordion);
        }

        $template->breadcrumb = $this->navbar();
        $template->breadcrumbbtn = $this->page_heading_button();
        $template->courseheader = $this->course_header();

        return $this->render_from_template('theme_arup/header', $template);
    }

    /**
     * Navbar burger button for mobile layout.
     */
    public function navbar_button() {
        $template = new stdClass();
        return $this->render_from_template('theme_arup/navbarbutton', $template);
    }

    /**
     * Render the navbar with clear hidden elements.
     */
    public function navbar() {
        $items = $this->page->navbar->get_items();
        if (!empty($items) && preg_match('/page-local-onlineappraisal/', $this->page->bodyid)) {
            array_shift($items);
        }
        if (empty($items)) { // MDL-46107.
            return '';
        }
        $breadcrumbs = '';
        foreach ($items as $item) {
            $item->hideicon = true;
            if ($item->hidden) {
                $item->text .= get_string('hiddennavitem', 'theme_arup');
            }
            $breadcrumbs .= '<li>'.$this->render($item).'</li>';
        }
        return "<ol class=breadcrumb>$breadcrumbs</ol>";
    }

    /**
     * Shows the user time.
     */
    public function display_usertime() {
        $usertimezone = '';
        if (!empty($USER->timezone)) {
            $usertimezone = $USER->timezone;
            $tzclass = '';
        } else {
            $tzclass = ' notset';
        }
        if ($usertimezone == 99) {
            $tzclass = ' notset';
        }

        $usertimezone = core_date::get_user_timezone_object();
        $time = new DateTime();
        $time->setTimezone($usertimezone);
        $usertime = $time->format('G:i (T)');
        return $usertime;
    }

    /**
     * Shows the user time.
     */
    public function usertime_modal() {
        global $PAGE, $USER, $DB;

        if (!isloggedin() || isguestuser()) {
            return '';
        }

        $template = new stdClass();
        $template->userid = $USER->id;
        $template->sesskey = sesskey();
        $template->countries =  array();
        $template->checkmarkicon = $this->pix_icon('t/check', 'checkmark');

        $aruptimezones = $DB->get_records('local_timezones');
        if (count($aruptimezones) > 0) {
            foreach ($aruptimezones as $country) {
                $thiscountry = new stdClass();
                $thiscountry->name = $country->display;
                $thiscountry->timezone = $country->timezone;
                $template->countries[] = $thiscountry;
            }
        } else {
            $countries = core_date::get_list_of_timezones($USER->timezone, true);
            foreach ($countries as $country) {
                $thiscountry = new stdClass();
                $thiscountry->name = $country;
                $template->countries[] = $thiscountry;
            }
        }
        return $this->render_from_template('theme_arup/usertimemodal', $template);
    }

    // Copied from old version of theme_bootstrap as needed for override.
    public function user_menu($user = null, $withlinks = null) {
        $usermenu = new custom_menu('', current_language());
        return $this->render_user_menu($usermenu, $user);
    }

    /**
     * Generates the user dropdown menu
     */
    protected function render_user_menu(custom_menu $menu, $user) {
        global $USER, $DB, $SESSION;

        if (empty($user)) {
            $user = $USER;
        }

        $menuclass = 'guest';

        if (isloggedin() && !isguestuser()) {

            $messagecount = $this->messagecount();
            $menu->add($this->glyphicon('bell') . $messagecount, new moodle_url('/message/'), 'inbox', 2);

            $usertime = $this->display_usertime();
            $menu->add($usertime, new moodle_url('#'), 'setusertime');

            $menuclass = 'loggedin';
            $userpicture = new user_picture($user);
            $userpicture->link = false;
            $userpicture->size = 40;
            $picture = html_writer::tag('span', $this->render($userpicture), array('class' => 'picspan'));

            $usermenu = $menu->add($picture, new moodle_url('#'), 'moodleuser', 4);

            $usermenu->add(
                $this->glyphicon('dashboard')  . get_string('mylearning', 'theme_arup'),
                new moodle_url('/my'),
                get_string('myhome')
            );


            $usermenu->add(
                $this->glyphicon('user') . get_string('profile'),
                new moodle_url('/user/profile.php', array('id' => $user->id)),

                get_string('profile')
            );

            $usermenu->add(
                $this->glyphicon('inbox') . get_string('messages', 'message'),
                new moodle_url('/message/index.php'),

                get_string('messages', 'message')
            );

            $usermenu->add(
                $this->glyphicon('cog') . get_string('preferences'),
                new moodle_url('/user/preferences.php'),

                get_string('preferences')
            );

            $usermenu->add(
                '#######',
                new moodle_url('/'),
                '#######'
            );

            $usermenu->add(
                $this->glyphicon('sign-out') . get_string('logout'),
                new moodle_url('/login/logout.php', array('sesskey' => sesskey(), 'alt' => 'logout')),
                get_string('logout')
            );
        } else {

            $menu->add(
                $this->glyphicon('sign-in')  . get_string('login'),
                new moodle_url('/login/index.php', array('alt' => get_string('login'))),
                get_string('login')
            );
        }

        $content = html_writer::start_tag('ul', array('class' => 'nav navbar-nav pull-right usermenu ' . $menuclass, 'role' => 'menubar'));
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1, 'pull-right');
        }
        $content .= html_writer::end_tag('ul');

        return $content;
    }

    /**
     * Get the number of unread messages for the user.
     */
    private function messagecount() {
        global $DB, $USER;
        $newmessagesql = "SELECT id, smallmessage, useridfrom, useridto, timecreated, fullmessageformat, notification, contexturl
                          FROM {message}
                          WHERE useridto = :userid";

        if ($messages = $DB->get_records_sql($newmessagesql, array('userid' => $USER->id))) {
            return '<i class="tmn-counts">'.count($messages).'</i>';
        }
    }

    /**
     * Create the custom menu from the overall theme settings page.
     */
    public function custom_menu($custommenuitems = '') {
        // The custom menu is always shown, even if no menu items
        // are configured in the global theme settings page.
        global $CFG;

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }

        if (!has_capability('theme/arup:viewcustommenu', context_system::instance())) {
            // Not allowed to view the custom menu.
            $custommenuitems = '';
        }

        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render_custom_menu($custommenu);
    }

    /**
     * Render custom menu from the overall theme settings page.
     */
    protected function render_custom_menu(custom_menu $menu) {
        $content = '<ul class="nav navbar-nav custommenu">';

        $custommenucontent = '';
        foreach ($menu->get_children() as $item) {
            $custommenucontent .= $this->render_custom_menu_item($item, 1);
        }

        $inappraisal = preg_match('/page-local-onlineappraisal/', $this->page->bodyid);
        $appraisalmenucontent = $this->appraisal_dropdown_menu($inappraisal);
        if ($inappraisal) {
            $content .= $appraisalmenucontent;
        } else {
            $content .= $custommenucontent;
        }
        // The new appraisal menu item.

        return $content.'</ul>';
    }

    /**
     * Render the custom menu dropdown items and main items Bootstrap 3 style.
     */
    protected function render_custom_menu_item(custom_menu_item $menunode, $level = 0, $direction = '' ) {
        static $submenucount = 0;

        if ($menunode->has_children()) {

            if ($level == 1) {
                $dropdowntype = 'dropdown ' .  $menunode->get_title();
            } else {
                $dropdowntype = 'dropdown-submenu';
            }

            $content = html_writer::start_tag('li', array('class' => $dropdowntype));
            // If the child has menus render it as a sub menu.
            $submenucount++;
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = new moodle_url('#cm_submenu_'.$submenucount);
            }
            $linkattributes = array(
                'href' => $url,
                'class' => 'dropdown-toggle',
                'data-toggle' => 'dropdown',
                'title' => $menunode->get_title(),
            );
            if ($this->_url_compare($this->page->url, $url, true)) {
                $linkattributes['class'] .= ' active';
            }
            $content .= html_writer::start_tag('a', $linkattributes);
            $content .= $menunode->get_text();
            if ($level == 1) {
                $content .= '<b class="caret"></b>';
            }
            $content .= '</a>';
            $content .= '<ul class="dropdown-menu '.$direction.'">';
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode, 0);
            }
            $content .= '</ul>';
        } else {
            $content = '<li>';
            // The node doesn't have children so produce a final menuitem.
            $class = $menunode->get_title();
            if (preg_match("/^#+$/", $menunode->get_text())) {
                $content = '<li class="divider" role="presentation">';
            } else {
                $content = '<li>';
                // The node doesn't have children so produce a final menuitem.
                if ($menunode->get_url() !== null) {
                    $url = $menunode->get_url();
                } else {
                    $url = '#';
                }
                if ($this->_url_compare($this->page->url, $url)) {
                    $class .= ' active';
                }
                $content .= html_writer::link($url, $menunode->get_text(), array('class' => $class,
                    'title' => $menunode->get_title()));
            }
        }
        return $content;
    }

    /**
     * Shortcut function for creating Glyphicons
     */
    private function glyphicon($icon, $size = null) {
        $icon = html_writer::tag('i', '', array('class' => 'glyphicon glyphicon-' . $icon));
        return html_writer::tag('span', $icon, array('class' => 'iconwrapper'));
    }

    /**
     * Copyright Shown in footer
     */
    public function copyright() {
        return '&copy; Arup ' . date('Y');
    }

    /**
     * Footer links are configure in the theme and shown in the course footer.
     */
    public function footerlinks() {
        global $PAGE;

        $return = '';

        if (isset($PAGE->theme->settings->footerlinks) && !empty($PAGE->theme->settings->footerlinks) && isloggedin()) {
            $footerlinks = str_ireplace("\r\n", "\n", $PAGE->theme->settings->footerlinks);
            $links = explode("\n", $footerlinks);
            foreach ($links as $link) {
                $linkinfo = explode('|', $link);
                if (count($linkinfo) == 2) {
                    $url = new moodle_url($linkinfo[1]);
                    $return .= ' / ' . html_writer::link($url, $linkinfo[0]);
                }
            }
        }

        return $return;
    }

    /**
     * Renders Bootstrap 3 tabtree
     *
     * @param tabtree $tabtree
     * @return string
     */
    protected function render_tabtree(tabtree $tabtree) {
        if (empty($tabtree->subtree)) {
            return '';
        }
        $firstrow = $secondrow = '';
        foreach ($tabtree->subtree as $tab) {
            $firstrow .= $this->render($tab);
            if (($tab->selected || $tab->activated) && !empty($tab->subtree) && $tab->subtree !== array()) {
                $secondrow = $this->tabtree($tab->subtree);
            }
        }
        return html_writer::tag('ul', $firstrow, array('class' => 'nav nav-tabs')) . $secondrow;
    }

    /**
     * Override parent block renderer to output blocks with class flexblock for front page
     */
    public function block(block_contents $bc, $region) {

        $addclass = ($this->blockcount - 1) % 2 == 0 ? ' second' : '';
        $addclass .= ($this->blockcount - 1) % 3 == 0 ? ' third' : '';
        $addclass .= ($this->blockcount - 1) % 4 == 0 ? ' fourth' : '';
        $this->blockcount++;

        $bc->attributes['class'] = $bc->attributes['class'] . $addclass;

        $output = parent::block($bc, $region);
        if ($region == 'centre'  && !$this->page->user_is_editing()) {
            return html_writer::tag('div', $output, array('class' => 'flexblock'));
        } else {
            return $output;
        }
    }

    protected function appraisal_dropdown_menu($inappraisal) {
        global $CFG;
        $classfile = $CFG->dirroot . '/local/onlineappraisal/classes/navbarmenu.php';
        if (file_exists($classfile)) {
            $renderer = $this->page->get_renderer('local_onlineappraisal', 'navbarmenu');
            $navbarmenu = new \local_onlineappraisal\navbarmenu();
            $template = new \local_onlineappraisal\output\navbarmenu\navbarmenu($navbarmenu);
            $template->inappraisal = $inappraisal;
            return $renderer->render($template);
        }
    }

    // FUNCTIONS BENEATH THIS LINE MIGHT BE DEPRECATED.

    public function course_search() {
        $courserenderer = $this->page->get_renderer('core', 'course');
        $content = $courserenderer->course_search_form();
        return $content;
    }

    protected function _url_compare(moodle_url $url1, moodle_url $url2, $dirname = false) {
        $base1 = $url1->out_omit_querystring();
        $base2 = $url2->out_omit_querystring();

        // Append index.php if there is no specific file
        if (substr($base1,-1) == '/') {
            $base1 .= 'index.php';
        }
        if (substr($base2,-1) == '/') {
            $base2 .= 'index.php';
        }

        // Compare the two base URLs
        if ($dirname && dirname($url1->get_path(false)) === dirname($url2->get_path(false))) {
            return true;
        } elseif ($base1 != $base2) {
            return false;
        }

        $url1params = $url1->params();
        foreach ($url2->params() as $param => $value) {
            if ($param == 'sesskey') {
                continue;
            }
            if (!array_key_exists($param, $url1params) || $url1params[$param] != $value) {
                return false;
            }
        }

        return true;
    }


    public function local_frontpage_alert() {
        global $DB, $USER;

        $dbman = $DB->get_manager();
        if ($dbman->table_exists('tapsenrol_iw_tracking') && get_config('local_taps', 'version')) {
            $taps = new \local_taps\taps();

            list($in, $inparams) = $DB->get_in_or_equal(
                $taps->get_statuses('requested'),
                SQL_PARAMS_NAMED, 'status'
            );
            $compare = $DB->sql_compare_text('lte.bookingstatus');

            $approvalssql = '
            SELECT
                COUNT(tit.id)
            FROM
                {tapsenrol_iw_tracking} tit
            JOIN
                {local_taps_enrolment} lte
                ON lte.enrolmentid = tit.enrolmentid
            JOIN
                {tapsenrol} t
                ON t.tapscourse = lte.courseid
            JOIN
                {tapsenrol_iw} ti
                ON ti.id = t.internalworkflowid
            JOIN
                {user} u
                ON u.idnumber = lte.staffid
            WHERE
                tit.approved IS NULL
                AND timecancelled IS NULL
                AND tit.sponsoremail = :sponsoremail
                AND (lte.archived = 0 OR lte.archived IS NULL)
                AND '.$compare.' '.$in;
            $approvalsparams = array(
                'sponsoremail' => strtolower($USER->email),
            );
            $approvals = $DB->count_records_sql($approvalssql, array_merge($approvalsparams, $inparams));
            if ($approvals) {
                $a = new stdClass();
                $a->count = $approvals;
                $a->plural = $approvals > 1 ? 's' : '';
                $approvalurl = new moodle_url('/mod/tapsenrol/approve.php');
                $class = 'frontpage-alert alert fade in alert-info';
                $content = html_writer::tag('button', '&times;', array('class' => 'close', 'data-dismiss' => 'alert', 'type' => 'button'));
                $content .= html_writer::tag('b', get_string('approvalstitle', 'theme_arup'));
                $content .= ' ';
                $content .= html_writer::link($approvalurl, get_string('approvalstext', 'theme_arup', $a));

                return html_writer::tag('div', $content, array('class' => $class));
            }
        }

        return '';
    }


    public function local_sitemessaging() {
        global $PAGE;

        $config = get_config('local_sitemessaging');

        if (!$config) {
            return '';
        }

        $message = new stdClass();
        $message->active = 0;
        $message->type = 'warning';
        $message->title = '';
        $message->body = '';
        $message->url = '';
        $message->url_text = '';

        $message = (object) array_merge((array) $message, (array) $config);

        if (!$message->active || !$message->body) {
            return '';
        }

        $alertclose = 'this.parentNode.parentNode.removeChild(this.parentNode);';
        $countdowncontent = '';
        if (!empty($config->countdown_active)) {
            $now = time();
            $PAGE->requires->js(new moodle_url('/local/sitemessaging/js/raf.js'), false);
            $PAGE->requires->js(new moodle_url('/local/sitemessaging/js/countdown.min.js'), false);
            $to = (empty($config->countdown_until) ? $now : $config->countdown_until);
            $toms = $to * 1000;
            $pre = empty($config->countdown_pre) ? '' : $config->countdown_pre;
            $ended = empty($config->countdown_ended) ? get_string('countdown_ended_default', 'local_sitemessaging') : $config->countdown_ended;
            $script = "
            function update() {
                countdown.setLabels(
                null,
                null,
                ', ',
                null,
                '{$ended}'
                );

                var counter = document.getElementById('countdown-timer');
                var ts = countdown(null, {$toms}, countdown.HOURS | countdown.MINUTES | countdown.SECONDS);

                if (ts.value <= 0) {
                    ts.value = 0;
                    ts.hours = 0;
                    ts.minutes = 0;
                    ts.seconds = 0;
                }

                counter.innerHTML = ts.toHTML('strong');

                /* Global scope for alert closing cancellation */
                anim = requestAnimationFrame(update);

                if (ts.value <= 0) {
                    cancelAnimationFrame(anim)
                }
            }
            update();";

            $PAGE->requires->js_init_code($script, true, null);
            $alertclose .= 'cancelAnimationFrame(anim);';
            if ($now >= $to) {
                $countdown = $ended;
            } else {
                $timespan = $to - $now;
                $hours = floor($timespan/3600);
                $minutes = floor(($timespan - $hours*3600)/60);
                $seconds = floor($timespan - $hours*3600 - $minutes*60);
                $countdownarray = array();
                if ($hours > 0) {
                    $s = $hours > 1 ? 's' : '';
                    $countdownarray[] = html_writer::tag('strong', $hours.' hour'.$s);
                }
                if ($minutes > 0) {
                    $s = $minutes > 1 ? 's' : '';
                    $countdownarray[] = html_writer::tag('strong', $minutes.' minute'.$s);
                }
                if ($seconds > 0) {
                    $s = $seconds > 1 ? 's' : '';
                    $countdownarray[] = html_writer::tag('strong', $seconds.' second'.$s);
                }
                $countdown = implode(', ', $countdownarray);
            }
            $countdowntimer = html_writer::tag('span', $countdown, array('id' => 'countdown-timer'));
            $countdowncontent .= html_writer::tag('p', $pre.$countdowntimer);
        }

        $class = "sitemessaging alert alert-{$message->type}";
        $content = html_writer::tag('button', '&times;', array('class' => 'close', 'onClick' => $alertclose, 'type' => 'button'));
        $content .= $message->title ? html_writer::tag('h4', $message->title) : '';
        $content .= html_writer::tag('p', nl2br($message->body));
        if ($message->url && $message->url_text) {
            $url = new moodle_url($message->url);
            $link = html_writer::link($url, $message->url_text);
            $content .= html_writer::tag('p', $link);
        }
        $content .= $countdowncontent;

        return html_writer::tag('div', $content, array('class' => $class));
    }

    /**
     * Shortcut function for links (depricated?)
     */
    protected static function a($attributes, $content) {
        return html_writer::tag('a', $content, $attributes);
    }

    /**
     * Shortcut function for divs (depricated?)
     */
    protected static function div($attributes, $content) {
        return html_writer::tag('div', $content, $attributes);
    }

    /**
     * Shortcut function for spans (depricated?)
     */
    protected static function span($attributes, $content) {
        return html_writer::tag('span', $content, $attributes);
    }

    public function user_picture(stdClass $user, array $options = null) {
        global $PAGE;
        if ($PAGE->bodyid == 'page-mod-forum-discuss' || $PAGE->bodyid == 'page-site-index' ) {
            $options = array('size' => '100');
        }

        $userpicture = new user_picture($user);
        foreach ((array)$options as $key=>$value) {
            if (array_key_exists($key, $userpicture)) {
                $userpicture->$key = $value;
            }
        }
        return $this->render($userpicture);
    }

}