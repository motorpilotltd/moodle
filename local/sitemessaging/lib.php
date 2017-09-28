<?php

require_once($CFG->libdir.'/adminlib.php');

function local_sitemessaging_reset_auto_config($sessiontimeout) {
    set_config('countdown_inprogress', NULL, 'local_sitemessaging');
    set_config('login_stopped', NULL, 'local_sitemessaging');
    if (!empty($sessiontimeout)) {
        set_config('sessiontimeout', $sessiontimeout);
        set_config('sessiontimeout', NULL, 'local_sitemessaging');
    }
}

/**
 * Checkbox
 *
 * Special version to carry out some resetting, extended from admin_setting_configcheckbox
 */
class local_sitemessaging_admin_setting_configcheckbox extends admin_setting_configcheckbox {
    /**
     * Sets the value for the setting
     *
     * Sets the value for the setting to either the yes or no values
     * of the object by comparing $data to yes
     *
     * @param mixed $data Gets converted to str for comparison against yes value
     * @return string empty string or error
     */
    public function write_setting($data) {
        if ((string)$data === $this->no) { // convert to strings before comparison
            // Reset data as a key setting is disabled
            local_sitemessaging_reset_auto_config(get_config('local_sitemessaging', 'sessiontimeout'));
        }
        return parent::write_setting($data);
    }
}

/**
 * Date/Time selector
 *
 * Derived from admin_setting_configtime
 */
class local_sitemessaging_admin_setting_configdatetime extends admin_setting {
    /**
     * Constructor
     * @param string $name base name
     * @param string $visiblename localised
     * @param string $description long localised info
     */
    public function __construct($name, $visiblename, $description) {
        parent::__construct($name, $visiblename, $description, NULL);
    }

    /**
     * Get the selected time
     *
     * @return mixed A timestamp, or null if not set
     */
    public function get_setting() {
        return $this->config_read($this->name);
    }

    /**
     * Store the time (hours and minutes)
     *
     * @param array $data Must be form 'h'=>xx, 'm'=>xx
     * @return string empty string if ok, string error message otherwise
     */
    public function write_setting($data) {
        if (!is_array($data)) {
            return '';
        }
        $hours = sprintf("%02d", $data['h']);
        $minutes = sprintf("%02d", $data['m']);
        $countdownuntil = strtotime("{$data['d']} {$hours}{$minutes}");
        if ($countdownuntil < time()) {
            // Reset data as countdown until time is in the past
            local_sitemessaging_reset_auto_config(get_config('local_sitemessaging', 'sessiontimeout'));
        }

        $result = $this->config_write($this->name, $countdownuntil);
        return ($result ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Returns XHTML time select fields
     *
     * @param array $data Must be form 'h'=>xx, 'm'=>xx
     * @param string $query
     * @return string XHTML time select fields and wrapping div(s)
     */
    public function output_html($data, $query='') {
        $now = time();

        $data = (int)$data;

        $current = array(
            'd' => date('Y-m-d', $data),
            'h' => date('H', $data),
            'm' => date('i', $data)
        );

        $days = array(
            date('Y-m-d', strtotime('today', $now)) => 'Today',
            date('Y-m-d', strtotime('tomorrow', $now)) => 'Tomorrow',
            date('Y-m-d', strtotime('+2 days', $now)) => '+2 days',
            date('Y-m-d', strtotime('+3 days', $now)) => '+3 days'
        );
        if (!array_key_exists($current['d'], $days)) {
            $days = array($current['d'] => date('d M Y', $data)) + $days;
        }
        for ($i=0; $i<=23; $i++) {
            $hours[$i] = sprintf("%02d",$i);
        }
        for ($i=0; $i<60; $i+=10) {
            $minutes[$i] = sprintf("%02d",$i);
        }

        $return = '<div class="form-time defaultsnext">';
        $past = $data < $now ? 'Expired' : date('d M Y H:i', $data);
        $return .= 'Current setting: '.$past.'<br />';
        $return .= '<select id="'.$this->get_id().'d" name="'.$this->get_full_name().'[d]">';
        foreach ($days as $value => $text) {
            $return .= '<option value="'.$value.'"'.($value == $current['d'] ? ' selected="selected"' : '').'>'.$text.'</option>';
        }
        $return .= '</select>&nbsp;<select id="'.$this->get_id().'h" name="'.$this->get_full_name().'[h]">';
        foreach ($hours as $value => $text) {
            $return .= '<option value="'.$value.'"'.($value == $current['h'] ? ' selected="selected"' : '').'>'.$text.'</option>';
        }
        $return .= '</select>:<select id="'.$this->get_id().'m" name="'.$this->get_full_name().'[m]">';
        foreach ($minutes as $value => $text) {
            $return .= '<option value="'.$value.'"'.($value == $current['m'] ? ' selected="selected"' : '').'>'.$text.'</option>';
        }
        $return .= '</select></div>';
        return format_admin_setting($this, $this->visiblename, $return, $this->description, false, '', NULL, $query);
    }

}