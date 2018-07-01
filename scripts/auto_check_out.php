<?php
# Make sure this script doesn't run via the webserver
if (php_sapi_name() != 'cli') {
    echo "It is not allowed to run this script through the webserver.\n";
    exit(1);
}
# This page sends an E-mail if a due date is getting near
# includes all due_dates not met
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'core.php');
$t_core_path = config_get('core_path');

require_once($t_core_path . 'bug_api.php');
require_once($t_core_path . 'email_api.php');
require_once($t_core_path . 'bugnote_api.php');
require_once($t_core_path . 'category_api.php');
require_once($t_core_path . 'helper_api.php');

require_once(__DIR__ . '/../core/attendances_api.php');

plugin_push_current('Attendances');

$users = attendances_get_auto_check_out();
foreach ($users as $user) {
    $ok = auth_attempt_script_login(user_get_name($user['user_id']));
    if (!$ok) { // # Unable to login. Could be deleted user
        continue;
    }
    attendances_check_out(true);

    user_clear_cache( $user['user_id'] );
    current_user_set( null );
}