<?php
require_once '../../config.php';
require_once 'reportlib.php';

try {
    $filename = required_param('data', PARAM_FILE);
    $filepath = "{$CFG->dataroot}/threesixty/spiderdata/{$filename}";
    $data = unserialize(base64_decode(file_get_contents($filepath)));
    unlink($filepath);
    require_login();
    $context = context_module::instance($data->cmid);
    if (!has_capability('mod/threesixty:viewreports', $context)) {
        require_capability('mod/threesixty:viewownreports', $context);
    }
    print_spiderweb($data, $context);
} catch (Exception $e) {
    send_file_not_found();
    exit;
}