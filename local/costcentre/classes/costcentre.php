<?php
// This file is part of the Arup cost centre system
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

namespace local_costcentre;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use Exception;
use moodle_exception;
use moodle_url;

class costcentre {
    const GROUP_LEADER = 1;
    const BUSINESS_ADMINISTRATOR = 2;
    const APPRAISER = 4;
    const SIGNATORY = 8;
    const REPORTER = 16;
    const HR_LEADER = 32;
    const HR_ADMIN = 64;
    const GROUP_LEADER_APPRAISAL = 128;
    const LEARNING_REPORTER = 256;
    const ALL = 511;

    /** @var string The current page. */
    private $page;
    /** @var string The current action. */
    private $action;
    /** @var bool Access all ability. */
    private $canaccessall;
    /** @var bool HR Admin Access all ability. */
    private $hraccessall;
    /** @var context The current context. */
    private $context;
    /** @var string The current cost centre. */
    private $costcentre = '';
    /** @var string[] Array(user) The current selected user. */
    private $currentuser = array();
    /** @var string[] Array (permissions) of permissions. */
    private $permissions;
    /** @var string[] Array (menu) of costcentres for current user. */
    private $costcentresmenu;
    /** @var string[] Array (permissions) of userpermissionsdata */
    private $userpermissionsdata = array();
    /** @var false|stdClass Settings for current cost centre. */
    private $settings;
    /** @var array Array of forms for (top of) page. */
    private $forms = array();
    /** @var string Renderable page content. */
    private $content;
    /** @var array Page alerts. */
    private $alerts = array();
    /** @var \local_costcentre\output\renderer Renderer. */
    private $renderer;

    /** @var string[] Valid actions by page. */
    private static $validaction = array(
        'index' => 'edit',
        'view' => 'save',
        'usersettings' => 'load'
    );

    public static function get_permission_list() {
        return [
        \local_costcentre\costcentre::GROUP_LEADER           => get_string('select:groupleader', 'local_costcentre'),
                \local_costcentre\costcentre::GROUP_LEADER_APPRAISAL => get_string('select:groupleaderappraisal', 'local_costcentre'),
                \local_costcentre\costcentre::HR_LEADER              => get_string('select:hrleader', 'local_costcentre'),
                \local_costcentre\costcentre::HR_ADMIN               => get_string('select:hradmin', 'local_costcentre'),
                \local_costcentre\costcentre::BUSINESS_ADMINISTRATOR =>  get_string('select:businessadmin', 'local_costcentre'),
                \local_costcentre\costcentre::APPRAISER                 => get_string('select:appraiser', 'local_costcentre'),
                \local_costcentre\costcentre::SIGNATORY                 => get_string('select:signatory', 'local_costcentre'),
                \local_costcentre\costcentre::REPORTER                  => get_string('select:reporter', 'local_costcentre'),
                \local_costcentre\costcentre::LEARNING_REPORTER         => get_string('select:learningreporter', 'local_costcentre')
        ];
    }

