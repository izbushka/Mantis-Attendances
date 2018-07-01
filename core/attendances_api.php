<?php

function attendances_get_current_bug_id($p_create = true) {
    $bug_id = null;
    $t_attendances_bugs_table = plugin_table('attendances_bugs', 'Attendances');
    $v_cur_month = date('Y-m-01');
    $current_user = auth_get_current_user_id();
    $t_query = "
        SELECT `bug_id`
        FROM $t_attendances_bugs_table
        WHERE
            `month` = '$v_cur_month' AND user_id=" . db_param();
    $t_result = db_query($t_query, array($current_user));

    if (db_num_rows($t_result) < 1) {
        if ($p_create) {
            $bug_id = attendances_create_bug($v_cur_month);
        }
    } else {
        $t_row = db_fetch_array($t_result);
        $bug_id = $t_row['bug_id'];
    }
    return $bug_id;
}

function attendances_create_bug($p_month) {
    $current_user = auth_get_current_user_id();
    $t_bug_data = new BugData;
    $t_bug_data->project_id = plugin_config_get('report_project_id');
    $t_bug_data->category_id = plugin_config_get('report_category_id');
    $t_bug_data->reporter_id = $current_user;
    $t_bug_data->handler_id = $current_user;
    $t_bug_data->status = ASSIGNED;
    $t_bug_data->summary = plugin_config_get('default_summary') . ' '
        . user_get_realname($current_user)
        . ', ' . date('F (m/Y)', strtotime($p_month));
    $t_bug_data->description = plugin_config_get('default_description');

    # Allow plugins to pre-process bug data
    $t_bug_data = event_signal('EVENT_REPORT_BUG_DATA', $t_bug_data);

    # Create the bug
    $t_bug_id = $t_bug_data->create();
    $t_bug_data->process_mentions();

    if ($t_bug_id) {
        $t_update_table = plugin_table('attendances_bugs', 'Attendances');
        $t_query = "
            INSERT INTO $t_update_table (`user_id`, `bug_id`, `month`, `created_at`)
            VALUES (" . db_param() . ", " . db_param() . ", " . db_param() . ", NOW())
        ";
        db_query($t_query, [$current_user, $t_bug_id, $p_month]);
    }
    return $t_bug_id;

}
function attendances_get_bug_url_html($bug_id) {
    return '<a href="' . string_get_bug_view_url($bug_id) . '">#' . $bug_id . '</a>';
}

function attendances_check_in_link($html = false) {
    $v_url = plugin_page('attendances_page') . '&action=checkin';
    return $html ? '<a href="' . $v_url . '">' . plugin_lang_get('check_in') . '</a>' : $v_url;
}

function attendances_check_out_link($html = false) {
    $v_url = plugin_page('attendances_page') . '&action=checkout';
    return $html ? '<a href="' . $v_url . '">' . plugin_lang_get('check_out') . '</a>' : $v_url;
}

function attendances_update_last_seen() {
    $t_update_table = plugin_table('attendances', 'Attendances');
    $current_user = auth_get_current_user_id();
    $t_query = "
        UPDATE $t_update_table SET `last_seen` = NOW(), `updated_at` = NOW()
        WHERE ISNULL(`check_out`) AND `user_id` = '$current_user'
    ";
    db_query($t_query);
}

function attendances_check_in() {
    $t_update_table = plugin_table('attendances', 'Attendances');
    $current_user = auth_get_current_user_id();
    $t_bug_id = attendances_get_current_bug_id();
    $t_query = "
        INSERT INTO $t_update_table 
            (`user_id`, `bug_id`, `check_in`, `last_seen`, `created_at`, `updated_at`)
        VALUES (" . db_param() . ", " . db_param() . ", NOW(), NOW(), NOW(), NOW())
    ";
    db_query($t_query, [$current_user, $t_bug_id]);
    bugnote_add($t_bug_id, 'Check in', '0:00', false, BUGNOTE, '', $current_user, false);
}

function attendances_check_out($auto = false) {
    $auto = $auto ? 1 : 0;
    $t_update_table = plugin_table('attendances', 'Attendances');
    $current_user = auth_get_current_user_id();
    $t_bug_id = attendances_get_current_bug_id();
    $last_seen = $auto ? '' : '`last_seen` = NOW(),';
    $t_query = "
        UPDATE $t_update_table 
        SET `check_out` = NOW(), `updated_at` = NOW(), $last_seen `auto_checkout` = '$auto'
        WHERE ISNULL(`check_out`) AND `user_id` = '$current_user'
    ";
    db_query($t_query);
    bugnote_add($t_bug_id, 'Check out' . ($auto ? ' (timeout)' : ''), '0:00', false, BUGNOTE, '', $current_user, false);
}

function attendances_get_auto_check_out() {
    $t_checkin_table = plugin_table('attendances', 'Attendances');
    $timeout = (new DateTime('2 minutes ago'))->format('Y-m-d H:i:s');

    $t_query = "
        SELECT `user_id`, `bug_id` FROM $t_checkin_table 
        WHERE ISNULL(`check_out`) AND `last_seen` < '$timeout' 
    ";
    $t_result = db_query($t_query);

    $result = [];
    while ($row = db_fetch_array($t_result)) {
        $result[] = $row;
    }
    return $result;
}

function attendances_get_check_in_status() {
    $bug_id = null;
    $t_attendances_table = plugin_table('attendances', 'Attendances');
    $current_user = auth_get_current_user_id();
    $t_query = "
        SELECT `check_in`
        FROM $t_attendances_table
        WHERE ISNULL(`check_out`) AND `user_id` = '$current_user'
    ";
    $t_result = db_query($t_query);

    if (db_num_rows($t_result) < 1) {
        return null;
    } else {
        $t_row = db_fetch_array($t_result);
        return $t_row['check_in'];
    }
}
