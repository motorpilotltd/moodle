<?php
$string['pluginname'] = 'Site messaging';

// Settings page strings
$string['active'] = 'Show message?';
$string['active_desc'] = 'Whether to allow theme to display message';

$string['body'] = 'Message body';
$string['body_desc'] = 'Body of message for theme to display';

$string['configuration'] = 'Site Messaging Configuration';
$string['countdown_active'] = 'Show countdown timer';
$string['countdown_active_desc'] = 'Whether to show the countdown timer at the end of the message.';
$string['countdown_ended'] = 'Countdown finished text';
$string['countdown_ended_default'] = 'NOW';
$string['countdown_ended_desc'] = 'Text that will be shown in place of the countdown timer when the countdown has finished.';
$string['countdown_heading'] = 'Countdown to an event';
$string['countdown_heading_desc'] = 'Enter details for counting down to an event, normally a maintenance window.'
        . '<br />Only active if messaging is enabled above.';
$string['countdown_pre'] = 'Pre-countdown text';
$string['countdown_pre_desc'] = 'Text displayed before the countdown timer.';
$string['countdown_stop_login'] = 'Stop logins';
$string['countdown_stop_login_desc'] = 'Stop logins a set amount of time before the countdown ends (useful for maintenance windows).'
        . '<br />Will also temporarily shorten the session time so inactive user sessions die before the end of the countdown.'
        . '<br />NB. \'{$a}\' must be enabled above.';
$string['countdown_stop_login_message'] = 'Message on stopped login page';
$string['countdown_stop_login_time'] = 'Minutes before to stop logins';
$string['countdown_stop_login_time_desc'] = 'How many minutes before the end of the countdown logins should be stopped.';
$string['countdown_until'] = 'Countdown to';
$string['countdown_until_desc'] = 'Set the end time for the countdown timer (Server timezone: {$a}).';

$string['error:login_stopped'] = 'Logging in is currently disabled by the site administrators. Please refer to applicable site messaging above.';
$string['error:login_stopped_title'] = 'Login prohibited';

$string['taskcheckcountdown'] = 'Check countdown status';
$string['title'] = 'Message title';
$string['title_desc'] = 'Title of message for theme to display';
$string['type'] = 'Type of message';
$string['type_desc'] = 'The type of message can allow the theme to control how to render the message.';

$string['url'] = 'URL';
$string['url_desc'] = 'URL where the user can get further info.';
$string['url_text'] = 'URL text';
$string['url_text_desc'] = 'Link text for the URL for further info.';