    /**
     * Constructor.
     *
     * @global \moodle_database $DB
     * @param string $page
     * @throws moodle_exception
     */
    public function __construct($page, \local_costcentre\output\renderer $renderer) {
        global $DB, $SESSION, $USER;

        if (!isset($SESSION->localcostcentre)) {
            $SESSION->localcostcentre = new stdClass();
        }

        if (!empty($SESSION->localcostcentre->alerts)) {
            $this->alerts = $SESSION->localcostcentre->alerts;
        }

        $SESSION->localcostcentre->alerts = [];

        $this->context = \context_system::instance();
        $this->canaccessall = has_capability('local/costcentre:administer', $this->context);

        $ishradmin = self::is_user($USER->id, array(costcentre::HR_LEADER, costcentre::HR_ADMIN));

        $this->hraccessall = ($ishradmin && has_capability('local/costcentre:administer_hr', $this->context));

        $this->set_permission_mappings();

        $this->page = $page;
        if (!array_key_exists($this->page, self::$validaction)) {
            throw new moodle_exception('error:invalid:page', 'local_costcentre');
        }
        $this->action = optional_param('action', '', PARAM_ALPHA);
        if (!empty($this->action) && $this->action != self::$validaction[$this->page]) {
            throw new moodle_exception('error:invalid:action', 'local_costcentre');
        }

        if ($this->page == 'index') {
            $this->costcentresmenu = $this->get_costcentresmenu();
            $costcentre = optional_param('costcentre', '', PARAM_ALPHANUMEXT);
            if ($this->action == self::$validaction[$this->page] && empty($costcentre)) {
                // Reset action as no cost centre selected.
                $this->action = '';
            } else if ($this->action == self::$validaction[$this->page]) {
                $this->costcentre = $costcentre;
            } else if (count($this->costcentresmenu) == 1) {
                $this->action = self::$validaction[$this->page];
                reset($this->costcentresmenu);
                $this->costcentre = key($this->costcentresmenu);
            }

            if (!empty($this->costcentre)) {
                $this->settings = $DB->get_record('local_costcentre', array('costcentre' => $this->costcentre));
            }
        } else if ($this->page == 'usersettings') {
            $user = optional_param('user', '', PARAM_INT);
            if (!empty($user) && $this->action == self::$validaction[$this->page]) {
                // load user's cost centre permission
                $this->userpermissionsdata = self::get_user_costcentres_permissions($user);
                $params = array('id' => $user);
                $usertextconcat = $DB->sql_concat('firstname', "' '", 'lastname', "' ('", 'email', "')'");
                $this->currentuser = $DB->get_record('user', $params, $usertextconcat . ' as name, id', MUST_EXIST);
                if (empty($this->userpermissionsdata)) {
                    $this->alerts[] = new \local_costcentre\output\alert(get_string('alert:usernopermission', 'local_costcentre'), 'warning', false);
                }
            }
        }

        $this->renderer = $renderer;
    }

    /**
     * Magic getter.
     *
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name) {
        if (method_exists($this, "get_{$name}")) {
            return $this->{"get_{$name}"}();
        }
        if (!isset($this->{$name})) {
            throw new Exception('Undefined property ' .$name. ' requested');
        }
        return $this->{$name};
    }

    /**
     * Get the valid action for the page.
     *
     * @return string
     */
    private function get_validaction() {
        return self::$validaction[$this->page];
    }

    /**
     * Set alert.
     *
     * @global stdClass $SESSION
     * @param \local_costcentre\output\alert $alert
     */
    private function set_alert(\local_costcentre\output\alert $alert) {
        global $SESSION;
        $SESSION->localcostcentre->alerts[] = $this->alerts[] = $alert;
    }
    /**
     * Prepare the page for rendering.
     *
     * @throws Exception
     */
    public function prepare_page() {
        $method = __FUNCTION__ . '_' . $this->page;
        if (!method_exists($this, $method)) {
            throw new Exception('Undefined method ' .$method. ' requested');
        }
        // Prepare generic select form for index page.
        if ($this->page == 'index') {
            $this->forms[] = new \local_costcentre\local\form\select(null, array('costcentre' => $this));
        }

        // Prepare user selection for usersettings page.
        if ($this->page == 'usersettings') {
            $this->forms[] = new \local_costcentre\local\form\user_select(null, array('costcentre' => $this));
        }
        // Prepare specific page.
        $this->{$method}();
    }

    /**
     * Prepare view page
     *
     * @throws moodle_exception
     */
    private function prepare_page_view() {
        $userpermissionform = new \local_costcentre\local\form\user_permission(null, array('costcentre' => $this));
        $this->forms[] = $userpermissionform;

        if ($userpermissionform->process()) {
            $this->set_alert(
                new \local_costcentre\output\alert(
                    get_string('view:save:success', 'local_costcentre'),
                    'success',
                    false)
            );
            redirect(new moodle_url('/local/costcentre/view.php'));
        } else if ($userpermissionform->is_cancelled()) {
            $this->set_alert(
                new \local_costcentre\output\alert(
                    get_string('view:save:cancelled', 'local_costcentre'),
                    'warning',
                    false)
            );

        }

        if (!$this->canaccessall) {
            throw new moodle_exception('error:invalid:page', 'local_costcentre');
        }
    }


