<?php
# MantisBT - a php based bugtracking system
# Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

form_security_validate('plugin_Attendances_config_edit');

auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));

$f_report_project_id = gpc_get_int('report_project_id', plugin_config_get('report_project_id'));
$f_report_category_id = gpc_get_int('report_category_id', plugin_config_get('report_category_id'));
$f_default_summary = gpc_get_string('default_summary', plugin_config_get('default_summary'));
$f_default_description = gpc_get_string('default_description', plugin_config_get('default_description'));

foreach (['report_project_id', 'report_category_id', 'default_summary', 'default_description'] as $var) {
    $f_var = "f_$var";
    if (plugin_config_get($var) != $$f_var)
        plugin_config_set($var, $$f_var);
}

form_security_purge('plugin_Attendances_config_edit');

print_successful_redirect(plugin_page('config', true));
