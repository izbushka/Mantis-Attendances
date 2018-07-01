<?php

# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   MantisBT
 * @link      http://www.mantisbt.org
 */
/**
 * MantisBT Core API's
 */
require_once('core.php');

require_api('bug_api.php');
require_api('bugnote_api.php');
require_api('icon_api.php');
require_once('attendances_api.php');
require_once('page_api.php');

$t_filter = array();

$t_today = date('d:m:Y');

$f_action = gpc_get_string('action', null);
$t_attendance_bug_id = attendances_get_current_bug_id();
$check_in = attendances_get_check_in_status();

if (!empty($f_action)) {
    if ($f_action == 'checkin' && !$check_in) {
        attendances_check_in();
    } elseif ($f_action == 'checkout' && $check_in) {
        attendances_check_out();
    }
    print_successful_redirect(plugin_page('attendances_page', true));
}

layout_page_header(plugin_lang_get('title'));
layout_page_begin();

echo '<br/>';
echo plugin_lang_get('current_task') . ': ' . attendances_get_bug_url_html($t_attendance_bug_id);
echo '<br/>';

if ($check_in) {
    html_operation_confirmation( [ [ attendances_check_out_link(), "<i class='fa fa-sign-out'></i> " . plugin_lang_get('check_out') ] ], plugin_lang_get('checked_in') . ": " . $check_in, CONFIRMATION_TYPE_SUCCESS );
} else {
    html_operation_confirmation( [ [ attendances_check_in_link(), "<i class='fa fa-sign-in'></i> " . plugin_lang_get('check_in') ] ], plugin_lang_get('checked_out'), CONFIRMATION_TYPE_FAILURE );
}
layout_page_end();
