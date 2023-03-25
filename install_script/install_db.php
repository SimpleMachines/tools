<?php
/**
 * Install Script (IS)
 *
 * @package IS
 * @author emanuele
 * @copyright 2012 emanuele, Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 0.1.0
 */

global $db_changes, $mod_name, $has_db_changes;

/**
 * @db_changes: this variable contains all the changes to the db.
 * The structure is as easier as possible (at least I hope).
 * The idea behind is: 'name_of_the_db_function' => array(elements like used with $smcFunx['name_of_the_db_function']);
 * For example adding two columns could be:
$db_changes['db_add_column'][] = array (
			'{db_prefix}topics',
			array(
			      'name' => 'new_column_name',
			      'type' => 'mediumint',
			      'default' => '0'
			),
			array(),
			'ignore'
		);
$db_changes['db_add_column'][] = array (
			'{db_prefix}boards',
			array(
			      'name' => 'new_board_column_name',
			      'type' => 'varchar',
						'size' => 255,
			      'default' => ''
			),
			array(),
			'ignore'
		);
 * Instead create a new table would look like:
	@todo put here an example of a table
 */
$db_changes = array(
);


// -------------------------- DON'T CHANGE ANYTHING BELOW THIS LINE -------------------------------------------------------------------------------------------
define('SMF_INTEGRATION_SETTINGS', serialize(array(
	'integrate_menu_buttons' => 'install_menu_button',)));

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

if (SMF == 'SSI')
{
	if (!defined('MOD_INSTALL'))
	{
		// Let's start the main job
		install_db_mod();
		// and then let's throw out the template! :P
		obExit(null, null, true);
	}
}
else
{
	setup_dbchanges();
}

function install_db_mod()
{
	global $context, $mod_name, $has_db_changes;

	$context['mod_name'] = $mod_name;
	$context['sub_template'] = 'install_script';
	$context['page_title_html_safe'] = 'Install script of the mod: ' . $mod_name;
	if (isset($_GET['action']))
		$context['uninstalling'] = $_GET['action'] == 'uninstall' ? true : false;
	$context['html_headers'] .= '
	<style type="text/css">
    .buttonlist ul {
      margin:0 auto;
			display:table;
		}
	</style>';

	if ($has_db_changes)
	{
		$context['has_db_changes'] = true;
		if (file_exists(dirname(__FILE__) . '/install_db.php'))
			$context['apply_db_changes'] = true;
		else
			$context['warning_missing_db_changes'] = true;
	}
	else
		$context['has_db_changes'];

	// Sorry, only logged in admins...
	isAllowedTo('admin_forum');

	if (isset($context['uninstalling']))
		setup_dbchanges();
}

function setup_dbchanges($uninstall = false, $apply = true)
{
	global $db_changes, $smcFunc, $db_prefix;

	$revertable_functions = array(
		'db_add_column' => ($uninstall ? 'db_remove_column' : 'db_add_column'),
		'db_add_index' => ($uninstall ? 'db_remove_index' : 'db_add_index'),
		'db_create_table' => ($uninstall ? 'db_drop_table' : 'db_create_table')
	);
	// sorry, no data validation. What is here *must* be correct, it's your fault if it isn't.
	foreach ($db_changes as $function => $actions)
		foreach ($actions as $action)
		{
			if (in_array($function, $revertable_functions))
				$smcFunc[$revertable_functions[$function]]($action);
			else
				$smcFunc[$function]($action);
		}

	$context['installation_db_done'] = true;

	$changes = array();
	foreach ($db_changes as $function => $actions)
	{
		foreach ($actions as $action)
		{
			switch ($function)
			{
				case 'db_add_column':
					$changes[$function][$action[0]][] = array('name' => $action[1]['name'], 'type' => $action[1]['type']);
					break;
				case 'db_create_table':
					$data = array();
					$data['name'] = $action[0];
					foreach ($action[1] as $row)
						$data['columns'][] = array('name' => $row['name'], 'type' => $row['type']);

					$changes[$function][] = $data;
					break;
				case 'db_change_column':
					$changes[$function][] = array('table' => $action[0], 'old_column' => $action[1]);
					break;
			}
		}
	}

	$db_columns_changes = '';
	$db_tables_changes = '';
	$db_non_tracked_changes = '';
	foreach ($changes as $function => $actions)
	{
		switch ($function)
		{
			case 'db_add_column':
				foreach ($actions as $table => $new_columns)
				{
					$db_columns_changes .= '<br />In the table: ' . str_replace('{db_prefix}', $db_prefix, $table) . ' the following column/s has/ve been ' . ($uninstall ? 'removed' : 'created') . ':<br /><ul>';
					foreach ($new_columns as $new_column)
						$db_columns_changes .= '<li>Name: ' . $new_column['name'] . ' (type ' . $new_column['type'] . ')</li>';
					$db_columns_changes .= '</ul>';
				}
				break;
			case 'db_create_table':
				foreach ($actions as $table)
				{
					$db_tables_changes .= '<br />The table' . str_replace('{db_prefix}', $db_prefix, $table['name']) . ' has been ' . ($uninstall ? 'removed' : 'created') . 'with the following columns:<br />';
					foreach ($table['columns'] as $column)
						$db_tables_changes .= '<li>Name: ' . $column['name'] . ' (type ' . $column['type'] . ')</li>';
					$db_tables_changes .= '</ul>';
				}
				break;
			case 'db_change_column':
				foreach ($actions as $old_column)
				{
					$db_non_tracked_changes .= '<li>The column ' . $old_column['old_column'] . ' of the table ' . str_replace('{db_prefix}', $db_prefix, $old_column['table']) . ' has been changed during the install.</li>';
				}
				break;
		}
	}
	return $db_columns_changes . (!empty($db_tables_changes) ? '<br />' . $db_tables_changes : '') . (!empty($db_non_tracked_changes) ? '<br /><br />Please note the following changes will not be reverted during the uninstall:<ul>' . $db_non_tracked_changes . '</ul>' : '');
}

function install_menu_button(&$buttons)
{
	global $boardurl, $context;

	$context['sub_template'] = 'install_script';
	$context['current_action'] = 'install';

	$buttons['install'] = array(
		'title' => 'Installation script',
		'show' => allowedTo('admin_forum'),
		'href' => $boardurl . '/install.php',
		'active_button' => true,
		'sub_buttons' => array(
		),
	);
}

function template_install_script()
{
	global $boardurl, $context;

	echo '
	<div class="tborder login"">
		<div class="cat_bar">
			<h3 class="catbg">
				Welcome to the install script of the mod: ' . $context['mod_name'] . '
			</h3>
		</div>
		<span class="upperframe"><span></span></span>
		<div class="roundframe centertext">';
	if (!isset($context['installation_done']))
		echo '
			<strong>Please select the action you want to perform:</strong>
			<div class="buttonlist">
				<ul>
					<li>
						<a class="active" href="' . $boardurl . '/install.php?action=install">
							<span>Install</span>
						</a>
					</li>
					<li>
						<a class="active" href="' . $boardurl . '/install.php?action=uninstall">
							<span>Uninstall</span>
						</a>
					</li>
				</ul>
			</div>';
	else
		echo '<strong>Database adaptation successful!</strong>';

	echo '
		</div>
		<span class="lowerframe"><span></span></span>
	</div>';
}
?>