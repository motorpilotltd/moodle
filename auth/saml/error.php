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
 * Error handling for auth_saml.
 *
 * @package    auth_saml
 * @copyright  2016 Motorpilot Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Formatted output of auth_saml errors.
 *
 * @param array $err
 * @param mixed $urltogo
 * @param string $logfile
 * @return void
 */
function saml_error($err, $urltogo = false, $logfile = '') {
    global $CFG, $PAGE, $OUTPUT;

    if (!isset($CFG->debugdisplay) || !$CFG->debugdisplay) {
        $debug = false;
    } else {
        $debug = true;
    }

    if ($urltogo != false) {
        $site = get_site();
        if ($site === false || !isset($site->fullname)) {
            $sitename = '';
        } else {
            $sitename = $site->fullname;
        }
        $PAGE->set_title($sitename .':Error SAML Login');

        echo $OUTPUT->header();
    }
    if (is_array($err)) {
        foreach ($err as $key => $messages) {
            if (!is_array($messages)) {
                if ($urltogo != false && $debug) {
                    echo render_saml_error_alert($messages);
                }
                $msg = 'Moodle SAML module: '  .$key . ': ' . $messages;
                log_saml_error(str_ireplace('<br />', ' ', $msg), $logfile);
            } else {
                foreach ($messages as $message) {
                    if ($urltogo != false && $debug) {
                        echo render_saml_error_alert($message);
                    }
                    $msg = 'Moodle SAML module: ' . $key . ': ' . $message;
                    log_saml_error(str_ireplace('<br />', ' ', $msg), $logfile);
                }
            }
            echo '<br>';
        }
    } else {
        if ($urltogo != false) {
            echo render_saml_error_alert($err);
        }
        $msg = 'Moodle SAML module: login: '.$err;
        log_saml_error(str_ireplace('<br />', ' ', $msg), $logfile);
    }
    if ($urltogo != false) {
        echo '</div>';
        $OUTPUT->continue_button($urltogo);
        $OUTPUT->footer();
        exit();
    }
}

/**
 * Rendering of auth_saml errors.
 *
 * @param string $msg
 * @return string
 */
function render_saml_error_alert($msg) {
    $icon = html_writer::tag('i', '', array('class' => 'fa fa-exclamation-triangle'));
    $class = 'alert alert-danger fade in';
    return html_writer::tag('div', $icon.' '.$msg, array('class' => $class));
}

/**
 * Logging of auth_saml errors.
 *
 * @param string $msg
 * @param mixed $logfile
 * @return void
 */
function log_saml_error($msg, $logfile) {
    global $CFG;
    // 0 - message  is sent to PHP's system logger, using the Operating System's system logging mechanism or a file.
    // 3 - message  is appended to the file destination.
    $destination = '';
    $errorlogtype = 0;
    if (!empty($logfile)) {
        if (substr($logfile, 0) == '/') {
            $destination = $logfile;
        } else {
            $destination = $CFG->dataroot . '/' . $logfile;
        }
        $errorlogtype = 3;
        $msg = decorate_saml_log($msg);
    }
    error_log($msg, $errorlogtype, $destination);
}

/**
 * Decorting of auth_saml errors for logging.
 *
 * @param string $msg
 * @return string
 */
function decorate_saml_log($msg) {
    return $msg = date('D M d H:i:s  Y') . ' [client ' . $_SERVER['REMOTE_ADDR'] . '] [error] ' . $msg . "\r\n";
}