    private function prepare_page_usersettings() {
        $permissionsform = new \local_costcentre\local\form\user_permission_check(null, array('costcentre' => $this));
        $this->forms[] = $permissionsform;

        if ($permissionsform->process()) {
            $this->set_alert(
                new \local_costcentre\output\alert(
                    get_string('view:save:success', 'local_costcentre'),
                    'success',
                    false)
            );
            // Redirect and reload the current user's permission
            redirect(
                new moodle_url('/local/costcentre/usersettings.php',
                    array(
                        'user' => $this->currentuser->id,
                        'action' => $this->action
                    )
                )
            );
        } else if ($permissionsform->is_cancelled()) {
            $this->set_alert(
                new \local_costcentre\output\alert(
                    get_string('view:save:cancelled', 'local_costcentre'),
                    'warning',
                    false)
            );
        }

        if (!$this->canaccessall) {
            throw new moodle_exception('error:invalid:page', 'local_costcentre');
        }
    }

    /**
     * Prepare the index page for rendering.
     */
    private function prepare_page_index() {
        // Edit form.
        $editform = new \local_costcentre\local\form\edit(null, array('costcentre' => $this));
        $this->forms[] = $editform;
        if ($editform->process()) {
            $this->set_alert(
                new \local_costcentre\output\alert(
                    get_string('edit:success', 'local_costcentre'),
                    'success',
                    false)
            );
            redirect(new moodle_url('/local/costcentre/index.php', ['costcentre' => $this->costcentre, 'action' => 'edit']));
        } else if ($editform->is_cancelled()) {
            $this->set_alert(
                new \local_costcentre\output\alert(
                    get_string('edit:cancelled', 'local_costcentre'),
                    'warning',
                    false)
            );
        }
        if ($this->costcentre && !$this->canaccessall) {
            $a = (new moodle_url('/course/view.php', array('id' => get_config('local_costcentre', 'help_courseid'))))->out(false);
            $this->set_alert(
                new \local_costcentre\output\alert(
                    get_string('alert:restrictedaccess',
                            'local_costcentre', $a),
                    'info',
                    false)
            );
        }
    }

    /**
     * Return the rendered output for the page.
     *
     * @return string
     */
    public function output_page() {
        global $SESSION;
        $output = '';

        // Alerts.
        foreach ($this->alerts as $alert) {
            $output .= $this->renderer->render($alert);
        }
        // Clear alerts.
        $SESSION->localcostcentre->alerts = $this->alerts = [];

        // Forms.
        foreach ($this->forms as $form) {
            $output .= $form->render();
        }

        // Other content.
        if (!empty($this->content)) {
            $output .= $this->renderer->render($this->content);
        }

        return $output;
    }

    /**
     * Saves settings data for cost centre.
     *
     * @global \moodle_database $DB
     * @param stdClass $data
     */
    public function save($data) {
        global $DB;
        if (empty($this->settings)) {
            $id = $DB->insert_record('local_costcentre', $data);
            $this->settings = $DB->get_record('local_costcentre', array('id' => $id));
        } else {
            foreach ($this->settings as $setting => $value) {
                if (isset($data->{$setting})) {
                    $this->settings->{$setting} = $data->{$setting};
                }
            }
            $DB->update_record('local_costcentre', $this->settings);
        }
    }

    /**
     * Returns settings for cost centre.
     *
     * @return stdClass
     */
    public function get_settings() {
        return (!empty($this->settings) ? $this->settings : new stdClass());
    }

    /**
     * Returns user to permission mappings for cost centre.
     * @global \moodle_database $DB
     * @return stdClass
     */
    public function get_mappings() {
        global $DB;

        $mappings = new stdClass();

        if (empty($this->costcentre)) {
            return $mappings;
        }

        foreach ($this->permissions as $key => $value) {
            $mapping = $DB->get_records_select_menu(
                    'local_costcentre_user',
                    'costcentre = :costcentre AND '.$DB->sql_bitand('permissions', $key).' = :permission',
                    array('costcentre' => $this->costcentre, 'permission' => $key),
                    '',
                    'userid as id, userid');
            $mappings->{$value} = $mapping;
        }

        return $mappings;
    }

    public function set_permission_mappings() {
        $this->permissions = array(
            self::GROUP_LEADER              => 'groupleader',
            self::GROUP_LEADER_APPRAISAL    => 'groupleaderappraisal',
            self::HR_LEADER                 => 'hrleader',
            self::HR_ADMIN                  => 'hradmin',
            self::BUSINESS_ADMINISTRATOR    => 'businessadmin',
            self::APPRAISER                 => 'appraiser',
            self::SIGNATORY                 => 'signatory',
            self::REPORTER                  => 'reporter',
            self::LEARNING_REPORTER         => 'learningreporter',
        );
    }

