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
 *
 * @package local_regions
 */

defined('MOODLE_INTERNAL') || die;

class local_regions {
    private $version = '0.1.0';
    protected $user;
    protected $config;
    protected $course;

    public function __construct(array $configs = null) {
        if ($configs !== null) {
            $this->set_configs($configs);
        }
    }

    public function set_configs(array $configs) {
        foreach ($configs as $name => $value) {
            if (property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }

        return $this;
    }

    public function grab_moodle_globals() {
        global $CFG, $USER, $COURSE;

        $this->user = $USER;
        $this->course = $COURSE;
        $this->config = $CFG;

        return $this;
    }

    /**
     * Load a controller
     *
     * @param string $request
     * @return local_regions_controller
     */
    public function controller($request) {
        $requestparts = explode('/', $request);

        // Load controller abstract and find the requested controller.
        $this->load_file('lib/controller.php');
        if (!$this->load_file('controllers/' . $requestparts[0] . '.php', true)) {
            print_error('invalidpage');
        }

        // Controller class name.
        $class = 'local_regions_controller_' . $requestparts[0];

        // Setup action call.
        if (isset($requestparts[1])) {
            $action = $requestparts[1];
        } else {
            $action = 'index';
            $request = $requestparts[0] . '/index';
        }

        // Return the controller.
        return new $class($action, $request, $this);
    }

    /**
     * Load a model class
     *
     * @param string $name
     * @return local_regions_model
     */
    public function model($name) {
        $this->load_file('lib/model.php');
        $this->load_file('models/' . $name . '.php');
        $class = 'local_regions_model_' . $name;
        return new $class($this);
    }

    public function get_user() {
        return $this->user;
    }

    public function get_course() {
        return $this->course;
    }

    public function get_config($name = null) {
        if ($name !== null && isset($this->config->{$name})) {
            return $this->config->{$name};
        }
        return $this->config;
    }

    /**
     * Get current version
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Get plugin base dir
     *
     * @param string $file
     * @return string
     */
    public function get_basedir($file = null) {
        return $this->config->dirroot . '/local/regions/' . $file;
    }

    /**
     * Get language string from plugin specific lang dir
     *
     * @param string $name
     * @param string|object $a
     * @return string
     */
    public function get_string($name, $a = null) {
        return stripslashes(get_string($name, 'local_regions', $a));
    }

    /**
     * Get language string from moodle core language
     *
     * @param string $name
     * @param string|object $a
     * @return string
     */
    public function get_string_fromcore($name, $a = null) {
        return get_string($name, '', $a);
    }

    /**
     * Load a file
     *
     * @param string $file
     * @param boolean $disableerror
     * @return mix
     */
    public function load_file($file, $disableerror = false) {
        $filepath = $this->get_basedir($file);
        if (!file_exists($filepath)) {
            if ($disableerror) {
                return false;
            }
            print_error('invalidfiletoload');
        }
        require_once($filepath);
        return true;
    }

    /**
     * setup administrator links and settings
     *
     * @param object $admin
     */
    public static function set_adminsettings($admin) {
        $me = new self();
        $me->grab_moodle_globals();
        $context = context_course::instance(SITEID);

        $admin->add('root', new admin_category('regions', $me->get_string('regions')));
        $admin->add(
                'regions',
                new admin_externalpage(
                        'regionsmanage',
                        $me->get_string('regionsmanage'),
                        $me->get_config('wwwroot') . '/local/regions/index.php',
                        'moodle/site:config',
                        false,
                        $context
                        )
                );
    }
}