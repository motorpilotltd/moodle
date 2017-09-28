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
 * Certification enrolment plugin.
 *
 * @author Kamil Helmrich <kamil.helmrich@webanywhere.co.uk>
 */



use enrol_certification\certif;

require_once("$CFG->dirroot/enrol/certification/classes/certif.php");


class enrol_certification_plugin extends enrol_plugin
{

    protected $lasternoller = null;
    protected $lasternollerinstanceid = 0;


    /**
     * Returns localised name of enrol instance
     *
     * @param stdClass $instance (null is accepted too)
     *
     * @return string
     */
    public function get_instance_name($instance)
    {
        global $DB;

        if (empty($instance->name)) {
            if (!empty($instance->roleid) and $role = $DB->get_record('role', array('id' => $instance->roleid))) {
                $role = ' (' . role_get_name($role, context_course::instance($instance->courseid, IGNORE_MISSING)) . ')';
            } else {
                $role = '';
            }
            $enrol = $this->get_name();
            return get_string('pluginname', 'enrol_' . $enrol) . $role;
        } else {
            return format_string($instance->name);
        }
    }

    public function roles_protected()
    {
        // Users may tweak the roles later.
        return false;
    }

    public function allow_unenrol(stdClass $instance)
    {
        // Users with unenrol cap may unenrol other users manually manually.
        return true;
    }

    public function allow_manage(stdClass $instance)
    {
        // Users with manage cap may tweak period and status.
        return true;
    }

    public function show_enrolme_link(stdClass $instance)
    {

        if (true !== $this->can_certif_enrol($instance, false)) {
            return false;
        }

        return true;
    }

    /**
     * Return true if we can add a new instance to this course.
     *
     * @param int $courseid
     *
     * @return boolean
     */
    public function can_add_instance($courseid)
    {
        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/certification:config', $context)) {
            return false;
        }

