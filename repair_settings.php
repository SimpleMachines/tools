<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0
 */

// We need the Settings.php info for database stuff.
if (file_exists(dirname(__FILE__) . '/Settings.php'))
	require_once(dirname(__FILE__) . '/Settings.php');

// Initialize everything and load the language files.
initialize_inputs();

$txt['smf_repair_settings'] = 'SMF 2.0 Settings Repair Tool';
$txt['smf11_repair_settings'] = 'SMF 1.x Settings Repair Tool';
$txt['no_value'] = '<em style="font-weight: normal; color: red;">Value not found!</em>';
$txt['default_value'] = 'Recommended value';
$txt['other_possible_value'] = 'Other possible value';
$txt['no_default_value'] = 'No recommended value';
$txt['save_settings'] = 'Save Settings';
$txt['remove_hooks'] = 'Remove all hooks';
$txt['restore_all_settings'] = 'Restore all settings';
$txt['not_writable'] = 'Settings.php cannot be written to by your webserver.  Please modify the permissions on this file to allow write access.';
$txt['recommend_blank'] = '<em>(blank)</em>';
$txt['database_settings_hidden'] = 'Some settings are not being shown because the database connection information is incorrect.';

$txt['critical_settings'] = 'Critical Settings';
$txt['critical_settings_info'] = 'These are the settings most likely to be screwing up your board, but try the things below (especially the path and URL ones) if these don\'t help.  You can click on the recommended value to use it.';
$txt['maintenance'] = 'Maintenance Mode';
$txt['maintenance0'] = 'Off (recommended)';
$txt['maintenance1'] = 'Enabled';
$txt['maintenance2'] = 'Unusable <em>(not recommended!)</em>';
$txt['language'] = 'Language File';
$txt['cookiename'] = 'Cookie Name';
$txt['queryless_urls'] = 'Queryless URLs';
$txt['queryless_urls0'] = 'Off (recommended)';
$txt['queryless_urls1'] = 'On';
$txt['enableCompressedOutput'] = 'Output Compression';
$txt['enableCompressedOutput0'] = 'Off (recommended if you have problems)';
$txt['enableCompressedOutput1'] = 'On (saves a lot of bandwidth)';
$txt['databaseSession_enable'] = 'Database driven sessions';
$txt['databaseSession_enable0'] = 'Off (not recommended)';
$txt['databaseSession_enable1'] = 'On (recommended)';
$txt['theme_default'] = 'Set SMF Default theme as overall forum default<br />for all users';
$txt['theme_default0'] = 'No (keep the current users\' theme settings)';
$txt['theme_default1'] = 'Yes (recommended if you have problems)';

$txt['database_settings'] = 'Database Info';
$txt['database_settings_info'] = 'This is the server, username, password, and database for your server.';
$txt['db_server'] = 'Server';
$txt['db_name'] = 'Database name';
$txt['db_user'] = 'Username';
$txt['db_passwd'] = 'Password';
$txt['ssi_db_user'] = 'SSI Username';
$txt['ssi_db_passwd'] = 'SSI Password';
$txt['ssi_db_user_desc'] = '(Optional)';
$txt['ssi_db_passwd_desc'] = '(Optional)';
$txt['db_prefix'] = 'Table prefix';
$txt['db_persist'] = 'Connection type';
$txt['db_persist0'] = 'Standard (recommended)';
$txt['db_persist1'] = 'Persistent (might cause problems)';
$txt['db_mysql'] = 'MySQL';
$txt['db_postgresql'] = 'PostgreSQL';
$txt['db_sqlite'] = 'SQLite';

$txt['path_url_settings'] = 'Paths &amp; URLs';
$txt['path_url_settings_info'] = 'These are the paths and URLs to your SMF installation, and can cause big problems when they are wrong.  Sorry, there are a lot of them.';
$txt['boardurl'] = 'Forum URL';
$txt['boarddir'] = 'Forum Directory';
$txt['sourcedir'] = 'Sources Directory';
$txt['cachedir'] = 'Cache Directory';
$txt['attachmentUploadDir'] = 'Attachment Directory';
$txt['avatar_url'] = 'Avatar URL';
$txt['avatar_directory'] = 'Avatar Directory';
$txt['smileys_url'] = 'Smileys URL';
$txt['smileys_dir'] = 'Smileys Directory';
$txt['theme_url'] = 'Default Theme URL';
$txt['images_url'] = 'Default Theme Images URL';
$txt['theme_dir'] = 'Default Theme Directory';

$txt['theme_path_url_settings'] = 'Paths &amp; URLs For Themes';
$txt['theme_path_url_settings_info'] = 'These are the paths and URLs to your SMF themes.';

// Fix Database title to use $db_type if available
if (!empty($db_type) && isset($txt['db_' . $db_type]))
	$txt['database_settings'] = $txt['db_' . $db_type] . ' ' . $txt['database_settings'];

if (isset($_POST['submit']))
	set_settings();
if (isset($_POST['remove_hooks']))
	remove_hooks();

// try to find the smflogo: could be a .gif or a .png
$smflogo = "Themes/default/images/smflogo.png";
if (!file_exists(dirname(__FILE__) . "/" . $smflogo))
$smflogo = "Themes/default/images/smflogo.gif";