    /**
     * Processes and saves user to permission mappings for cost centre.
     *
     * @global \moodle_database $DB
     * @param stdClass $data
     */
    public function process_mappings($data) {
        global $DB;
        $select = "auth = 'saml' AND deleted = 0 AND suspended = 0 AND confirmed = 1";
        $validusers = $DB->get_records_select_menu('user', $select, array(), 'lastname ASC', "id, id as userid");

        $users = array();
        foreach ($this->permissions as $key => $value) {
            if (!empty($data->{$value})) {
                foreach ($data->{$value} as $userid) {
                    $userid = (int) $userid;
                    if (!array_key_exists($userid, $validusers)) {
                        continue;
                    }
                    // Add up permissions bits.
                    $users[$userid] = isset($users[$userid]) ? $users[$userid] + $key : $key;
                }
            }
        }

        // Clear out users who no longer have permissions.
        if (!empty($users)) {
            list($usql, $params) = $DB->get_in_or_equal(array_keys($users), SQL_PARAMS_NAMED, 'uid', false);
            $where = "costcentre = :costcentre AND userid {$usql}";
            $params['costcentre'] = $this->costcentre;
            $DB->delete_records_select('local_costcentre_user', $where, $params);
        } else {
            $DB->delete_records('local_costcentre_user', array('costcentre' => $this->costcentre));
        }

        // Add/update permissions in DB.
        foreach ($users as $userid => $permissions) {
            $existing = $DB->get_record('local_costcentre_user', array('costcentre' => $this->costcentre, 'userid' => $userid));
            if (!$existing) {
                $new = new stdClass();
                $new->costcentre = $this->costcentre;
                $new->userid = $userid;
                $new->permissions = $permissions;
                $new->id = $DB->insert_record('local_costcentre_user', $new);
            } else if ($existing->permissions != $permissions) {
                $existing->permissions = $permissions;
                $DB->update_record('local_costcentre_user', $existing);
            }
        }
    }

    /**
     * Returns array of administerable cost centres for current user.
     * Cost centre code is key.
     * Concatenated cost centre code and name is value.
     *
     * @global \moodle_database $DB
     * @global stdClass $USER
     * @return string[]
     */
    public function get_costcentresmenu() {
        global $DB, $USER;
        if (!isset($this->costcentresmenu)) {
            if ($this->canaccessall) {
                // User can access all so need list of all user cost centres in Moodle.
                // @TODO: Update this to use webservice to obtain full current list.
                $concat = $DB->sql_concat('u.icq', "' - '", 'u.department');
                $sql = "SELECT u.icq, {$concat}
                          FROM {user} u
                          JOIN (
                                SELECT MAX(id) maxid
                                  FROM {user} inneru
                                  JOIN (
                                        SELECT icq, MAX(timemodified) as maxtimemodified
                                          FROM {user}
                                      GROUP BY icq
                                       ) groupedicq
                                    ON inneru.icq = groupedicq.icq
                                       AND inneru.timemodified = groupedicq.maxtimemodified
                              GROUP BY groupedicq.icq
                               ) groupedid
                            ON u.id = groupedid.maxid
                         WHERE u.icq <> ''
                      ORDER BY u.icq ASC";
                $distinctusercostcentres = $DB->get_records_menu(
                        'local_costcentre_user',
                        array(),
                        'costcentre ASC',
                        'DISTINCT costcentre as id, costcentre as value');
                $costcentres = $DB->get_records_sql_menu($sql) + $distinctusercostcentres;
                ksort($costcentres);
                $this->costcentresmenu = $costcentres;
            } else {
                // Otherwise only BAs can administer cost centres.
                $this->costcentresmenu = self::get_user_cost_centres($USER->id, [self::BUSINESS_ADMINISTRATOR, self::HR_LEADER, self::HR_ADMIN]);
            }
        }
        return $this->costcentresmenu;
    }

    /**
     * Returns lists of users ready for select element
     *
     * @return mixed
     */
    public function get_userlist() {
        global $DB;
        $usertextconcat = $DB->sql_concat('firstname', "' '", 'lastname', "' ('", 'email', "')'");
        $params = array();
        $where = "auth = 'saml' AND deleted = 0 AND suspended = 0 AND confirmed = 1";
        return $DB->get_records_select_menu('user', $where, $params, 'lastname ASC', "id, $usertextconcat");
    }

