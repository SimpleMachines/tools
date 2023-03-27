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

global $hooks, $mod_name, $has_db_changes;

/**
 * @hooks: this variable is an array of hooks that will be installed by this script
 * Please use the notation:
 *	'integration_hook_name' => 'function',
 */
$hooks = array(
);
/**
 * @mod_name: Here you can write the name of your mod, this will be used in the page title and in the welcome message
 */
$mod_name = 'Mod\'s name';
/**
 * @has_db_changes: true/false, this variable is used to define if the mod requires db changes,
 * if so it will expect a file called 'install_db.php' in the same directory as this file (i.e. the forum root)
 * see install_db.php for how this file must be set up
 */
$has_db_changes = true;

// -------------------------- DON'T CHANGE ANYTHING BELOW THIS LINE -------------------------------------------------------------------------------------------
define('SMF_INTEGRATION_SETTINGS', serialize(array(
	'integrate_menu_buttons' => 'install_menu_button',)));
define('MOD_INSTALL', true);

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

if (isset($_GET['delete']))
	doDelete();

if (SMF == 'SSI')
{
	// Let's start the main job
	install_mod();
	// and then let's throw out the template! :P
	obExit(null, null, true);
}
else
{
	setup_hooks();
}

function install_mod()
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
	$context['has_db_changes'] = false;
	$context['apply_db_changes'] = false;
	$context['warning_missing_db_changes'] = false;

	if ($has_db_changes)
	{
		$context['has_db_changes'] = true;
		if (file_exists(dirname(__FILE__) . '/install_db.php'))
		{
			require_once(dirname(__FILE__) . '/install_db.php');
			$context['apply_db_changes'] = true;
		}
		else
			$context['warning_missing_db_changes'] = true;
	}
	else
		$context['has_db_changes'];

	// Sorry, only logged in admins...
	isAllowedTo('admin_forum');
	loadLanguage('install');

	if (isset($context['uninstalling']))
	{
		setup_hooks();
		if ($context['apply_db_changes'])
			$context['db_changes'] = setup_dbchanges($context['uninstalling']);
	}
}

function setup_hooks()
{
	global $context, $hooks;

	$integration_function = empty($context['uninstalling']) ? 'add_integration_function' : 'remove_integration_function';
	foreach ($hooks as $hook => $function)
		$integration_function($hook, $function);

	$context['installation_done'] = true;
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
	global $boardurl, $context, $txt;

	echo '
	<div class="tborder login">
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
		echo '<strong>Database adaptation successful!</strong><br />
		<div style="margin-top: 1ex; font-weight: bold;">
			<label for="delete_self"><input type="checkbox" id="delete_self" onclick="doTheDelete();" class="input_check" /> ', $txt['delete_installer'], !isset($_SESSION['installer_temp_ftp']) ? ' ' . $txt['delete_installer_maybe'] : '', '</label>
		</div>
		<div>' . $context['db_changes'] . '</div>
		<script type="text/javascript"><!-- // --><![CDATA[
			function doTheDelete()
			{
				var theCheck = document.getElementById ? document.getElementById("delete_self") : document.all.delete_self;
				var tempImage = new Image();

				tempImage.src = "', $boardurl, '/install.php?delete=1&ts_" + (new Date().getTime());
				tempImage.width = 0;
				theCheck.disabled = true;
			}
		// ]]></script>';

	echo '
		</div>
		<span class="lowerframe"><span></span></span>
	</div>';
}

function doDelete()
{
	@unlink(__FILE__);
	@unlink(dirname(__FILE__) . '/install_db.php');

	// Now just redirect to a blank.gif...
	header('Location: http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']) . dirname($_SERVER['PHP_SELF']) . '/Themes/default/images/blank.gif');
	exit;
}
?>