// Note that we're using the default URLs because we aren't even going to try to use Settings.php's settings.
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="robots" content="noindex" />
		<title>', $context['is_legacy'] ? $txt['smf11_repair_settings'] : $txt['smf_repair_settings'], '</title>
		<script type="text/javascript" src="Themes/default/scripts/script.js"></script>
		<style type="text/css">
			body
			{
				background-color: #e5e5e8;
				margin: 0px;
				padding: 0px;
			}
			body, td
			{
				color: #000000;
				font-size: small;
				font-family: verdana, sans-serif;
			}
			div#header
			{
				background-color: #88a6c0;
				padding: 22px 4% 12px 4%;
				color: white;
				font-family: Georgia, serif;
				font-size: xx-large;
				border-bottom: 1px solid black;
				height: 40px;
			}
			div#content
			{
				padding: 20px 30px;
			}
			div.error_message
			{
				border: 2px dashed red;
				background-color: #e1e1e1;
				margin: 1ex 4ex;
				padding: 1.5ex;
			}
			div.panel
			{
				border: 1px solid gray;
				background-color: #f6f6f6;
				margin: 1ex 0;
				padding: 1.2ex;
			}
			div.panel h2
			{
				margin: 0;
				margin-bottom: 0.5ex;
				padding-bottom: 3px;
				border-bottom: 1px dashed black;
				font-size: 14pt;
				font-weight: normal;
			}
			div.panel h3
			{
				margin: 0;
				margin-bottom: 2ex;
				font-size: 10pt;
				font-weight: normal;
			}
			form
			{
				margin: 0;
			}
			td.textbox
			{
				padding-top: 2px;
				font-weight: bold;
				white-space: nowrap;
				padding-', empty($txt['lang_rtl']) ? 'right' : 'left', ': 2ex;
			}
			.smalltext
			{
				font-size: 0.8em;
				font-weight: normal;
			}
			.centertext
			{
				margin: 0 auto;
				text-align: center;
			}
			.righttext
			{
				margin-left: auto;
				margin-right: 0;
				text-align: right;
			}
			.lefttext
			{
				margin-left: 0;
				margin-right: auto;
				text-align: left;
			}
			.changed td
			{
				color: red;
			}
		</style>
	</head>
	<body>
		<div id="header">
			<a href="http://www.simplemachines.org/" target="_blank"><img src="' . $smflogo . '" style="width: 250px; float: right;" alt="Simple Machines" border="0" /></a>
			<div>', $context['is_legacy'] ? $txt['smf11_repair_settings'] : $txt['smf_repair_settings'], '</div>
		</div>
		<div id="content">';

show_settings();

echo '
		</div>
	</body>
</html>';

function initialize_inputs()
{
	global $smcFunc, $db_connection, $sourcedir, $db_server, $db_name, $db_user, $db_passwd, $db_prefix, $db_type, $context;

	// Turn off magic quotes runtime and enable error reporting.
	@set_magic_quotes_runtime(0);
	error_reporting(E_ALL);
	if (ini_get('session.save_handler') == 'user')
		ini_set('session.save_handler', 'files');
	@session_start();


	// Slashes as soo old-fashion...
	if (!function_exists('get_magic_quotes_gpc') || @get_magic_quotes_gpc() == 0)
	{
		foreach ($_POST as $k => $v)

			if (is_array($v))
				foreach ($v as $k2 => $v2)
					$_POST[$k][$k2] = addslashes($v2);
			else
				$_POST[$k] = addslashes($v);
	}

	foreach ($_POST as $k => $v)
	{
		if (is_array($v))
			foreach ($v as $k2 => $v2)
				$_POST[$k][$k2] = addcslashes($v2, '\'');
		else
			$_POST[$k] = addcslashes($v, '\'');
	}

	// This is really quite simple; if ?delete is on the URL, delete the installer...
	if (isset($_GET['delete']))
	{
		@unlink(__FILE__);

		// Now just redirect to a blank.gif...
		header('Location: http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']) . dirname($_SERVER['PHP_SELF']) . '/Themes/default/images/blank.gif');
		exit;
	}

	$db_connection = false;
	if (isset($sourcedir))
	{
		define('SMF', 1);

		if (empty($smcFunc))
			$smcFunc = array();

		// Default the database type to MySQL.
		if (empty($db_type) || !file_exists($sourcedir . '/Subs-Db-' . $db_type . '.php'))
			$db_type = 'mysql';

		require_once($sourcedir . '/Load.php');
		require_once($sourcedir . '/Subs-Auth.php');

		// compat mode. Active!
		$context['is_legacy'] = true;
		if (!file_exists($sourcedir . '/Subs-Db-' . $db_type . '.php') && $db_type == 'mysql')
		{
			$db_connection = smc_compat_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true));
		}
		else
		{
			// Far as I know, this is 2.0.
			$context['is_legacy'] = false;
			require_once($sourcedir . '/Subs-Db-' . $db_type . '.php');
			require_once($sourcedir . '/DbExtra-' . $db_type . '.php');
			$db_connection = smf_db_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true));
			db_extra_init();
		}
	}
}