    /**
     * Returns user's cost centre and permissions
     *
     * @param $userid
     * @return array
     */
    public function get_user_costcentres_permissions($userid) {
        global $DB;

        $where = 'lcu.userid = :userid AND lcu.permissions > 0';
        $params = array('userid' => $userid);

        $concat = $DB->sql_concat('u.icq', "' - '", 'u.department');
        $sql = <<<EOS
            SELECT
                u.icq, {$concat} as costcentre
            FROM
                {user} u
            INNER JOIN
                (SELECT
                    MAX(id) maxid
                FROM
                    {user} inneru
                INNER JOIN
                    (SELECT
                        icq, MAX(timemodified) as maxtimemodified
                    FROM
                        {user}
                    GROUP BY
                        icq) groupedicq
                    ON inneru.icq = groupedicq.icq AND inneru.timemodified = groupedicq.maxtimemodified
                GROUP BY
                    groupedicq.icq) groupedid
                ON u.id = groupedid.maxid
            INNER JOIN
                {local_costcentre_user} lcu ON lcu.costcentre = u.icq AND {$where}
            ORDER BY
                u.icq ASC
EOS;
        $costcentresuser = $DB->get_records_sql($sql, $params);
        foreach ($costcentresuser as $key => $val) {
            $permissions = new stdClass();

            foreach ($this->permissions as $key => $value) {
                $permission = $DB->get_records_select(
                    'local_costcentre_user',
                    'costcentre = :costcentre AND '.$DB->sql_bitand('permissions', $key).' = :permission AND userid = :userid AND permissions > 0',
                    array('costcentre' => $val->icq, 'permission' => $key, 'userid' => $userid),
                    '',
                    'userid');
                if ($permission ) {
                    $permissions->{$value} = $key;
                }
            }
            $val->permissions = $permissions;
        }
        return $costcentresuser;
    }

    /**
     * Returns array of cost centres for current user given permission(s).
     * Cost centre code is key.
     * Concatenated cost centre code and name is value.
     *
     * @global \moodle_database $DB
     * @param int $userid
     * @param int|int[] $permissions
     * @return string[]
     */
    public static function get_user_cost_centres($userid, $permissions) {
        global $DB;

        if (!is_array($permissions)) {
            $permissions = array($permissions);
        }

        $where = 'lcu.userid = :userid';
        $params = array('userid' => $userid);

        $bitandwhere = array();
        $permissioncount = 1;
        // Loop through requested permissions and build bitwise where clause.
        foreach ($permissions as $permission) {
            if ($permission == 0) {
                continue;
            }
            $bitand = $DB->sql_bitand('permissions', $permission);
            $bitandwhere[] = $bitand . ' = :permission' . $permissioncount;
            $params['permission'.$permissioncount] = $permission;
            $permissioncount++;
        }

        if (empty($bitandwhere)) {
            // No non-zero permissions.
            return array();
        }

        $where .= ' AND (' . implode(' OR ', $bitandwhere) . ')';

        // TODO: Use list of cost centres from webservice.
        $concat = $DB->sql_concat('u.icq', "' - '", 'u.department');
        $sql = "SELECT u.icq, {$concat}
                  FROM {user} u
                  JOIN (
                        SELECT MAX(id) maxid
                          FROM {user} inneru
                          JOIN (
                                SELECT icq, MAX(timemodified) as maxtimemodified
                                  FROM {user}
                              GROUP BY icq
                               ) groupedicq
                             ON inneru.icq = groupedicq.icq
                                AND inneru.timemodified = groupedicq.maxtimemodified
                      GROUP BY groupedicq.icq
                       ) groupedid
                    ON u.id = groupedid.maxid
                  JOIN {local_costcentre_user} lcu
                    ON lcu.costcentre = u.icq AND {$where}
              ORDER BY u.icq ASC";
        return $DB->get_records_sql_menu($sql, $params);
    }

    /**
     * Checks is a user has specified permisssion(s) for any cost centre or, if provided, the specified cost centre.
     *
     * @param int $userid
     * @param int|int[] $permissions
     * @param string $costcentre
     * @return bool
     */
    public static function is_user($userid, $permissions, $costcentre = null) {
        if (!empty($costcentre)) {
            return array_key_exists($costcentre, self::get_user_cost_centres($userid, $permissions));
        } else {
            return (bool) count(self::get_user_cost_centres($userid, $permissions));
        }
    }

