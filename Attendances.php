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
 * Attendances plugin
 * @package    MantisPlugin
 * @subpackage MantisPlugin
 * @link       http://www.mantisbt.org
 */

/**
 * requires MantisPlugin.class.php
 */
define('FILTER_PROPERTY_DEPARTMENT', 'department_id');
define('FILTER_WORKERS_ONLY', 'workers_only');
require_once(config_get('class_path') . 'MantisPlugin.class.php');

/**
 * Motives Class
 */
class AttendancesPlugin extends MantisPlugin {
    const BASE_NAME = 'Attendances';

    /**
     *  A method that populates the plugin information and minimum requirements.
     */
    function register() {
        $this->name = plugin_lang_get('title');
        $this->description = plugin_lang_get('description');
        $this->page = 'config';

        $this->version = '1.0';
        $this->requires = array('MantisCore' => '2.0.0',);

        $this->author = 'Oleg Muraviov';
        $this->contact = 'mirage@izbushka.kiev.ua';
        $this->url = 'https://github.com/izbushka/Mantis-Attendances.git';
    }

    /**
     * Default plugin configuration.
     */
    function hooks() {
        $hooks = array('EVENT_MENU_MAIN'           => 'menu',
                       'EVENT_LAYOUT_RESOURCES'    => 'resources',
                       'EVENT_VIEW_BUG_DETAILS'    => 'update_last_seen',
        );

        return $hooks;
    }

    /**
     * Show appropriate forms for updating time spent.
     * @param string $p_event Event name
     * @param int $p_bug_id Bug ID
     */
    function add_note_form($p_event, $p_bug_id) {
        if (!access_has_bug_level(plugin_config_get('update_threshold'), $p_bug_id)) {
            return;
        }
        echo '<tr ', helper_alternate_class(), '><td class="category">', plugin_lang_get('bonuses_fines'),
            '</td><td><select id="plugin_motives_user" name="plugin_motives_user"><option value="' . META_FILTER_ANY . '">[' . plugin_lang_get('none') . ']</option>';

        print_note_option_list(NO_USER, bug_get_field($p_bug_id, 'project_id'));
        echo '</select> ',
        plugin_lang_get('amount'), '<input name="plugin_motives_amount" pattern="^(-)?[0-9]+$" title="', plugin_lang_get('error_numbers'), '" value="0" /></td></tr>';
    }

    /**
     * Plugin schema.
     */
    function schema() {
        return array(
            array('CreateTableSQL', array(plugin_table('attendances'), "
				user_id			I		NOTNULL UNSIGNED,
				bug_id          I       NOTNULL UNSIGNED,
				check_in		T		NOTNULL,
				check_out		T		DEFAULT NULL,
				last_seen		T		NOTNULL,
				auto_checkout   I       NOTNULL DEFAULT 0,
				created_at		T		NOTNULL DEFAULT 0,
				updated_at		T		NOTNULL DEFAULT 0
				")),
            array( 'CreateIndexSQL', array( 'user_id_idx', plugin_table('attendances'), 'user_id')),
            array( 'CreateIndexSQL', array( 'check_out_idx', plugin_table('attendances'), 'check_out')),
            array('CreateTableSQL', array(plugin_table('attendances_bugs'), "
				user_id			I		NOTNULL UNSIGNED,
				bug_id          I       NOTNULL UNSIGNED,
				month           T       NOTNULL,
				created_at		T		NOTNULL
				")),
            array( 'CreateIndexSQL', array( 'user_id_idx', plugin_table('attendances_bugs'), 'user_id')),
        );
    }

    function menu() {
        if (!access_has_global_level(plugin_config_get('view_report_threshold'))) {
            return array();
        }

        $links = array();
        $links[] = array(
            'title' => plugin_lang_get('menu'),
            'url'   => plugin_page('attendances_page'),
            'icon'  => 'fa-bell',
        );
        return $links;
    }

    function init() {
        $t_path = config_get_global('plugin_path') . plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR;
        set_include_path(get_include_path() . PATH_SEPARATOR . $t_path);
        require_once('attendances_api.php');
    }

    function config() {
        return array(
            'report_project_id'   => '',
            'report_category_id'  => '',
            'default_summary'     => 'Attendance',
            'default_description' => 'Attendance',
        );
    }

    /**
     * Create the resource link
     */
    function resources($p_event) {
        return '<link rel="stylesheet" type="text/css" href="' . plugin_file('attendances.css') . '"/>' .
        '<script src="' . plugin_file('attendances.js') . '"></script>';
    }

    function update_last_seen() {
        attendances_update_last_seen();
    }
}