function show_settings()
{
	global $txt, $smcFunc, $db_connection, $db_type, $db_name, $db_prefix, $context;

	// Check to make sure Settings.php exists!
	if (file_exists(dirname(__FILE__) . '/Settings.php'))
		$settingsArray = file(dirname(__FILE__) . '/Settings.php');
	else
		$settingsArray = array();

	if (count($settingsArray) == 1)
		$settingsArray = preg_split('~[\r\n]~', $settingsArray[0]);

	$settings = array();
	for ($i = 0, $n = count($settingsArray); $i < $n; $i++)
	{
		$settingsArray[$i] = rtrim(stripslashes($settingsArray[$i]));

		if (isset($settingsArray[$i][0]) && $settingsArray[$i][0] != '.')
		{
			preg_match('~^[$]([a-zA-Z_]+)\s*=\s*(?:(["\'])(?:(.*?)["\'])(?:\\2)?|(.*?)(?:\\2)?);~', $settingsArray[$i], $match);
			if (isset($match[3]))
			{
				if ($match[3] == 'dirname(__FILE__)')
					$settings[$match[1]] = dirname(__FILE__);
				elseif ($match[3] == 'dirname(__FILE__) . \'/Sources\'')
					$settings[$match[1]] = dirname(__FILE__) . '/Sources';
				elseif ($match[3] == '$boarddir . \'/Sources\'')
					$settings[$match[1]] = $settings['boarddir'] . '/Sources';
				elseif ($match[3] == 'dirname(__FILE__) . \'/cache\'')
					$settings[$match[1]] = dirname(__FILE__) . '/cache';
				else
					$settings[$match[1]] = $match[3];
			}
		}
	}

	if ($db_connection == true)
	{
		$request = $smcFunc['db_query'](true, '
			SELECT DISTINCT variable, value
			FROM {db_prefix}settings',
			array(
				'db_error_skip' => true
			),
			$db_connection
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$settings[$row['variable']] = $row['value'];
		$smcFunc['db_free_result']($request);

		// Load all the themes.
		$request = $smcFunc['db_query'](true, '
			SELECT variable, value, id_theme
			FROM {db_prefix}themes
			WHERE id_member = 0
				AND variable IN ({array_string:variables})',
			array(
				'variables' => array('theme_dir', 'theme_url', 'images_url', 'name'),
				'db_error_skip' => true
			)
		);

		$theme_settings = array();
		while ($row = $smcFunc['db_fetch_row']($request))
			$theme_settings[$row[2]][$row[0]] = $row[1];
		$smcFunc['db_free_result']($request);

		$show_db_settings = $request;
	}
	else
		$show_db_settings = false;

	$known_settings = array(
		'critical_settings' => array(
			'maintenance' => array('flat', 'int', 2),
			'language' => array('flat', 'string', 'english'),
			'cookiename' => array('flat', 'string', 'SMFCookie' . (!empty($db_name) ? abs(crc32($db_name . preg_replace('~[^A-Za-z0-9_$]~', '', $db_prefix)) % 1000) : '20')),
			'queryless_urls' => array('db', 'int', 1),
			'enableCompressedOutput' => array('db', 'int', 1),
			'databaseSession_enable' => array('db', 'int', 1),
			'theme_default' => array('db', 'int', 1),
		),
		'database_settings' => array(
			'db_server' => array('flat', 'string', 'localhost'),
			'db_name' => array('flat', 'string'),
			'db_user' => array($db_type == 'sqlite' ? 'hidden' : 'flat', 'string'),
			'db_passwd' => array($db_type == 'sqlite' ? 'hidden' : 'flat', 'string'),
			'ssi_db_user' => array($db_type == 'sqlite' ? 'hidden' : 'flat', 'string'),
			'ssi_db_passwd' => array($db_type == 'sqlite' ? 'hidden' : 'flat', 'string'),
			'db_prefix' => array('flat', 'string'),
			'db_persist' => array('flat', 'int', 1),
		),
		'path_url_settings' => array(
			'boardurl' => array('flat', 'string'),
			'boarddir' => array('flat', 'string'),
			'sourcedir' => array('flat', 'string'),
			'cachedir' => array('flat', 'string'),
			'attachmentUploadDir' => array('db', 'array_string'),
			'avatar_url' => array('db', 'string'),
			'avatar_directory' => array('db', 'string'),
			'smileys_url' => array('db', 'string'),
			'smileys_dir' => array('db', 'string'),
		),
		'theme_path_url_settings' => array(),
	);

	// 1.x didn't have ssi_x, nor cachedir
	if ($context['is_legacy'])
	{
// 		if (empty($known_settings['database_settings']['ssi_db_user']))
			unset($known_settings['database_settings']['ssi_db_user']);
// 		if (empty($known_settings['database_settings']['ssi_db_passwd']))
			unset($known_settings['database_settings']['ssi_db_passwd']);
// 		if (empty($known_settings['path_url_settings']['cachedir']))
			unset($known_settings['path_url_settings']['cachedir']);
	}
	else
	{
		// !!! Multiple Attachment Dirs not supported as yet, so hide this field
// 		if (empty($known_settings['path_url_settings']['attachmentUploadDir']))
// 			unset($known_settings['path_url_settings']['attachmentUploadDir']);
	}

	// Let's assume we don't want to change the current theme
	$settings['theme_default'] = 0;

	$host = empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST'];
	$url = 'http://' . $host . substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
	$known_settings['path_url_settings']['boardurl'][2] = $url;
	$known_settings['path_url_settings']['boarddir'][2] = dirname(__FILE__);

	if (file_exists(dirname(__FILE__) . '/Sources'))
		$known_settings['path_url_settings']['sourcedir'][2] = realpath(dirname(__FILE__) . '/Sources');

	if (file_exists(dirname(__FILE__) . '/cache'))
		$known_settings['path_url_settings']['cachedir'][2] = realpath(dirname(__FILE__) . '/cache');

	if (file_exists(dirname(__FILE__) . '/avatars'))
	{
		$known_settings['path_url_settings']['avatar_url'][2] = $url . '/avatars';
		$known_settings['path_url_settings']['avatar_directory'][2] = realpath(dirname(__FILE__) . '/avatars');
	}

	if (file_exists(dirname(__FILE__) . '/Smileys'))
	{
		$known_settings['path_url_settings']['smileys_url'][2] = $url . '/Smileys';
		$known_settings['path_url_settings']['smileys_dir'][2] = realpath(dirname(__FILE__) . '/Smileys');
	}

/*	if (file_exists(dirname(__FILE__) . '/Themes/default'))
	{
		$known_settings['path_url_settings']['theme_url'][2] = $url . '/Themes/default';
		$known_settings['path_url_settings']['images_url'][2] = $url . '/Themes/default/images';
		$known_settings['path_url_settings']['theme_dir'][2] = realpath(dirname(__FILE__) . '/Themes/default');
	}
*/

	if (!empty($theme_settings))
	{
		// Create the values for the themes.
		foreach ($theme_settings as $id => $theme)
		{
			$this_theme = ($pos = strpos($theme['theme_url'], '/Themes/')) !== false ? substr($theme['theme_url'], $pos+8) : '';
			if (!empty($this_theme))
				$exist = file_exists(dirname(__FILE__) . '/Themes/' . $this_theme);
			else
				$exist = false;

			$known_settings['theme_path_url_settings'] += array(
				'theme_'. $id.'_theme_url'=>array('theme', 'string', $exist && !empty($this_theme) ? $url . '/Themes/' . $this_theme : null),
				'theme_'. $id.'_images_url'=>array('theme', 'string', $exist && !empty($this_theme) ? $url . '/Themes/' . $this_theme . '/images' : null),
				'theme_' . $id . '_theme_dir' => array('theme', 'string', $exist && !empty($this_theme) ? realpath(dirname(__FILE__) . '/Themes/' . $this_theme) : null),
			);
			$settings += array(
				'theme_' . $id . '_theme_url' => $theme['theme_url'],
				'theme_' . $id . '_images_url' => $theme['images_url'],
				'theme_' . $id . '_theme_dir' => $theme['theme_dir'],
			);

			$txt['theme_' . $id . '_theme_url'] = $theme['name'] . ' URL';
			$txt['theme_' . $id . '_images_url'] = $theme['name'] . ' Images URL';
			$txt['theme_' . $id . '_theme_dir'] = $theme['name'] . ' Directory';
		}
	}

	if ($db_connection == true)
	{
		$request = $smcFunc['db_list_tables']('', '
			{db_prefix}log_topics',
			array(
				'db_error_skip' => true,
			)
		);
		if ($request == true)
		{
			if ($smcFunc['db_num_rows']($request) == 1)
				list ($known_settings['database_settings']['db_prefix'][2]) = preg_replace('~log_topics$~', '', $smcFunc['db_fetch_row']($request));
			$smcFunc['db_free_result']($request);
		}
	}
	elseif (empty($show_db_settings))
	{
		echo '
			<div class="error_message" style="margin-bottom: 2ex;">
				', $txt['database_settings_hidden'], '
			</div>';
	}

	echo '
			<script type="text/javascript"><!-- // --><![CDATA[
				// Get the inner HTML of an element.
				var resetSettings = new Array();
				var settingsCounter = 0;
				function getInnerHTML(element)
				{
					return element.innerHTML;
				}

				function restoreAll()
				{
					for (var i=0;i<resetSettings.length;i++)
					{
						var elem = document.getElementById(resetSettings[i]);
						var val = elem.value;
						elem.value = getInnerHTML(document.getElementById(resetSettings[i] + \'_default\'));
						if (val != elem.value)
						elem.parentNode.parentNode.className += " changed";
					}
				}
			// ]]></script>

			<form action="', $_SERVER['PHP_SELF'], '" method="post">
				<div class="panel">';

	foreach ($known_settings as $settings_section => $section)
	{
		echo '
					<h2>', $txt[$settings_section], '</h2>
					<h3>', $txt[$settings_section . '_info'], '</h3>

					<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 3ex;">
						<tr>';

		foreach ($section as $setting => $info)
		{
			if ($info[0] == 'hidden')
				continue;

			if ($info[0] != 'flat' && empty($show_db_settings))
				continue;

			echo '
							<td width="20%" valign="top" class="textbox" style="padding-bottom: 1ex;">
								<label', $info[1] != 'int' ? ' for="' . $setting . '"' : '', '>', $txt[$setting], ': '.
									( isset($txt[$setting . '_desc']) ? '<span class="smalltext">' . $txt[$setting . '_desc'] . '</span>' : '' ).'
								</label>', !isset($settings[$setting]) && $info[1] != 'check' ? '<br />
								' . $txt['no_value'] : '', '
							</td>
							<td style="padding-bottom: 1ex;">';

			if ($info[1] == 'int' || $info[1] == 'check')
			{
				for ($i = 0; $i <= $info[2]; $i++)
					echo '
								<label for="', $setting, $i, '"><input type="radio" name="', $info[0], 'settings[', $setting, ']" id="', $setting, $i, '" value="', $i, '"', isset($settings[$setting]) && $settings[$setting] == $i ? ' checked="checked"' : '', ' class="input_radio" /> ', $txt[$setting . $i], '</label><br />';
			}
			elseif ($info[1] == 'string')
			{
				echo '
								<input type="text" name="', $info[0], 'settings[', $setting, ']" id="', $setting, '" value="', isset($settings[$setting]) ? htmlspecialchars($settings[$setting]) : '', '" size="', $settings_section == 'path_url_settings' || $settings_section == 'theme_path_url_settings' ? '60" style="width: 80%;' : '30', '" class="input_text" />';

				if (isset($info[2]))
					echo '
								<div style="font-size: smaller;">', $txt['default_value'], ': &quot;<strong><a href="javascript:void(0);" id="', $setting, '_default" onclick="document.getElementById(\'', $setting, '\').value = ', $info[2] == '' ? '\'\';">' . $txt['recommend_blank'] : 'getInnerHTML(this);">' . $info[2], '</a></strong>&quot;.</div>',
								$info[2] == '' ? '' : ($setting != 'language' && $setting != 'cookiename' ? '
								<script type="text/javascript"><!-- // --><![CDATA[
									resetSettings[settingsCounter++] = "' . $setting . '"; // ]]></script>' : '');
			}
			elseif ($info[1] == 'array_string')
			{
				if (!is_array($settings[$setting]))
					$array_settings = @unserialize($settings[$setting]);
				if (!is_array($array_settings))
					$array_settings = array($settings[$setting]);

				$item = 1;
				foreach ($array_settings as $array_setting)
				{
					$suggested = false;
					echo '
								<input type="text" name="', $info[0], 'settings[', $setting, '_', $item, ']" id="', $setting, $item, '" value="', $array_setting, '" size="', $settings_section == 'path_url_settings' || $settings_section == 'theme_path_url_settings' ? '60" style="width: 80%;' : '30', '" class="input_text" />';

					$suggested = guess_attachments_directories($item, $array_setting);

					if (!empty($suggested))
					{
						echo '
								<div style="font-size: smaller;">', $txt['default_value'], ': &quot;<strong><a href="javascript:void(0);" id="', $setting, $item, '_default" onclick="document.getElementById(\'', $setting, $item, '\').value = ', $suggested[0] == '' ? '\'\';">' . $txt['recommend_blank'] : 'getInnerHTML(this);">' . $suggested[0], '</a></strong>&quot;.</div>',
								$suggested[0] == '' ? '' : '
								<script type="text/javascript"><!-- // --><![CDATA[
									resetSettings[settingsCounter++] = "' . $setting . $item . '"; // ]]></script>';

						for ($i = 1; $i < count($suggested); $i++)
							echo '
								<div style="font-size: smaller;">', $txt['other_possible_value'], ': &quot;<strong><a href="javascript:void(0);" id="', $setting, $item, '_default" onclick="document.getElementById(\'', $setting, $item, '\').value = ', $suggested[$i] == '' ? '\'\';">' . $txt['recommend_blank'] : 'getInnerHTML(this);">' . $suggested[$i], '</a></strong>&quot;.</div>';
					}
					else
						echo '
								<div style="font-size: smaller;">', $txt['no_default_value'], '</div>';

					$item++;
				}
			}

			echo '
							</td>
						</tr><tr>';
		}

		echo '
							<td colspan="2"></td>
						</tr>
					</table>';
	}

	echo '

					<div class="righttext" style="margin: 1ex;">';

	$failure = false;
	if (strpos(__FILE__, ':\\') !== 1)
	{
		// On linux, it's easy - just use is_writable!
		$failure |= !is_writable('Settings.php') && !chmod('Settings.php', 0777);
	}
	// Windows is trickier.  Let's try opening for r+...
	else
	{
		// Funny enough, chmod actually does do something on windows - it removes the read only attribute.
		chmod(dirname(__FILE__) . '/' . 'Settings.php', 0777);
		$fp = @fopen(dirname(__FILE__) . '/' . 'Settings.php', 'r+');

		// Hmm, okay, try just for write in that case...
		if (!$fp)
			$fp = @fopen(dirname(__FILE__) . '/' . 'Settings.php', 'w');

		$failure |= !$fp;
		fclose($fp);
	}

	if ($failure)
		echo '
				<input type="submit" name="submit" value="', $txt['save_settings'], '" disabled="disabled" class="button_submit" /><br />', $txt['not_writable'];
	else
		echo '
				[<a href="javascript:restoreAll();">', $txt['restore_all_settings'], '</a>]
				<input type="submit" name="submit" value="', $txt['save_settings'], '" class="button_submit" />', $context['is_legacy'] ? '' : '
				<input type="submit" name="remove_hooks" value="' . $txt['remove_hooks'] . '" class="button_submit" />';

	echo '
				</div>
				</div>
			</form>';
}

function guess_attachments_directories($id, $array_setting)
{
	global $smcFunc, $context;
	static $usedDirs;

	if (empty($userdDirs))
	{
		$usedDirs = array();
		$request = $smcFunc['db_query'](true, '
			SELECT {raw:select_tables}, file_hash
			FROM {db_prefix}attachments
			{raw:smf1_limit}',
			array(
				'select_tables' => $context['is_legacy'] ? ($id . ' as id_folder, ID_ATTACH as id_attach') : 'DISTINCT(id_folder), id_attach',
			// If it's SMF 1.x then check only the last attachment loaded
				'smf1_limit' => $context['is_legacy'] ? ' ORDER BY ID_ATTACH DESC LIMIT 1' : '',
			)
		);

		if ($smcFunc['db_num_rows']($request) > 0)
		{
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$usedDirs[$row['id_folder']] = $row;
			$smcFunc['db_free_result']($request);
		}
	}

	if ($basedir = opendir(dirname(__FILE__)))
	{
		$availableDirs = array();
		while (false !== ($file = readdir($basedir)))
			if ($file != '.' && $file != '..' && is_dir($file) && $file != 'Sources'  && $file != 'Packages' && $file != 'Themes' && $file != 'cache' && $file != 'avatars' && $file != 'Smileys')
				$availableDirs[] = $file;
	}

	// 1st guess: let's see if we can find a file...if there is at least one.
	if (isset($usedDirs[$id]) || ($context['is_legacy'] && isset($usedDirs[$id])))
		foreach ($availableDirs as $aDir)
			if (file_exists(dirname(__FILE__) . '/' . $aDir . '/' . $usedDirs[$id]['id_attach'] . '_' . $usedDirs[$id]['file_hash']))
				return array(dirname(__FILE__) . '/' . $aDir);

	// 2nd guess: directory name
	if (!empty($availableDirs))
		foreach ($availableDirs as $dirname)
			if (strrpos($array_setting, $dirname) == (strlen($array_setting) - strlen($dirname)))
				return array(dirname(__FILE__) . '/' . $dirname);

	// Doing it later saves in case the attached files have been deleted from the file system
	if (empty($usedDirs) && empty($availableDirs))
		return false;
	elseif (empty($usedDirs) && !empty($availableDirs))
	{
		$guesses = array();
		// attachments is the first guess
		foreach ($availableDirs as $dir)
			if ($dir == 'attachments')
				$guesses[] = dirname(__FILE__) . '/' . $dir;

		// all the others
		foreach ($availableDirs as $dir)
			if ($dir != 'attachments')
				$guesses[] = dirname(__FILE__) . '/' . $dir;
		return $guesses;
	}
}

function set_settings()
{
	global $smcFunc, $context;

	$db_updates = isset($_POST['dbsettings']) ? $_POST['dbsettings'] : array();
	$theme_updates = isset($_POST['themesettings']) ? $_POST['themesettings'] : array();
	$file_updates = isset($_POST['flatsettings']) ? $_POST['flatsettings'] : array();
	$attach_dirs = array();

	if (empty($db_updates['theme_default']))
		unset($db_updates['theme_default']);
	else
	{
		$db_updates['theme_guests'] = 1;
		$smcFunc['db_query'](true, '
			UPDATE {db_prefix}members
			SET {raw:theme_column} = 0',
			array(
				'theme_column' => $context['is_legacy'] ? 'ID_THEME' : 'id_theme',
			)
		);
	}

	$settingsArray = file(dirname(__FILE__) . '/Settings.php');
	$settings = array();
	for ($i = 0, $n = count($settingsArray); $i < $n; $i++)
	{
		$settingsArray[$i] = rtrim($settingsArray[$i]);

		// Remove the redirect...
		if ($settingsArray[$i] == 'if (file_exists(dirname(__FILE__) . \'/install.php\'))')
		{
			$settingsArray[$i] = '';
			$settingsArray[$i++] = '';
			$settingsArray[$i++] = '';
			$settingsArray[$i++] = '';
			$settingsArray[$i++] = '';
			$settingsArray[$i++] = '';
			continue;
		}

		if (isset($settingsArray[$i][0]) && $settingsArray[$i][0] != '.' && preg_match('~^[$]([a-zA-Z_]+)\s*=\s*(?:(["\'])(.*?["\'])(?:\\2)?|(.*?)(?:\\2)?);~', $settingsArray[$i], $match) == 1)
			$settings[$match[1]] = stripslashes($match[3]);

		foreach ($file_updates as $var => $val)
		{
			if (strncasecmp($settingsArray[$i], '$' . $var, 1 + strlen($var)) == 0)
			{
				$comment = strstr($settingsArray[$i], '#');
				$settingsArray[$i] = '$' . $var . ' = \'' . $val . '\';' . ($comment != '' ? "\t\t" . $comment : '');
			}
		}
	}

	// Blank out the file - done to fix a oddity with some servers.
	$fp = @fopen(dirname(__FILE__) . '/Settings.php', 'w');
	fclose($fp);

	$fp = fopen(dirname(__FILE__) . '/Settings.php', 'r+');
	$lines = count($settingsArray);
	for ($i = 0; $i < $lines - 1; $i++)
	{
		// Don't just write a bunch of blank lines.
		if ($settingsArray[$i] != '' || $settingsArray[$i - 1] != '')
			fwrite($fp, $settingsArray[$i] . "\n");
	}
	fwrite($fp, $settingsArray[$i]);
	fclose($fp);

	// Make sure it works.
	require(dirname(__FILE__) . '/Settings.php');

	$setString = array();
	foreach ($db_updates as $var => $val)
		$setString[] = array($var, stripslashes($val));

	// Attachments dirs
	if ($context['is_legacy'])
	{
		foreach ($setString as $key => $value)
			if (strpos($value[0], 'attachmentUploadDir') == 0 && strpos($value[0], 'attachmentUploadDir') !== false)
			{
				$attach_dirs[0] = $value[1];
				unset($setString[$key]);
			}
	}
	else
	{
		$attach_count = 1;
		foreach ($setString as $key => $value)
			if (strpos($value[0], 'attachmentUploadDir') == 0 && strpos($value[0], 'attachmentUploadDir') !== false)
			{
				$attach_dirs[$attach_count++] = $value[1];
				unset($setString[$key]);
			}
	}

	// Only one dir...or maybe nothing at all
	if (count($attach_dirs) > 1)
	{
		$setString[] = array('attachmentUploadDir', @serialize($attach_dirs));
		// If we want to (re)set currentAttachmentUploadDir here is a good place
// 		foreach ($attach_dirs as $id => $attach_dir)
// 			if (is_dir($attach_dir) && is_writable($attach_dir))
// 				$setString[] = array('currentAttachmentUploadDir', $id + 1);
	}
	elseif (!$context['is_legacy'] && isset($attach_dirs[1]))
	{
		$setString[] = array('attachmentUploadDir', $attach_dirs[1]);
		$setString[] = array('currentAttachmentUploadDir', 0);
	}
	elseif ($context['is_legacy'] && isset($attach_dirs[0]))
	{
		$setString[] = array('attachmentUploadDir', $attach_dirs[0]);
	}
	else
	{
		$setString[] = array('attachmentUploadDir', '');
		$setString[] = array('currentAttachmentUploadDir', 0);
	}

	if (!empty($setString))
		$smcFunc['db_insert']('replace',
			'{db_prefix}settings',
			array('variable' => 'string', 'value' => 'string-65534'),
			$setString,
			array('variable')
		);

	$setString = array();
	foreach ($theme_updates as $var => $val)
	{
		// Extract the data
		preg_match('~theme_([\d]+)_(.+)~', $var, $match);
		if (empty($match[0]))
			continue;

		$setString[] = array($match[1], 0, $match[2], stripslashes($val));
	}

	if (!empty($setString))
		$smcFunc['db_insert']('replace',
			'{db_prefix}themes',
			array('id_theme' => 'int', 'id_member' => 'int', 'variable' => 'string', 'value' => 'string-65534'),
			$setString,
			array('id_theme', 'id_member', 'variable')
		);
}

function remove_hooks()
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}settings
		WHERE variable LIKE {string:variable}',
		array(
			'variable' => 'integrate_%'
		)
	);

	// Now fixing the cache...
	cache_put_data('modsettings', null, 0);
}

// Compat mode!
function smc_compat_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, $db_options = array())
{
	global $mysql_set_mod, $sourcedir, $db_connection, $db_prefix, $smcFunc;

	if (!empty($db_options['persist']))
		$db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);
	else
		$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);

	// Something's wrong, show an error if its fatal (which we assume it is)
	if (!$db_connection)
	{
		if (!empty($db_options['non_fatal']))
			return null;
		else
		{
			if (file_exists($sourcedir . '/Errors.php'))
			{
				require_once($sourcedir . '/Errors.php');
				display_db_error();
			}
			exit('Sorry, SMF was unable to connect to database.');
		}
	}

	// Select the database, unless told not to
	if (empty($db_options['dont_select_db']) && !@mysql_select_db($db_name, $connection) && empty($db_options['non_fatal']))
	{
		if (file_exists($sourcedir . '/Errors.php'))
		{
			require_once($sourcedir . '/Errors.php');
			display_db_error();
		}
		exit('Sorry, SMF was unable to connect to database.');
	}
	else
		$db_prefix = is_numeric(substr($db_prefix, 0, 1)) ? $db_name . '.' . $db_prefix : '`' . $db_name . '`.' . $db_prefix;

	// Some core functions.
	function smf_db_replacement__callback($matches)
	{
		global $db_callback, $user_info, $db_prefix;

		list ($values, $connection) = $db_callback;

		if ($matches[1] === 'db_prefix')
			return $db_prefix;

		if ($matches[1] === 'query_see_board')
			return $user_info['query_see_board'];

		if ($matches[1] === 'query_wanna_see_board')
			return $user_info['query_wanna_see_board'];

		if (!isset($matches[2]))
			smf_db_error_backtrace('Invalid value inserted or no type specified.', '', E_USER_ERROR, __FILE__, __LINE__);

		if (!isset($values[$matches[2]]))
			smf_db_error_backtrace('The database value you\'re trying to insert does not exist: ' . htmlspecialchars($matches[2]), '', E_USER_ERROR, __FILE__, __LINE__);

		$replacement = $values[$matches[2]];

		switch ($matches[1])
		{
			case 'int':
				if (!is_numeric($replacement) || (string) $replacement !== (string) (int) $replacement)
					smf_db_error_backtrace('Wrong value type sent to the database. Integer expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
				return (string) (int) $replacement;
			break;

			case 'string':
			case 'text':
				return sprintf('\'%1$s\'', mysql_real_escape_string($replacement, $connection));
			break;

			case 'array_int':
				if (is_array($replacement))
				{
					if (empty($replacement))
						smf_db_error_backtrace('Database error, given array of integer values is empty. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

					foreach ($replacement as $key => $value)
					{
						if (!is_numeric($value) || (string) $value !== (string) (int) $value)
							smf_db_error_backtrace('Wrong value type sent to the database. Array of integers expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

						$replacement[$key] = (string) (int) $value;
					}

					return implode(', ', $replacement);
				}
				else
					smf_db_error_backtrace('Wrong value type sent to the database. Array of integers expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

			break;

			case 'array_string':
				if (is_array($replacement))
				{
					if (empty($replacement))
						smf_db_error_backtrace('Database error, given array of string values is empty. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

					foreach ($replacement as $key => $value)
						$replacement[$key] = sprintf('\'%1$s\'', mysql_real_escape_string($value, $connection));

					return implode(', ', $replacement);
				}
				else
					smf_db_error_backtrace('Wrong value type sent to the database. Array of strings expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
			break;

			case 'date':
				if (preg_match('~^(\d{4})-([0-1]?\d)-([0-3]?\d)$~', $replacement, $date_matches) === 1)
					return sprintf('\'%04d-%02d-%02d\'', $date_matches[1], $date_matches[2], $date_matches[3]);
				else
					smf_db_error_backtrace('Wrong value type sent to the database. Date expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
			break;

			case 'float':
				if (!is_numeric($replacement))
					smf_db_error_backtrace('Wrong value type sent to the database. Floating point number expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
				return (string) (float) $replacement;
			break;

			case 'identifier':
				// Backticks inside identifiers are supported as of MySQL 4.1. We don't need them for SMF.
				return '`' . strtr($replacement, array('`' => '', '.' => '')) . '`';
			break;

			case 'raw':
				return $replacement;
			break;

			default:
				smf_db_error_backtrace('Undefined type used in the database query. (' . $matches[1] . ':' . $matches[2] . ')', '', false, __FILE__, __LINE__);
			break;
		}
	}

	// Because this is just compat mode, this is good enough.
	function smf_db_query($execute = true, $db_string, $db_values)
	{
		global $db_callback, $db_connection;

		// Only bother if there's something to replace.
		if (strpos($db_string, '{') !== false)
		{
			// This is needed by the callback function.
			$db_callback = array($db_values, $db_connection);

			// Do the quoting and escaping
			$db_string = preg_replace_callback('~{([a-z_]+)(?::([a-zA-Z0-9_-]+))?}~', 'smf_db_replacement__callback', $db_string);

			// Clear this global variable.
			$db_callback = array();
		}

		// We actually make the query in compat mode.
		if ($execute === false)
			return $db_string;
		return mysql_query($db_string, $db_connection);
	}

	// Insert some data...
	function smf_db_insert($method = 'replace', $table, $columns, $data, $keys, $disable_trans = false)
	{
		global $smcFunc, $db_connection, $db_prefix;

		// With nothing to insert, simply return.
		if (empty($data))
			return;

		// Replace the prefix holder with the actual prefix.
		$table = str_replace('{db_prefix}', $db_prefix, $table);

		// Inserting data as a single row can be done as a single array.
		if (!is_array($data[array_rand($data)]))
			$data = array($data);

		// Create the mold for a single row insert.
		$insertData = '(';
		foreach ($columns as $columnName => $type)
		{
			// Are we restricting the length?
			if (strpos($type, 'string-') !== false)
				$insertData .= sprintf('SUBSTRING({string:%1$s}, 1, ' . substr($type, 7) . '), ', $columnName);
			else
				$insertData .= sprintf('{%1$s:%2$s}, ', $type, $columnName);
		}
		$insertData = substr($insertData, 0, -2) . ')';

		// Create an array consisting of only the columns.
		$indexed_columns = array_keys($columns);

		// Here's where the variables are injected to the query.
		$insertRows = array();
		foreach ($data as $dataRow)
			$insertRows[] = smf_db_query(false, $insertData, array_combine($indexed_columns, $dataRow));

		// Determine the method of insertion.
		$queryTitle = ($method == 'replace') ? 'REPLACE' : (($method == 'ignore') ? 'INSERT IGNORE' : 'INSERT');

		// Do the insert.
		$smcFunc['db_query'](true, '
			' . $queryTitle . ' INTO ' . $table . '(`' . implode('`, `', $indexed_columns) . '`)
			VALUES
				' . implode(',
				', $insertRows),
			array(
				'security_override' => true,
			)
		);
	}

	// This function tries to work out additional error information from a back trace.
	function smf_db_error_backtrace($error_message, $log_message = '', $error_type = false, $file = null, $line = null)
	{
		if (empty($log_message))
			$log_message = $error_message;

		// A special case - we want the file and line numbers for debugging.
		if ($error_type == 'return')
			return array($file, $line);

		// Is always a critical error.
		if (function_exists('log_error'))
			log_error($log_message, 'critical', $file, $line);

		if ($error_type)
			trigger_error($error_message . ($line !== null ? '<em>(' . basename($file) . '-' . $line . ')</em>' : ''), $error_type);
		else
			trigger_error($error_message . ($line !== null ? '<em>(' . basename($file) . '-' . $line . ')</em>' : ''));
	}

	// Returns all tables
	function smf_db_list_tables($db = false, $filter = false)
	{
		global $db_name, $smcFunc;

		$db = $db == false ? $db_name : $db;
		$db = trim($db);
		$filter = $filter == false ? '' : ' LIKE \'' . $filter . '\'';

		$request = $smcFunc['db_query'](true, '
			SHOW TABLES
			FROM `{raw:db}`
			{raw:filter}',
			array(
				'db' => $db[0] == '`' ? strtr($db, array('`' => '')) : $db,
				'filter' => $filter,
			)
		);
		$tables = array();
		while ($row = $smcFunc['db_fetch_row']($request))
			$tables[] = $row[0];
		$smcFunc['db_free_result']($request);

		return $tables;
	}

	// This function tries to work out additional error information from a back trace.
	// Now, go functions, spread your love.
	$smcFunc['db_free_result'] = 'mysql_free_result';
	$smcFunc['db_fetch_row'] = 'mysql_fetch_row';
	$smcFunc['db_fetch_assoc'] = 'mysql_fetch_assoc';
	$smcFunc['db_num_rows'] = 'mysql_num_rows';
	$smcFunc['db_insert'] = 'smf_db_insert';
	$smcFunc['db_query'] = 'smf_db_query';
	$smcFunc['db_quote'] = 'smf_db_query';
	$smcFunc['db_error_backtrace'] = 'smf_db_error_backtrace';
	$smcFunc['db_list_tables'] = 'smf_db_list_tables';

	return $db_connection;
}