    /**
     * Returns an array of user ids (key) and their permissions (value) for a specific costcentre and set of permissions.
     *
     * @global \moodle_database $DB
     * @param string $costcentre
     * @param int|array $permissions
     * @return array
     */
    public static function get_cost_centre_users($costcentre, $permissions = array()) {
        global $DB;

        if (!is_array($permissions)) {
            $permissions = array($permissions);
        }

        $where = 'lcu.costcentre = :costcentre';
        $params = array('costcentre' => $costcentre);

        $bitandwhere = array();
        $permissioncount = 1;
        foreach ($permissions as $permission) {
            if ($permission == 0) {
                continue;
            }
            $bitand = $DB->sql_bitand('lcu.permissions', $permission);
            $bitandwhere[] = $bitand . ' = :permission' . $permissioncount;
            $params['permission'.$permissioncount] = $permission;
            $permissioncount++;
        }

        if (!empty($bitandwhere)) {
            $where .= ' AND (' . implode(' OR ', $bitandwhere) . ')';
        }

        $sql = "SELECT lcu.userid, lcu.permissions
                  FROM {local_costcentre_user} lcu
                 WHERE {$where}";
        return $DB->get_records_sql_menu($sql, $params);
    }

    /**
     * Returns true if the groupleader is active on this cost centre.
     *
     * @global \moodle_database $DB
     * @param string $costcentre
     * @return bool
     */
    public static function get_cost_centre_groupleaderactive($costcentre) {
        global $DB;
        if ($DB->get_record('local_costcentre', array('costcentre' => $costcentre, 'groupleaderactive' => 1))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns true if the appraiserissupervisor setting is active on this cost centre.
     *
     * @global \moodle_database $DB
     * @param string $costcentre
     * @return bool
     */
    public static function get_cost_centre_appraiserissupervisor($costcentre) {
        global $DB;
        return (bool) $DB->get_field('local_costcentre', 'appraiserissupervisor', ['costcentre' => $costcentre]);
    }

    /**
     * Get all settings or a specific setting for a cost centre.
     *
     * @global \moodle_database $DB
     * @param string $costcentre
     * @param null|string $setting
     * @return string|stdClass
     */
    public static function get_setting($costcentre, $setting = null) {
        global $DB;
        $settings = $DB->get_record('local_costcentre', array('costcentre' => $costcentre));
        if ($setting) {
            return (!empty($settings->{$setting})) ? $settings->{$setting} : false;
        }
        return $settings;
    }

    /**
     * Update a user's permissions for a cost centre.
     *
     * @global \moodle_database $DB
     * @param int $userid
     * @param string $costcentre
     * @param array $addpermissions
     * @param array $removepermissions
     * @return bool
     */
    public static function update_user_permissions(
            $userid, $costcentre, array $addpermissions = [], array $removepermissions = []) {
        global $DB;

        $settings = self::get_setting($costcentre);
        if (!$settings) {
            // Cost centre is not set up.
            return false;
        }

        $existing = $DB->get_record('local_costcentre_user', array('costcentre' => $costcentre, 'userid' => $userid));
        if (!$existing) {
            $existing = new stdClass();
            $existing->costcentre = $costcentre;
            $existing->userid = $userid;
            $existing->permissions = 0;
        }

        // Used later to see if we bother inserting/updating.
        $existingpermissions = $existing->permissions;

        foreach ($addpermissions as $addpermission) {
            if ((self::ALL & (int) $addpermission) !== (int) $addpermission) {
                // Not a valid permission.
                continue;
            }
            if (((int) $existing->permissions & (int) $addpermission) === (int) $addpermission) {
                // Already set.
                continue;
            }

            $existing->permissions += $addpermission;
        }

        foreach ($removepermissions as $removepermission) {
            if ((self::ALL & (int) $removepermission) !== (int) $removepermission) {
                // Not a valid permission.
                continue;
            }
            if (((int) $existing->permissions & (int) $removepermission) !== (int) $removepermission) {
                // Already not set.
                continue;
            }

            $existing->permissions -= $removepermission;
        }

        if ($existing->permissions == $existingpermissions) {
            // Nothing to do.
            return true;
        }

        if (empty($existing->id)) {
            return $DB->insert_record('local_costcentre_user', $existing);
        } else {
            return $DB->update_record('local_costcentre_user', $existing);
        }
    }
}