        return true;
    }

    /**
     * Self enrol user to course
     *
     * @param stdClass $instance enrolment instance
     * @param stdClass $data     data needed for enrolment.
     *
     * @return bool|array true if enroled else eddor code and messege
     */
    public function enrol_certification(stdClass $instance, $data = null)
    {
        global $USER;

        $timestart = time();
        $timeend = 0;

        $this->enrol_user($instance, $USER->id, $instance->roleid, $timestart, $timeend);


    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     *
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance)
    {
        global $OUTPUT;
        $certifid = optional_param('certifid', 0, PARAM_INT);
        $enrolstatus = $this->can_certif_enrol($instance);
        if (true === $enrolstatus && $certifid==$instance->customint8) {
            $this->enrol_certification($instance);
        } else {
            $data = new stdClass();
            $data->header = $this->get_instance_name($instance);
            $data->info = $enrolstatus;
            $url = isguestuser() ? get_login_url() : null;
            $form = new enrol_certification_empty_form($url, $data);;
            ob_start();
            if ($instance->customint8 == $certifid) {
                $form->display();
            }
            $output = ob_get_clean();
            return $OUTPUT->box($output);
        }
    }

    /**
     * Checks if user can certif enrol.
     *
     * @param stdClass $instance           enrolment instance
     * @param bool     $checkuserenrolment if true will check if user enrolment is inactive.
     *                                     used by navigation to improve performance.
     *
     * @return bool|string true if successful, else error message or false.
     */
    public function can_certif_enrol(stdClass $instance, $checkuserenrolment = true)
    {
        global $DB, $USER;
        if ($checkuserenrolment && !empty($instance)) {
            if ($DB->get_record('user_enrolments', array('userid' => $USER->id, 'enrolid' => $instance->id))) {
                return true;
            }
        }
        /**
         * Checking if user can be enrol via certification
         * Returns true if can, if not string with error information
         */
        $check_certif = certif::can_enrol_certif($instance->courseid, $instance->customint8);
        if ($check_certif['canbeenrol'] == false) {
            return $check_certif['errorinfo'];
        }
        if ($instance->status != ENROL_INSTANCE_ENABLED) {
            return get_string('canntenrol', 'enrol_certification');
        }
        return true;
    }

    /**
     * Return information for enrolment instance containing list of parameters required
     * for enrolment, name of enrolment plugin etc.
     *
     * @param stdClass $instance enrolment instance
     *
     * @return stdClass instance info.
     */
    public function get_enrol_info(stdClass $instance)
    {

        $instanceinfo = new stdClass();
        $instanceinfo->id = $instance->id;
        $instanceinfo->courseid = $instance->courseid;
        $instanceinfo->type = $this->get_name();
        $instanceinfo->name = $this->get_instance_name($instance);
        $instanceinfo->status = $this->can_certif_enrol($instance);

        return $instanceinfo;
    }

    /**
     * Add new instance of enrol plugin with default settings.
     *
     * @param stdClass $course
     *
     * @return int id of new instance
     */
    public function add_default_instance($course)
    {
        $fields = $this->get_instance_defaults();

        return $this->add_instance($course, $fields);
    }

    /**
     * Returns defaults for new instances.
     *
     * @return array
     */
    public function get_instance_defaults()
    {

        $fields = array();
        $fields['status'] = ENROL_INSTANCE_ENABLED;
        $fields['roleid'] = $this->get_config('roleid');

        return $fields;
    }


    /**
     * Sync all meta course links.
     *
     * @param progress_trace $trace
     * @param int            $courseid one course, empty mean all
     *
     * @return int 0 means ok, 1 means error, 2 means plugin disabled
     */
    public function sync(progress_trace $trace, $courseid = null)
    {
        return;
    }

    /**
     * Returns the user who is responsible for self enrolments in given instance.
     *
     * Usually it is the first editing teacher - the person with "highest authority"
     * as defined by sort_by_roleassignment_authority() having 'enrol/certification:manage'
     * capability.
     *
     * @param int $instanceid enrolment instance id
     *
     * @return stdClass user record
     */
    protected function get_enroller($instanceid)
    {
        global $DB;

        if ($this->lasternollerinstanceid == $instanceid and $this->lasternoller) {
            return $this->lasternoller;
        }

        $instance = $DB->get_record('enrol', array('id' => $instanceid, 'enrol' => $this->get_name()), '*', MUST_EXIST);
        $context = context_course::instance($instance->courseid);

        if ($users = get_enrolled_users($context, 'enrol/certification:manage')) {
            $users = sort_by_roleassignment_authority($users, $context);
            $this->lasternoller = reset($users);
            unset($users);
        } else {
            $this->lasternoller = parent::get_enroller($instanceid);
        }

        $this->lasternollerinstanceid = $instanceid;

        return $this->lasternoller;
    }

    /**
     * Gets an array of the user enrolment actions.
     *
     * @param course_enrolment_manager $manager
     * @param stdClass                 $ue A user enrolment object
     *
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue)
    {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;

        if ($this->allow_manage($instance) && has_capability("enrol/certification:manage", $context)) {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url, array('class' => 'editenrollink', 'rel' => $ue->id));
        }
        return $actions;
    }

    /**
     * Restore instance and map settings.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass                          $data
     * @param stdClass                          $course
     * @param int                               $oldid
     */
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid)
    {
        global $DB;
        if ($step->get_task()->get_target() == backup::TARGET_NEW_COURSE) {
            $merge = false;
        } else {
            $merge = array('courseid' => $data->courseid, 'enrol' => $this->get_name(), 'roleid' => $data->roleid,);
        }
        if ($merge and $instances = $DB->get_records('enrol', $merge, 'id')) {
            $instance = reset($instances);
            $instanceid = $instance->id;
        } else {
            $instanceid = $this->add_instance($course, (array)$data);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);
    }

    /**
     * Restore user enrolment.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass                          $data
     * @param stdClass                          $instance
     * @param int                               $oldinstancestatus
     * @param int                               $userid
     */
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus)
    {
        $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
    }

    /**
     * Restore role assignment.
     *
     * @param stdClass $instance
     * @param int      $roleid
     * @param int      $userid
     * @param int      $contextid
     */
    public function restore_role_assignment($instance, $roleid, $userid, $contextid)
    {
        // This is necessary only because we may migrate other types to this instance,
        // we do not use component in manual or self enrol.
        role_assign($roleid, $userid, $contextid, '', 0);
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     *
     * @return bool
     */
    public function can_delete_instance($instance)
    {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/certification:config', $context);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     *
     * @return bool
     */
    public function can_hide_show_instance($instance)
    {
        $context = context_course::instance($instance->courseid);

        if (!has_capability('enrol/certification:config', $context)) {
            return false;
        }

        // If the instance is currently disabled, before it can be enabled,
        // we must check whether the password meets the password policies.
        if ($instance->status == ENROL_INSTANCE_DISABLED) {
            if ($this->get_config('requirepassword')) {
                if (empty($instance->password)) {
                    return false;
                }
            }
            // Only check the password if it is set.
            if (!empty($instance->password) && $this->get_config('usepasswordpolicy')) {
                if (!check_password_policy($instance->password, $errmsg)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Return an array of valid options for the status.
     *
     * @return array
     */
    protected function get_status_options()
    {
        $options = array(ENROL_INSTANCE_ENABLED => get_string('yes'), ENROL_INSTANCE_DISABLED => get_string('no'));
        return $options;
    }

    /**
     * Add elements to the edit instance form.
     *
     * @param stdClass        $instance
     * @param MoodleQuickForm $mform
     * @param context         $context
     *
     * @return bool
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context)
    {
        global $CFG;

        $roles = $this->extend_assignable_roles($context, $instance->roleid);
        $mform->addElement('select', 'roleid', get_string('role', 'enrol_certification'), $roles);

    }

    /**
     * We are a good plugin and don't invent our own UI/validation code path.
     *
     * @return boolean
     */
    public function use_standard_editing_ui()
    {
        return true;
    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @param array   $data     array of ("fieldname"=>value) of submitted data
     * @param array   $files    array of uploaded files "element_name"=>tmp_file_path
     * @param object  $instance The instance loaded from the DB
     * @param context $context  The context of the instance we are editing
     *
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     * @return void
     */
    public function edit_instance_validation($data, $files, $instance, $context)
    {
        $errors = array();
        $validstatus = array_keys($this->get_status_options());
        $context = context_course::instance($instance->courseid);
        $validroles = array_keys($this->extend_assignable_roles($context, $instance->roleid));
        $tovalidate = array('status' => $validstatus, 'roleid' => $validroles);

        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
    }


    /**
     * Add new instance of enrol plugin.
     *
     * @param object $course
     * @param array  $fields instance fields
     *
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = null)
    {
        return parent::add_instance($course, $fields);
    }

    /**
     * Update instance of enrol plugin.
     *
     * @param stdClass $instance
     * @param stdClass $data modified instance fields
     *
     * @return boolean
     */
    public function update_instance($instance, $data)
    {
        return parent::update_instance($instance, $data);
    }

    /**
     * Gets a list of roles that this user can assign for the course as the default for self-enrolment.
     *
     * @param context $context     the context.
     * @param integer $defaultrole the id of the role that is set as the default for self-enrolment
     *
     * @return array index is the role id, value is the role name
     */
    public function extend_assignable_roles($context, $defaultrole)
    {
        global $DB;

        $roles = get_assignable_roles($context, ROLENAME_BOTH);
        if (!isset($roles[$defaultrole])) {
            if ($role = $DB->get_record('role', array('id' => $defaultrole))) {
                $roles[$defaultrole] = role_get_name($role, $context, ROLENAME_BOTH);
            }
        }
        return $roles;
    }
}
