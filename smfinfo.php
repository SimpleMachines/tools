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

// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

// Hmm... no SSI.php and no SMF?
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot start - please verify you put this in the same place as SMF\'s SSI.php.');

$smfinfo_version = '1.0';

initialize();

function load_txt_strings()
{
	global $txt, $boardurl;

	// Tabs
	$txt['title'] = 'SMF Info Support Tool';
	$txt['maininfo'] = 'System Info';
	$txt['phpinfo'] = 'PHP Info';
	$txt['detailedinfo'] = 'Detailed File Check';
	$txt['detailedinfo_db'] = 'Detailed DB Check';
	$txt['mods_installed'] = 'Mods Installed';
	$txt['error_log'] = 'Error Log';
	$txt['status'] = 'System Status';

	// Password form
	$txt['password_title'] = 'Password Entry';
	$txt['password'] = 'Password:';
	$txt['submit'] = 'Submit';

	// Main info
	$txt['smfinfo_pass'] = 'Below is the password to access this file.  Please share it wisely as this page contains a lot of information about your forum and host.<br /><br />Password: %s <a href="' . $boardurl . '/smfinfo.php?regenerate">(Regenerate)</a><br /><br /><a href="' . $boardurl . '/smfinfo.php?delete">Delete File</a> (This attempts to remove this file from your server)';
	$txt['smf_version'] = 'SMF Version';
	$txt['php_version'] = 'PHP Version';
	$txt['database_version'] = 'Database Version';
	$txt['webserver_version'] = 'Web Server';
	$txt['php_api'] = 'PHP/Server Interface';
	$txt['lang_char_set'] = 'Language Character Set';
	$txt['db_char_set'] = 'Database Character Set';
	$txt['db_table_info'] = 'Detailed Table Information';


	// SMF Specific Info
	$txt['smf_relevant'] = 'Relevant SMF Settings';
	$txt['sef_urls'] = 'SEF URLs';
	$txt['time_load'] = 'Display Load Times';
	$txt['hostname_lookup'] = 'Disable Hostname Lookups';
	$txt['log_pruning'] = 'Auto Log Pruning (2.0+)';
	$txt['db_persist'] = 'Persistent DB Connection';
	$txt['maintenance_mode'] = 'Maintenance Mode';
	$txt['cookie_name'] = 'Cookie Name';
	$txt['local_cookies'] = 'Local Cookie Storage';
	$txt['global_cookies'] = 'Subdomain Ind. Cookies';
	$txt['compressed_output'] = 'Compressed Output';
	$txt['database_sessions'] = 'Database Driven Sessions';
	$txt['database_loose'] = 'Return to cached pages';
	$txt['session_timeout'] = 'Session Timeout Delay';
	$txt['db_last_error'] = 'Last Database Error';
	$txt['db_debug'] = 'Debugging';
	$txt['enable_error'] = 'Enable Error Logging';
	$txt['auto_fix_db'] = 'Auto Fix Database';
	$txt['cache'] = 'Caching';
	$txt['memcached_settings'] = 'Memcached Settings';
	$txt['cache_level'] = 'Level';
	$txt['unknown_db_version'] = 'Database Character Set Unknown';
	$txt['support_versions_current'] = 'Current SMF Info version';
	$txt['support_versions_forum'] = 'Your SMF Info version';
	$txt['previousCharacterSet'] = 'Previous character set';


	// PHP Specific Info
	$txt['relevant_info'] = 'Relevant PHP Settings';
	$txt['safe_mode'] = 'Safe Mode';
	$txt['open_base'] = 'Open basedir';
	$txt['display_errors'] = 'Display Errors';
	$txt['file_uploads'] = 'File Uploads';
	$txt['magic_quotes'] = 'Magic Quotes';
	$txt['register_globals'] = 'Register Globals';
	$txt['output_buffering'] = 'Output Buffering';
	$txt['session_save'] = 'Session Save Path';
	$txt['session_auto'] = 'Session Auto Start';
	$txt['xml_enabled'] = 'XML Enabled';
	$txt['zlib_enabled'] = 'Zlib Enabled';
	$txt['disabled_func'] = 'Disabled Functions';

	// File check
	$txt['sources_version'] = 'Sources';
	$txt['template_version'] = 'Default Templates';
	$txt['language_version'] = 'Language Files';
	$txt['custom_template_version'] = 'Custom Templates';
	$txt['file_version'] = 'SMF File';
	$txt['your_version'] = 'Your Version';
	$txt['current_version'] = 'Current Version';

	// Database check
	$txt['no_detailed_db'] = 'Detailed database information is only available on MySQL databases';
	$txt['db_size'] = 'Database Size';
	$txt['db_table_name'] = 'Name';
	$txt['db_table_engine'] = 'Engine';
	$txt['db_table_rows'] = 'Rows';
	$txt['db_table_size'] = 'Size';
	$txt['db_table_max_size'] = 'Max Size';
	$txt['db_table_overhead'] = 'Overhead';
	$txt['db_table_auto'] = 'Next Auto';
	$txt['db_table_collation'] = 'Collation';
	$txt['db_column_name'] = 'Field Name';
	$txt['db_column_type'] = 'Type';
	$txt['db_column_null'] = 'Null';
	$txt['db_column_default'] = 'Default Value';
	$txt['db_column_extra'] = 'Extra Info';

	// Mods installed
	$txt['package_name'] = 'Package Name';
	$txt['package_id'] = 'Package Id';
	$txt['package_version'] = 'Package Version';

	// Error log
	$txt['error_log_count'] = 'Number of Errors';
	$txt['show_all_errors'] = 'Showing all errors';
	$txt['show_num_errors'] = 'Showing 100 of %d errors';
	$txt['error_time'] = 'Time of Error';
	$txt['error_member'] = 'Member ID that caused error';
	$txt['error_url'] = 'URL that caused error';
	$txt['error_message'] = 'Error Message';
	$txt['error_type'] = 'Error Type';
	$txt['error_file'] = 'File';
	$txt['error_line'] = 'Line';

	// Simple Text strings
	$txt['none'] = 'NONE';
	$txt['on'] = 'ON';
	$txt['off'] = 'OFF';
	$txt['empty'] = 'EMPTY';
	$txt['seconds'] = 'seconds';
	$txt['na'] = 'n/a';
	$txt['recommended'] = 'Recommended Value';
}

load_txt_strings();

// Setup the information
// The keys on these entries need to match
// what is in the $txt keys/brackets above
// Makes for easy adding of extra info, or deleting
$context['smfinfo'] = array (
	'db_last_error' => !empty($db_last_error) ? date(DATE_RFC822, $db_last_error) : $txt['none'],
	'auto_fix_db' => get_smf_setting('autoFixDatabase', 'on'),
	'db_persist' => get_smf_setting('db_persist', 'off'),
	'db_debug' => get_smf_setting('db_show_debug', 'off'),
	'enable_error' => get_smf_setting('enableErrorLogging', 'on'),
	'database_sessions' => get_smf_setting('databaseSession_enable'),
	'database_loose' => get_smf_setting('databaseSession_loose'),
	'session_timeout' => !empty($modSettings['databaseSession_lifetime']) ? $modSettings['databaseSession_lifetime'] . ' ' . $txt['seconds'] : '<i>' . $txt['empty'] . '</i>&nbsp;<strong>(' . $txt['recommended'] . ': >300)</strong>',
	'maintenance_mode' => get_smf_setting('maintenance'),
	'time_load' => get_smf_setting('timeLoadPageEnable'),
	'hostname_lookup' => get_smf_setting('disableHostnameLookup'),
	'cache' => (!empty($modSettings['cache_enable']) ? $txt['cache_level'] . ' ' . $modSettings['cache_enable'] : $txt['off']) . ($modSettings['cache_enable'] != '1' ? '&nbsp;<strong>(' . $txt['recommended'] . ': ' . $txt['cache_level'] . ' 1)</strong>' : ''),
	'memcached_settings' => isset($modSettings['cache_memcached']) && trim($modSettings['cache_memcached']) != '' ? trim($modSettings['cache_memcached']) : '<i>' . $txt['empty'] . '</i>',
	'cookie_name' => !empty($cookiename) ? $cookiename : '<i>' . $txt['empty'] . '</i>&nbsp;<strong>(' . $txt['recommended'] . ': SMFCookie' . rand(100,999) . ')</strong>',
	'local_cookies' => get_smf_setting('localCookies', 'off'),
	'global_cookies' => get_smf_setting('globalCookies'),
	'log_pruning' => get_smf_setting('pruningOptions', 'on'),
	'sef_urls' => get_smf_setting('queryless_urls'),
	'compressed_output' => get_smf_setting('enableCompressedOutput'),
	'previousCharacterSet' => get_smf_setting('previousCharacterSet'),
);

$context['phpinfo'] = array (
	'safe_mode' => get_php_setting('safe_mode', 'off'),
	'open_base' => ($ob = ini_get('open_basedir')) ? $ob : $txt['none'],
	'display_errors' => get_php_setting('display_errors', 'off'),
	'file_uploads' => get_php_setting('file_uploads', 'on'),
	'magic_quotes' => get_php_setting('magic_quotes_gpc', 'off'),
	'register_globals' => get_php_setting('register_globals', 'off'),
	'output_buffering' => get_php_setting('output_handler'),
	'session_save' => ($path = ini_get('session.save_path')) ? $path : $txt['none'],
	'session_auto' => (int) ini_get('session.auto_start'),
	'xml_enabled' => extension_loaded('xml') ? $txt['on'] : $txt['off'],
	'zlib_enabled' => extension_loaded('zlib') ? $txt['on'] : $txt['off'],
);

show_header();

show_system_info();

show_php_info();

show_detailed_file();

show_detailed_db();

show_mods();

show_error_log();

show_status();

show_footer();

function show_header()
{
	global $txt, $smfInfo, $context, $smfinfo_version;

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>

		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<title>', $txt['title'], '</title>
		<style type="text/css">
			body
			{
				background-color: #E5E5E8;
				margin: 0px;
				padding: 0px;
			}
			body, td
			{
				color: #000000;
				font-size: 11px;
				font-family: verdana, sans-serif;
			}
			div#header
			{
				background-image: url(Themes/default/images/catbg.jpg);
				background-repeat: repeat-x;
				background-color: #88A6C0;
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
			div.panel
			{
				position: relative;
				border: 1px solid gray;
				background-color: #F6F6F6;
				margin: 1ex 0;
				padding: 1.2ex;
				z-index: 1;
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
			pre
			{
				border: 1px dotted #aaaaaa;
				overflow: auto;
			}
			tr.row1
			{
				background: #E5E5E8;
			}
			/* This is for the tabbed layout */
			.dynamic-tab-pane-control .tab-pane{
				position:relative;
			}






























			.dynamic-tab-pane-control .tab-page{
				border:1px solid #919b9c;
				background:#f6f6f6;
				z-index:2;
				position:relative;
				top:-2px;
				font:11px Tahoma;
				color:#333;
				padding:5px;
				width:97%;
				float:left;
			}






			/* This is for phpinfo */
			table.adminlist
			{
				background-color:#FFF;
				margin:0;
				padding:0;
				border:1px solid #CCC;
				border-spacing:0;
				width:100%;
				border-collapse:collapse;
			}

			table.adminlist th
			{
				margin:0;
				padding:6px 4px 2px 4px;
				height:25px;

				background-repeat:repeat;
				font-size:11px;
				color:#fff;
			}

			table.adminlist th.title
			{
				text-align:left;
			}

			table.adminlist th a
			{
				color:#f90;
			}

			table.adminlist tr.row0
			{
				background-color:#F5F5F5;
			}

			table.adminlist tr.row1
			{
				background-color:#FFF;
			}

			table.adminlist td
			{
				border-bottom:1px solid #e5e5e5;
				padding:4px;
			}

			table.adminlist tr.row0:hover
			{
				background-color:#f1f1f1;
			}

			table.adminlist tr.row1:hover
			{
				background-color:#f1f1f1;
			}

			table.adminlist td.options
			{
				background-color:#fff;
				font-size:8px;
			}

		</style>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var sections = new Array();
			var titles = new Array();
			function addSection(name, title)
			{


				var drop = document.getElementById("menuDropdown");
				var option = document.createElement("option");
				sections.push(name);
				titles.push(title);
				option.text = titles[titles.length-1];
				option.value = titles.length-1;
				drop.options.add(option);
			}

			function swapSection(id)
			{



				for (var i = 0; i < sections.length; i++)
				{
					if (i == id)
						document.getElementById(sections[i]).style.display = "";
					else
						document.getElementById(sections[i]).style.display = "none";
				}

			}
			var onload_events = new Array();
			function addLoadEvent(func)
			{



				// Get the old event if there is one.
				var oldOnload = window.onload;

				// Was the old event really an event?
				if (typeof(oldOnload) != \'function\')
				{
					// Since we don\'t have anything at this point just add it stright in.
					window.onload = func;
				}



				// So it is a function but is it our special function?
				else if(onload_events.length == 0)
				{
					// Nope it is just a regular function...
					onload_events[0] = oldOnload;
					onload_events[1] = func;
					window.onload = function() {
						for (var i=0; i < onload_events.length; i++)
						{
							if (onload_events[i])
							{
								onload_events[i]();
							}
						}
					}
				}
				else
					// Ok just add it to the list of functions to call.
					onload_events[onload_events.length] = func;
			}
			addLoadEvent(function() {swapSection(0);});
		// ]]></script>
	</head>
	<body>
		<div id="header">
			<a href="http://www.simplemachines.org/" target="_blank"><img src="./Themes/default/images/smflogo.gif" style="width: 258px; float: right;" alt="Simple Machines" border="0" /></a>
			<div>', $txt['title'], '</div>
		</div>
		<div id="content">';


	if (allowedTo('admin_forum'))
		echo '
		<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
			', sprintf($txt['smfinfo_pass'], $smfInfo), '<br /><br />
			', $txt['support_versions_forum'], ':
			<i id="yourVersion" style="white-space: nowrap;">', $smfinfo_version, '</i><br />
			', $txt['support_versions_current'], ':
			<i id="smfInfoVersion" style="white-space: nowrap;">??</i><br />

		<script language="JavaScript" type="text/javascript" src="http://www.simplemachines.org/smf/current-smfinfo.js"></script>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function smfInfoCurrentVersion()
			{
				var smfVer, yourVer;

				if (typeof(window.smfInfoVersion) != "string")
					return;

				smfVer = document.getElementById("smfInfoVersion");
				yourVer = document.getElementById("yourVersion");

				setInnerHTML(smfVer, window.smfInfoVersion);

				var currentVersion = getInnerHTML(yourVer);
				if (currentVersion != window.smfInfoVersion)
					setInnerHTML(yourVer, "<span style=\"color: red;\">" + currentVersion + "</span>");
			}
			var oldonload;
			if (typeof(window.onload) != "undefined")
				oldonload = window.onload;

			window.onload = function ()
			{
				smfInfoCurrentVersion();';

	if ($context['browser']['is_ie'] && !$context['browser']['is_ie4'])
		echo '
				if (typeof(smf_codeFix) != "undefined")
					window.detachEvent("onload", smf_codeFix);
				window.attachEvent("onload",
					function ()
					{
						with (document.all.supportVersionsTable)
							style.height = parentNode.offsetHeight;
					}
				);
				if (typeof(smf_codeFix) != "undefined")
					window.attachEvent("onload", smf_codeFix);';

	echo '

				if (oldonload)
					oldonload();
			}
		// ]]></script>
		</div>';
	echo '
		<select id="menuDropdown" onchange="swapSection(this[this.selectedIndex].value); return true;">
			<option value="0">-- Menu --</option>
		</select>
			<div class="dynamic-tab-pane-control tab-page" id="smfinfo" style="margin-top: 10px;">';
}

function show_password_form()
{
	global $txt, $boardurl;
	load_txt_strings();
	show_header();

	echo '
			<div class="tab-page" id="main"><h2 class="tab">', $txt['password_title'], '</h2>
				<script type="text/javascript">addSection("main", "', $txt['password_title'], '");</script>
				<form action="', $boardurl, '/smfinfo.php" method="post"
				<table border="0" width="50%" cellpadding="2" cellspacing="2">
					<tr>
						<td>', $txt['password'], '</td><td><input type="text" size="20" name="pass" /></td><td><input type="submit" value="', $txt['submit'], '" /></td>
					</tr>
				</table>
				</form>
			</div>
			</div>
		</div>
	</body>
</html>';

	exit();
}

function show_system_info()
{
	global $txt, $smcFunc, $context, $smfInfo, $modSettings, $forum_version;
	global $db_persist, $maintenance, $cookiename, $db_last_error, $db_show_debug;

	get_database_version();

	echo '




			<div class="tab-page" id="main"><h2 class="tab">', $txt['maininfo'], '</h2>
				<script type="text/javascript">addSection("main", "', $txt['maininfo'], '" );</script>
				<table border="0" width="100%" cellpadding="2" cellspacing="2">
					<tr>
						<td width="25%"><strong>', $txt['smf_version'], '</strong></td>
						<td>', $forum_version, '</td>
					</tr>
					<tr>
						<td width="25%"><strong>', $txt['lang_char_set'], '</strong></td>
						<td>', $txt['lang_character_set'], '</td>
					</tr>
					<tr>
						<td width="25%"><strong>', $txt['db_char_set'], '</strong></td>
						<td>', $context['character_set'], '</td>
					</tr>
					<tr>
						<td valign="top"><strong>', $txt['smf_relevant'], '</strong></td>
						<td>
							<table width="100%" cellpadding="2" cellspacing="2">';

	foreach ($context['smfinfo'] as $item => $value)
		echo '
								<tr>
									<td width="25%">', $txt[$item], ':</td>
									<td>', $value, '</td>
								</tr>';

	echo '
							</table>
						</td>
					</tr>
					<tr>
						<td><strong>', $txt['php_version'], '</strong></td>
						<td>', phpversion(), '</td>
					</tr>
					<tr>
						<td><strong>', $txt['database_version'], '</strong></td>
						<td>', $context['database_version'], '</td>
					</tr>
					<tr>
						<td><strong>', $txt['webserver_version'], '</strong></td>
						<td>', get_server_software(), '</td>
					</tr>
					<tr>
						<td><strong>', $txt['php_api'], '</strong></td>
						<td>', php_sapi_name(), '</td>
					</tr>
					<tr>
						<td valign="top"><strong>', $txt['relevant_info'], '</strong></td>
						<td>
							<table width="100%" cellpadding="2" cellspacing="2">';

	foreach ($context['phpinfo'] as $item => $value)
		echo '
								<tr>
									<td width="25%">', $txt[$item], ':</td>
									<td>', $value, '</td>
								</tr>';

	echo '
							</table>
						</td>
					</tr>';

	$server_checks = array('gd', 'mmcache', 'eaccelerator', 'phpa', 'apc', 'memcache',);
	foreach (get_server_versions($server_checks) as $values)
		echo '
					<tr>
						<td><strong>', $values['title'], '</strong></td>
						<td>', $values['version'], '</td>
					</tr>';

	echo '
				</table>
			</div>';
}

function show_php_info()
{
	global $txt;

	echo '
			<div class="tab-page" id="phpinfo"><h2 class="tab">', $txt['phpinfo'], '</h2>
				<script type="text/javascript">addSection( "phpinfo", "', $txt['phpinfo'], '");</script>';

	// Get the PHP Info
	ob_start();
	phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES);
	$phpinfo = ob_get_contents();
	ob_end_clean();

	// Get the main body of it, then fix it up a bit
	preg_match_all('#<body[^>]*>(.*)</body>#siU', $phpinfo, $output);
	$output = preg_replace('#<table#', '<table class="adminlist" align="center"', $output[1][0]);
	$output = preg_replace('#(\w),(\w)#', '\1, \2', $output);
	$output = preg_replace('#border="0" cellpadding="3" width="600"#', 'border="0" cellspacing="1" cellpadding="4" width="95%"', $output);
	$output = preg_replace('#<hr />#', '', $output);

	echo
		$output, '
			</div>';
}

function show_detailed_file()
{
	global $context, $boardurl, $txt;

	echo '
			<div class="tab-page" id="detailedinfo"><h2 class="tab">', $txt['detailedinfo'], '</h2>
				<script type="text/javascript">addSection("detailedinfo", "', $txt['detailedinfo'], '");</script>';

	get_file_versions();

	// The current version of the core SMF package.
	echo '
					<table width="60%" cellpadding="2" cellspacing="0" border="0" align="center">
						<tr>
							<td width="50%"><b>', $txt['file_version'], '</b></td><td width="25%"><b>', $txt['your_version'], '</b></td><td width="25%"><b>', $txt['current_version'], '</b></td>
						</tr>
						<tr>
							<td>', $txt['smf_version'], '</td><td><i id="yourSMF">SMF ', $context['forum_version'], '</i></td><td><i id="currentSMF">??</i></td>
						</tr>';

	// Now list all the source file versions, starting with the overall version (if all match!).
	echo '
						<tr>
							<td><a href="javascript:void(0);" onclick="return swapOption(this, \'Sources\');">', $txt['sources_version'], '</a></td><td><i id="yourSources">??</i></td><td><i id="currentSources">??</i></td>
						</tr>
					</table>
					<table id="Sources" width="60%" cellpadding="2" cellspacing="0" border="0" align="center">';

	// Loop through every source file displaying its version - using javascript.
	foreach ($context['file_versions'] as $filename => $version)
		echo '
						<tr>
							<td width="50%" style="padding-left: 3ex;">', $filename, '</td><td width="25%"><i id="yourSources', $filename, '">', $version, '</i></td><td width="25%"><i id="currentSources', $filename, '">??</i></td>
						</tr>';

	// Default template files.
	echo '
					</table>
					<table width="60%" cellpadding="2" cellspacing="0" border="0" align="center">
						<tr>
							<td width="50%"><a href="javascript:void(0);" onclick="return swapOption(this, \'Default\');">', $txt['template_version'], '</a></td><td width="25%"><i id="yourDefault">??</i></td><td width="25%"><i id="currentDefault">??</i></td>
						</tr>
					</table>
					<table id="Default" width="60%" cellpadding="2" cellspacing="0" border="0" align="center">';

	foreach ($context['default_template_versions'] as $filename => $version)
		echo '
						<tr>
							<td width="50%" style="padding-left: 3ex;">', $filename, '</td><td width="25%"><i id="yourDefault', $filename, '">', $version, '</i></td><td width="25%"><i id="currentDefault', $filename, '">??</i></td>
						</tr>';

	// Now the language files...
	echo '
					</table>
					<table width="60%" cellpadding="2" cellspacing="0" border="0" align="center">
						<tr>
							<td width="50%"><a href="javascript:void(0);" onclick="return swapOption(this, \'Languages\');">', $txt['language_version'], '</a></td><td width="25%"><i id="yourLanguages">??</i></td><td width="25%"><i id="currentLanguages">??</i></td>
						</tr>
					</table>
					<table id="Languages" width="60%" cellpadding="2" cellspacing="0" border="0" align="center">';

	foreach ($context['default_language_versions'] as $language => $files)
	{
		foreach ($files as $filename => $version)
			echo '
						<tr>
							<td width="50%" style="padding-left: 3ex;">', $filename, '.<i>', $language, '</i>.php</td><td width="25%"><i id="your', $filename, '.', $language, '">', $version, '</i></td><td width="25%"><i id="current', $filename, '.', $language, '">??</i></td>
						</tr>';
	}

	echo '
					</table>';

	// Finally, display the version information for the currently selected theme - if it is not the default one.
	if (!empty($context['template_versions']))
	{
		echo '
					<table width="60%" cellpadding="2" cellspacing="0" border="0" align="center">
						<tr>
							<td width="50%"><a href="javascript:void(0);" onclick="return swapOption(this, \'Templates\');">', $txt['custom_template_version'], '</a></td><td width="25%"><i id="yourTemplates">??</i></td><td width="25%"><i id="currentTemplates">??</i></td>
						</tr>
					</table>
					<table id="Templates" width="60%" cellpadding="2" cellspacing="0" border="0" align="center">';

		foreach ($context['template_versions'] as $filename => $version)
			echo '
						<tr>
							<td width="50%" style="padding-left: 3ex;">', $filename, '</td><td width="25%"><i id="yourTemplates', $filename, '">', $version, '</i></td><td width="25%"><i id="currentTemplates', $filename, '">??</i></td>
						</tr>';

		echo '
					</table>';
	}

	echo '
			</div>';
}

function show_detailed_db()
{
	global $txt, $context, $db_type;

	if (empty($db_type) || (!empty($db_type) && $db_type == 'mysql'))
		get_database_info();

	echo '
			<div class="tab-page" id="detailedinfo_db"><h2 class="tab">', $txt['detailedinfo_db'], '</h2>
				<script type="text/javascript">addSection("detailedinfo_db", "', $txt['detailedinfo_db'], '");</script>';

	echo '
				<table border="0" width="100%" cellpadding="2" cellspacing="2">
					<tr>
						<td width="25%"><strong>', $txt['database_version'], '</strong></td>
						<td>', $context['database_version'], '</td>
					</tr>
					<tr>
						<td><strong>', $txt['db_char_set'], '</strong></td>
						<td>', $context['character_set'], '</td>
					</tr>';
	if (isset($context['database_size']))
		echo '
					<tr>
						<td><strong>', $txt['db_size'], '</strong></td>
						<td>', $context['database_size'], '</td>
					</tr>';

	if (!empty($context['database_tables']))
	{
		echo '
					<tr>
						<td valign="top"><strong>', $txt['db_table_info'], '</strong></td>
						<td>
							<table width="100%" cellpadding="2" cellspacing="2">
								<tr>
									<td><strong>', $txt['db_table_name'], '</strong></td>
									<td><strong>', $txt['db_table_engine'], '</strong></td>
									<td><strong>', $txt['db_table_rows'], '</strong></td>
									<td><strong>', $txt['db_table_size'], '</strong></td>
									<td><strong>', $txt['db_table_overhead'], '</strong></td>
									<td><strong>', $txt['db_table_auto'], '</strong></td>
									<td><strong>', $txt['db_table_collation'], '</strong></td>
								</tr>';

		$table_color = 1;
		foreach($context['database_tables'] as $table)
		{
			echo '
								<tr class="row', $table_color = !$table_color, '">
									<td>', !empty($table['columns']) ? '<a href="javascript:void(0);" onclick="return swapOption(this, \'' . $table['name'] . '\');">' : '', $table['name'], !empty($table['columns']) ? '</a>' : '', '</td>
									<td>', isset($table['engine']) ?  $table['engine'] : $txt['na'], '</td>
									<td>', isset($table['rows']) ?  $table['rows'] : $txt['na'], '</td>
									<td>', isset($table['size']) ?  $table['size'] : $txt['na'], '</td>
									<td>', isset($table['overhead']) ?  $table['overhead'] : $txt['na'], '</td>
									<td>', isset($table['auto_increment']) ?  $table['auto_increment'] : $txt['na'], '</td>
									<td>', isset($table['collation']) ?  $table['collation'] : $txt['na'], '</td>
								</tr>';
			if (!empty($table['columns']))
			{
				echo '
								<tr id="', $table['name'], '">
									<td colspan="7">
										<table width="100%" cellpadding="2" cellspacing="2" style="padding-left: 10px;">
											<tr>
												<td><strong>', $txt['db_column_name'], '</strong></td>
												<td><strong>', $txt['db_column_type'], '</strong></td>
												<td><strong>', $txt['db_table_collation'], '</strong></td>
												<td><strong>', $txt['db_column_null'], '</strong></td>
												<td><strong>', $txt['db_column_default'], '</strong></td>
												<td><strong>', $txt['db_column_extra'], '</strong></td>
											</tr>';
				$column_color = 1;
				foreach ($table['columns'] as $column)
					echo '
											<tr class="row', $column_color = !$column_color, '">
												<td>', $column['name'], '</td>
												<td>', isset($column['type']) ? $column['type'] : $txt['na'], '</td>
												<td>', !empty($column['collation']) ? $column['collation'] : '', '</td>
												<td>', isset($column['null']) ? $column['null'] : $txt['na'], '</td>
												<td>', isset($column['default']) ? $column['default'] : $txt['na'], '</td>
												<td>', isset($column['extra']) ? $column['extra'] : $txt['na'], '</td>
											</tr>';
				echo '
										</table>
									</td>
								</tr>';
			}
		}
		echo '
							</table>
						</td>
					</tr>';
	}
	else
		echo '
					<tr>
						<td colspan="2"><strong>', $txt['no_detailed_db'], '</strong></td>
					</tr>';

	echo '
				</table>';

	// Setup the javascript stuff here
	echo '
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					window.databaseTables = {';
	foreach ($context['database_tables'] as $table)
		echo '
						\'', $table['name'], '\': \'0\',';
	echo '
						\'\':\'\'
					};
				// ]]></script>';

	echo '
			</div>';
}

function show_mods()
{
	global $txt;

	echo '
			<div class="tab-page" id="mods_installed"><h2 class="tab">', $txt['mods_installed'], '</h2>
				<script type="text/javascript">addSection("mods_installed", "', $txt['mods_installed'], '");</script>
				<table border="0" width="50%" align="center">
					<tr>
						<td width="200px"><strong>', $txt['package_name'], '</strong></td>
						<td><strong>', $txt['package_id'], '</strong></td>
						<td width="150px"><strong>', $txt['package_version'], '</strong></td>
					</tr>';

	foreach(loadInstalledPackages() as $package)
	{
		echo '
					<tr>
						<td>', $package['name'], '</td>
						<td>', (!empty($package['package_id']) ? $package['package_id'] : $package['id']), '</td>
						<td>', $package['version'], '</td>
					</tr>';
	}

	echo '
				</table>
			</div>';
}

function show_error_log()
{
	global $txt, $context;

	get_error_log();

	echo '
			<div class="tab-page" id="error_log"><h2 class="tab">', $txt['error_log'], '</h2>
				<script type="text/javascript">addSection("error_log", "', $txt['error_log'], '");</script>
				<table border="0" width="100%" cellpadding="2" cellspacing="2">
					<tr>
						<td width="25%"><strong>', $txt['error_log_count'], '</strong></td>
						<td>', $context['num_errors'], '</td>
					</tr>
					<tr>
						<td colspan="2"><strong>', (($context['num_errors'] < 101) ? $txt['show_all_errors'] : sprintf($txt['show_num_errors'], $context['num_errors'])), '</strong></td>
					</tr>
					<tr>
						<td colspan="2">';

	foreach ($context['errors'] as $error)
	{
		echo '
							<table width="100%" cellspacing="2" cellpadding="2" style="padding-left: 20px;">
								<tr>
									<td width="25%"><strong>&raquo; ', $txt['error_time'], '</strong></td>
									<td>', $error['time'], '</td>
								</tr>
								<tr>
									<td width="25%"><strong>&raquo; ', $txt['error_member'], '</strong></td>
									<td>', $error['member_id'], '</td>
								</tr>
								<tr>
									<td width="25%" valign="top"><strong>&raquo; ', $txt['error_url'], '</strong></td>
									<td><a href="', $error['url_html'], '">', $error['url_html'], '</a></td>
								</tr>
								<tr>
									<td width="25%" valign="top"><strong>&raquo; ', $txt['error_message'], '</strong></td>
									<td>', $error['message_html'], '</td>
								</tr>';

		if (isset($error['type']))
			echo '
								<tr>
									<td width="25%"><strong>&raquo; ', $txt['error_type'], '</strong></td>
									<td>', $error['type'], '</td>
								</tr>';

		echo '
							</table>
							<hr width="95%" />';
	}

	echo '
						</td>
					</tr>
				</table>
			</div>';
}

function show_status()
{
	global $context, $command_line, $txt, $db_type;

	if (strpos(strtolower(PHP_OS), 'win') === 0)
		get_windows_data();
	else
		get_linux_data();

	if (empty($db_type) || (!empty($db_type) && $db_type == 'mysql'))
		get_mysql_data();

	echo '
			<div class="tab-page" id="status"><h2 class="tab">', $txt['status'], '</h2>
				<script type="text/javascript">addSection("status", "', $txt['status'], '");</script>';

	if ($command_line)
	{
		if (!empty($context['operating_system']['name']))
			echo 'Operating System:   ', trim($context['operating_system']['name']), "\n";
		if (!empty($context['cpu_info']))
			echo 'Processor:          ', trim($context['cpu_info']['model']), ' (', trim($context['cpu_info']['mhz']), 'MHz)', "\n";
		if (isset($context['load_averages']))
			echo 'Load averages:      ', implode(', ', $context['load_averages']), "\n";
		if (!empty($context['running_processes']))
			echo 'Current processes:  ', count($context['running_processes']), ' (', !empty($context['num_sleeping_processes']) ? $context['num_sleeping_processes'] . ' sleeping, ' : '', $context['num_running_processes'], ' running, ', $context['num_zombie_processes'], ' zombie)', "\n";

		if (!empty($context['top_cpu_usage']))
		{
			echo 'Processes by CPU:   ';

			$temp = array();
			foreach ($context['top_cpu_usage'] as $proc)
				$temp[$proc['percent']] = $proc['name'] . ($proc['number'] > 1 ? ' (' . $proc['number'] . ') ' : ' ') . number_format($proc['percent'], 1) . '%';

			krsort($temp);
			echo implode(', ', $temp), "\n";
		}

		if (!empty($context['memory_usage']))
			echo 'Memory usage:       ', round(($context['memory_usage']['used'] * 100) / $context['memory_usage']['total'], 3), '% (', $context['memory_usage']['used'], 'k / ', $context['memory_usage']['total'], 'k)', "\n";
		if (isset($context['memory_usage']['swap_used']))
			echo 'Swap usage:         ', round(($context['memory_usage']['swap_used'] * 100) / max(1, $context['memory_usage']['swap_total']), 3), '% (', $context['memory_usage']['swap_used'], 'k / ', $context['memory_usage']['swap_total'], 'k)', "\n";

		if (!empty($context['mysql_processes']) || !empty($context['mysql_num_sleeping_processes']) || !empty($context['mysql_num_locked_processes']))
			echo 'MySQL processes:    ', $context['mysql_num_running_processes'] + $context['mysql_num_locked_processes'] + $context['mysql_num_sleeping_processes'], ' (', $context['mysql_num_sleeping_processes'], ' sleeping, ', $context['mysql_num_running_processes'], ' running, ', $context['mysql_num_locked_processes'], ' locked)', "\n";

		if (!empty($context['mysql_statistics']))
		{
			echo "\n", 'MySQL statistics:', "\n";

			foreach ($context['mysql_statistics'] as $stat)
			{
				$warning = (isset($stat['max']) && $stat['value'] > $stat['max']) || (isset($stat['min']) && $stat['value'] < $stat['min']);
				$warning = $warning ? '(should be ' . (isset($stat['min']) ? '>= ' . $stat['min'] . ' ' : '') . (isset($stat['max'], $stat['min']) ? 'and ' : '') . (isset($stat['max']) ? '<= ' . $stat['max'] : '') . ')' : '';

				echo sprintf('%-34s%-6.6s %34s', $stat['description'] . ':', round($stat['value'], 4), $warning), "\n";
			}
		}

		return;
	}

	echo '
		<div class="panel">
			<h2>Basic Information</h2>

			<div style="text-align: right;">', $context['current_time'], '</div>
			<table width="100%" cellpadding="2" cellspacing="0" border="0">';

	if (!empty($context['operating_system']['name']))
		echo '
				<tr>
					<th valign="top" style="text-align: left; width: 30%;">Operating System:</th>
					<td>', $context['operating_system']['name'], '</td>
				</tr>';

	if (!empty($context['cpu_info']))
		echo '
				<tr>
					<th valign="top" style="text-align: left; width: 30%;">Processor:</th>
					<td>', strtr($context['cpu_info']['model'], array('(R)' => '&reg;')), ' (', $context['cpu_info']['mhz'], 'MHz)</td>
				</tr>';

	if (isset($context['load_averages']))
		echo '
				<tr>
					<th style="text-align: left; width: 30%;">Load averages:</th>
					<td>', implode(', ', $context['load_averages']), '</td>
				</tr>';

	if (!empty($context['running_processes']))
		echo '
				<tr>
					<th style="text-align: left; width: 30%;">Current processes:</th>
					<td>', count($context['running_processes']), ' (', !empty($context['num_sleeping_processes']) ? $context['num_sleeping_processes'] . ' sleeping, ' : '', $context['num_running_processes'], ' running, ', $context['num_zombie_processes'], ' zombie)</td>
				</tr>';

	if (!empty($context['top_cpu_usage']))
	{
		echo '
				<tr>
					<th style="text-align: left; width: 30%;">Processes by CPU:</th>
					<td>';

		$temp = array();
		foreach ($context['top_cpu_usage'] as $proc)
			$temp[$proc['percent']] = htmlspecialchars($proc['name']) . ' <em>(' . $proc['number'] . ')</em> ' . number_format($proc['percent'], 1) . '%';

		krsort($temp);
		echo implode(', ', $temp);

		echo '
					</td>
				</tr>';
	}

	if (!empty($context['memory_usage']))
	{
		echo '
				<tr>
					<th valign="top" style="text-align: left; width: 30%;">Memory usage:</th>
					<td>
						', round(($context['memory_usage']['used'] * 100) / $context['memory_usage']['total'], 3), '% (', $context['memory_usage']['used'], 'k / ', $context['memory_usage']['total'], 'k)';
		if (isset($context['memory_usage']['swap_used']))
			echo '<br />
						Swap: ', round(($context['memory_usage']['swap_used'] * 100) / max(1, $context['memory_usage']['swap_total']), 3), '% (', $context['memory_usage']['swap_used'], 'k / ', $context['memory_usage']['swap_total'], 'k)';
		echo '
					</td>
				</tr>';
	}

	echo '
			</table>
		</div>';

	if (!empty($context['mysql_processes']) || !empty($context['mysql_num_sleeping_processes']) || !empty($context['mysql_num_locked_processes']))
	{
		echo '
		<div class="panel">
			<h2>MySQL processes</h2>

			<table width="100%" cellpadding="2" cellspacing="0" border="0">
				<tr>
					<th valign="top" style="text-align: left; width: 30%;">Total processes:</th>
					<td>', $context['mysql_num_running_processes'] + $context['mysql_num_locked_processes'] + $context['mysql_num_sleeping_processes'], ' (', $context['mysql_num_sleeping_processes'], ' sleeping, ', $context['mysql_num_running_processes'], ' running, ', $context['mysql_num_locked_processes'], ' locked)</td>
				</tr>
			</table>';

		if (!empty($context['mysql_processes']))
		{
			echo '
			<br />
			<h2>Running processes</h2>

			<table width="100%" cellpadding="2" cellspacing="0" border="0" style="table-layout: fixed;">
				<tr>
					<th style="width: 14ex;">State</th>
					<th style="width: 8ex;">Time</th>
					<th>Query</th>
				</tr>';

			foreach ($context['mysql_processes'] as $proc)
			{
				echo '
				<tr>
					<td>', $proc['state'], '</td>
					<td style="text-align: center;">', $proc['time'], 's</td>
					<td><div style="width: 100%; ', strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') !== false ? 'max-' : '', 'height: 7em; overflow: auto;"><pre style="margin: 0; border: 1px solid gray;">';

				$temp = explode("\n", $proc['query']);
				$min_indent = 0;
				foreach ($temp as $line)
				{
					preg_match('/^(\t*)/', $line, $x);
					if (strlen($x[0]) < $min_indent || $min_indent == 0)
						$min_indent = strlen($x[0]);
				}

				if ($min_indent > 0)
				{
					$proc['query'] = '';
					foreach ($temp as $line)
						$proc['query'] .= preg_replace('~^\t{0,' . $min_indent . '}~i', '', $line) . "\n";
				}

				// Now, let's clean up the query.
				$clean = '';
				$old_pos = 0;
				$pos = -1;
				while (true)
				{
					$pos = strpos($proc['query'], '\'', $pos + 1);
					if ($pos === false)
						break;
					$clean .= substr($proc['query'], $old_pos, $pos - $old_pos);

					$str_pos = $pos;
					while (true)
					{
						$pos1 = strpos($proc['query'], '\'', $pos + 1);
						$pos2 = strpos($proc['query'], '\\', $pos + 1);
						if ($pos1 === false)
							break;
						elseif ($pos2 == false || $pos2 > $pos1)
						{
							$pos = $pos1;
							break;
						}

						$pos = $pos2 + 1;
					}
					$str = substr($proc['query'], $str_pos, $pos - $str_pos + 1);
					$clean .= strlen($str) < 12 ? $str : '\'%s\'';

					$old_pos = $pos + 1;
				}
				$clean .= substr($proc['query'], $old_pos);

				echo strtr(htmlspecialchars($clean), array("\n" => '<br />', "\r" => ''));

				echo '</pre></div></td>
				</tr>';
			}

			echo '
			</table>';
		}

		echo '
		</div>';
	}

	if (!empty($context['mysql_statistics']))
	{
		echo '
		<div class="panel">
			<h2>MySQL Statistics</h2>

			<div style="text-align: right;">MySQL ', $context['mysql_version'], '</div>
			<table width="100%" cellpadding="2" cellspacing="0" border="0">';

		foreach ($context['mysql_statistics'] as $stat)
		{
			$warning = (isset($stat['max']) && $stat['value'] > $stat['max']) || (isset($stat['min']) && $stat['value'] < $stat['min']);

			echo '
				<tr>
					<th valign="top" style="text-align: left; width: 30%;">
						', $stat['description'], ':', isset($stat['setting']) ? '<br />
						<em style="font-size: smaller;' . ($warning ? 'font-weight: bold;' : '') . '">(' . $stat['setting'] . ')</em>' : '', '
					</th>
					<td>
						', round($stat['value'], 4);

			if (isset($stat['max']) || isset($stat['min']))
				echo '
						', $warning ? '<b>' : '', '(should be ', isset($stat['min']) ? '&gt;= ' . $stat['min'] . ' ' : '', isset($stat['max'], $stat['min']) ? 'and ' : '', isset($stat['max']) ? '&lt;= ' . $stat['max'] : '', ')', $warning ? '</b>' : '';

			echo '
					</td>
				</tr>';
		}

		echo '
			</table>';

		echo '
		<br />
		<h2>MySQL status</h2>

		<table width="100%" cellpadding="2" cellspacing="0" border="0">';

		foreach ($context['mysql_status'] as $var)
		{
			echo '
				<tr>
					<th valign="top" style="text-align: left; width: 30%;">', $var['name'], ':</th>
					<td>', $var['value'], '</td>
				</tr>';
		}

		echo '
			</table>

			<br />
			<h2>MySQL variables</h2>

			<table width="100%" cellpadding="2" cellspacing="0" border="0">';

		foreach ($context['mysql_variables'] as $var)
		{
			echo '
				<tr>
					<th valign="top" style="text-align: left; width: 30%;">', $var['name'], ':</th>
					<td>', $var['value'], '</td>
				</tr>';
		}

		echo '
			</table>';

		echo '
		</div>';
	}
	echo '
	</div>';
}

function show_footer()
{
	global $context, $boardurl, $forum_copyright, $forum_version;

	$t = sprintf($forum_copyright, $forum_version);
	echo '
			</div>
			<div style="clear: left">
				', sprintf($forum_copyright, $forum_version),' | <a href="http://validator.w3.org/check?uri=referer">XHTML</a> | <a href="http://jigsaw.w3.org/css-validator/">CSS</a>
			</div>
		</div>';

	/* Below is the hefty javascript for this. Upon opening the page it checks the current file versions with ones
	   held at simplemachines.org and works out if they are up to date.  If they aren't it colors that files number
	   red.  It also contains the function, swapOption, that toggles showing the detailed information for each of the
	   file catorgories. (sources, languages, and templates.) */

	echo '
		<script language="JavaScript" type="text/javascript" src="', $boardurl, '/Themes/default/', (strpos($context['forum_version'], '2.') !== false ? 'scripts/' : ''), 'script.js"></script>
		<script language="JavaScript" type="text/javascript" src="http://www.simplemachines.org/smf/detailed-version.js?version=', $context['forum_version'], '"></script>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var swaps = {};

			function swapOption(sendingElement, name)
			{
				// If it is undefined, or currently off, turn it on - otherwise off.
				swaps[name] = typeof(swaps[name]) == "undefined" || !swaps[name];
				document.getElementById(name).style.display = swaps[name] ? "" : "none";

				// Unselect the link and return false.
				sendingElement.blur();
				return false;
			}

			function smfDetermineVersions()
			{
				var highYour = {"Sources": "??", "Default" : "??", "Languages": "??", "Templates": "??"};
				var highCurrent = {"Sources": "??", "Default" : "??", "Languages": "??", "Templates": "??"};
				var lowVersion = {"Sources": false, "Default": false, "Languages" : false, "Templates": false};
				var knownLanguages = [".', implode('", ".', $context['default_known_languages']), '"];

				document.getElementById("Sources").style.display = "none";
				document.getElementById("Languages").style.display = "none";
				document.getElementById("Default").style.display = "none";
				if (document.getElementById("Templates"))
					document.getElementById("Templates").style.display = "none";


				if (typeof(window.smfVersions) == "undefined")
					window.smfVersions = {};

				for (var filename in window.smfVersions)
				{
					if (!document.getElementById("current" + filename))
						continue;

					var yourVersion = getInnerHTML(document.getElementById("your" + filename));

					var versionType;
					for (var verType in lowVersion)
						if (filename.substr(0, verType.length) == verType)
						{
							versionType = verType;
							break;
						}

					if (typeof(versionType) != "undefined")
					{
						if ((highYour[versionType] < yourVersion || highYour[versionType] == "??") && !lowVersion[versionType])
							highYour[versionType] = yourVersion;
						if (highCurrent[versionType] < smfVersions[filename] || highCurrent[versionType] == "??")
							highCurrent[versionType] = smfVersions[filename];

						if (yourVersion < smfVersions[filename])
						{
							lowVersion[versionType] = yourVersion;
							document.getElementById("your" + filename).style.color = "red";
						}
					}
					else if (yourVersion < smfVersions[filename])
						lowVersion[versionType] = yourVersion;

					setInnerHTML(document.getElementById("current" + filename), smfVersions[filename]);
					setInnerHTML(document.getElementById("your" + filename), yourVersion);
				}


				if (typeof(window.smfLanguageVersions) == "undefined")
					window.smfLanguageVersions = {};

				for (filename in window.smfLanguageVersions)
				{
					for (var i = 0; i < knownLanguages.length; i++)
					{
						if (!document.getElementById("current" + filename + knownLanguages[i]))
							continue;

						setInnerHTML(document.getElementById("current" + filename + knownLanguages[i]), smfLanguageVersions[filename]);

						yourVersion = getInnerHTML(document.getElementById("your" + filename + knownLanguages[i]));
						setInnerHTML(document.getElementById("your" + filename + knownLanguages[i]), yourVersion);

						if ((highYour["Languages"] < yourVersion || highYour["Languages"] == "??") && !lowVersion["Languages"])
							highYour["Languages"] = yourVersion;
						if (highCurrent["Languages"] < smfLanguageVersions[filename] || highCurrent["Languages"] == "??")
							highCurrent["Languages"] = smfLanguageVersions[filename];

						if (yourVersion < smfLanguageVersions[filename])
						{
							lowVersion["Languages"] = yourVersion;
							document.getElementById("your" + filename + knownLanguages[i]).style.color = "red";
						}
					}
				}

				setInnerHTML(document.getElementById("yourSources"), lowVersion["Sources"] ? lowVersion["Sources"] : highYour["Sources"]);
				setInnerHTML(document.getElementById("currentSources"), highCurrent["Sources"]);
				if (lowVersion["Sources"])
					document.getElementById("yourSources").style.color = "red";

				setInnerHTML(document.getElementById("yourDefault"), lowVersion["Default"] ? lowVersion["Default"] : highYour["Default"]);
				setInnerHTML(document.getElementById("currentDefault"), highCurrent["Default"]);
				if (lowVersion["Default"])
					document.getElementById("yourDefault").style.color = "red";

				if (document.getElementById("Templates"))
				{
					setInnerHTML(document.getElementById("yourTemplates"), lowVersion["Templates"] ? lowVersion["Templates"] : highYour["Templates"]);
					setInnerHTML(document.getElementById("currentTemplates"), highCurrent["Templates"]);

					if (lowVersion["Templates"])
						document.getElementById("yourTemplates").style.color = "red";
				}

				setInnerHTML(document.getElementById("yourLanguages"), lowVersion["Languages"] ? lowVersion["Languages"] : highYour["Languages"]);
				setInnerHTML(document.getElementById("currentLanguages"), highCurrent["Languages"]);
				if (lowVersion["Languages"])
					document.getElementById("yourLanguages").style.color = "red";
			}

			function smfHideDbColumns()
			{

				if (typeof(window.databaseTables) == "undefined")
					window.databaseTables = {};

				for (var filename in window.databaseTables)
				{
					if (!document.getElementById(filename))
						continue;

					document.getElementById(filename).style.display = "none";
				}
			}
		// ]]></script>';

	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[

			addLoadEvent(function() {
				smfDetermineVersions();
				smfHideDbColumns();

			});
		// ]]></script>';

	echo '
	</body>
</html>
';
}

function initialize()
{
	global $txt, $context, $smfInfo, $sourcedir, $forum_version, $db_show_debug, $db_last_error, $modSettings;

	// Set this to true so we get the correct forum value
	$ssi_gzip = true;







	$forum_version = get_file_versions(true);

	$smfInfo = !empty($modSettings['smfInfo']) ? $modSettings['smfInfo'] : '';

	if (empty($smfInfo) || (allowedTo('admin_forum') && isset($_GET['regenerate'])))
		generate_password();

	// If the user isn't an admin or they don't have a password in the URL, or its incorrect, kick 'em out
	if (!allowedTo('admin_forum') && (!isset($_POST['pass']) || strcmp($_POST['pass'], $smfInfo) != 0))



		show_password_form();

	// Either their an admin or have the right password
	require_once($sourcedir . '/Subs-Package.php');

	// Enable error reporting.
	error_reporting(E_ALL);
	@session_start();

	if ($context['user']['is_admin'] && isset($_GET['delete']))
	{
		// This won't work on all servers...

		@unlink(__FILE__);

		// Now just redirect to the forum...
		redirectexit();
	}
}

function get_database_version()
{
	global $db_type, $smcFunc, $context, $db_name, $txt, $db_prefix;

	if (empty($db_type) || (!empty($db_type) && $db_type == 'mysql'))
	{



		$temp_prefix = (strpos($db_prefix, $db_name) === false ? '`' . $db_name . '`.' : '') . $db_prefix;







		// Get the collation of the 'body' field of the messages table
		$query = ' SHOW FULL COLUMNS FROM ' . $temp_prefix . 'messages WHERE Field = \'body\'';
		$request = mysql_query($query);
		$row = @mysql_fetch_assoc($request);


		if (!empty($row))
			$collation = $row['Collation'];
	}
	if (!empty($collation))
		$context['character_set'] = $collation;

	else
		$context['character_set'] = $txt['unknown_db_version'];

	if (empty($smcFunc))
		$context['database_version'] = 'MySQL ' . mysql_get_server_info();

	else
	{
		db_extend();
		$context['database_version'] = $smcFunc['db_title'] . ' ' . $smcFunc['db_get_version']();
	}
}

function get_file_versions($core = false)
{
	global $sourcedir, $boarddir, $context, $txt, $scripturl, $boardurl, $settings;

	// Change index.php below to whatever you've changed yours to...
	$fp = fopen($boarddir . '/index.php', 'rb');
	$header = fread($fp, 3072);
	fclose($fp);

	// The version looks rougly like... that.
	if (preg_match('~\$forum_version\s=\s\'SMF (.+)\'~i', $header, $match) == 1)
		$context['forum_version'] = $match[1];

	// Not found!  This is bad.
	else
		$context['forum_version'] = '??';

	if ($core)
		return $context['forum_version'];

	$versionOptions = array(
		'include_ssi' => true,
		'include_subscriptions' => true,
		'sort_results' => true,
	);

	// Default place to find the languages would be the default theme dir.
	$lang_dir = $settings['default_theme_dir'] . '/languages';

	$version_info = array(
		'file_versions' => array(),
		'default_template_versions' => array(),
		'template_versions' => array(),
		'default_language_versions' => array(),
	);

	// Find the version in SSI.php's file header.
	if (!empty($versionOptions['include_ssi']) && file_exists($boarddir . '/SSI.php'))
	{
		$fp = fopen($boarddir . '/SSI.php', 'rb');
		$header = fread($fp, 4096);
		fclose($fp);

		// The comment looks rougly like... that.
		if (preg_match('~\*\s*Software\s+Version:\s+SMF\s+(.+?)[\s]{2}~i', $header, $match) == 1)
			$version_info['file_versions']['SSI.php'] = $match[1];

		// Not found!  This is bad.
		else
			$version_info['file_versions']['SSI.php'] = '??';
	}

	// Do the paid subscriptions handler?
	if (!empty($versionOptions['include_subscriptions']) && file_exists($boarddir . '/subscriptions.php'))
	{
		$fp = fopen($boarddir . '/subscriptions.php', 'rb');
		$header = fread($fp, 4096);
		fclose($fp);

		// Found it?
		if (preg_match('~\*\s*Software\s+Version:\s+SMF\s+(.+?)[\s]{2}~i', $header, $match) == 1)
			$version_info['file_versions']['subscriptions.php'] = $match[1];

		// If we haven't how do we all get paid?
		else
			$version_info['file_versions']['subscriptions.php'] = '??';
	}

	// Load all the files in the Sources directory, except this file and the redirect.
	$Sources_dir = dir($sourcedir);
	while ($entry = $Sources_dir->read())
	{
		if (substr($entry, -4) === '.php' && !is_dir($sourcedir . '/' . $entry) && $entry !== 'index.php')
		{
			// Read the first 4k from the file.... enough for the header.
			$fp = fopen($sourcedir . '/' . $entry, 'rb');
			$header = fread($fp, 4096);
			fclose($fp);

			// Look for the version comment in the file header.
			if (preg_match('~\*\s*Software\s+Version:\s+SMF\s+(.+?)[\s]{2}~i', $header, $match) == 1)
				$version_info['file_versions'][$entry] = $match[1];

			// It wasn't found, but the file was... show a '??'.
			else
				$version_info['file_versions'][$entry] = '??';
		}
	}
	$Sources_dir->close();

	// Load all the files in the default template directory - and the current theme if applicable.
	$directories = array('default_template_versions' => $settings['default_theme_dir']);
	if ($settings['theme_id'] != 1)
		$directories += array('template_versions' => $settings['theme_dir']);

	foreach ($directories as $type => $dirname)
	{
		$This_dir = dir($dirname);
		while ($entry = $This_dir->read())
		{
			if (substr($entry, -12) == 'template.php' && !is_dir($dirname . '/' . $entry))
			{
				// Read the first 768 bytes from the file.... enough for the header.
				$fp = fopen($dirname . '/' . $entry, 'rb');
				$header = fread($fp, 768);
				fclose($fp);

				// Look for the version comment in the file header.
				if (preg_match('~(?://|/\*)\s*Version:\s+(.+?);\s*' . preg_quote(basename($entry, '.template.php'), '~') . '(?:[\s]{2}|\*/)~i', $header, $match) == 1)
					$version_info[$type][$entry] = $match[1];

				// It wasn't found, but the file was... show a '??'.
				else
					$version_info[$type][$entry] = '??';
			}
		}
		$This_dir->close();
	}

	// Load up all the files in the default language directory and sort by language.
	$This_dir = dir($lang_dir);
	while ($entry = $This_dir->read())
	{
		if (substr($entry, -4) == '.php' && $entry != 'index.php' && !is_dir($lang_dir . '/' . $entry))
		{
			// Read the first 768 bytes from the file.... enough for the header.
			$fp = fopen($lang_dir . '/' . $entry, 'rb');
			$header = fread($fp, 768);
			fclose($fp);

			// Split the file name off into useful bits.
			list ($name, $language) = explode('.', $entry);

			// Look for the version comment in the file header.
			if (preg_match('~(?://|/\*)\s*Version:\s+(.+?);\s*' . preg_quote($name, '~') . '(?:[\s]{2}|\*/)~i', $header, $match) == 1)
				$version_info['default_language_versions'][$language][$name] = $match[1];

			// It wasn't found, but the file was... show a '??'.
			else
				$version_info['default_language_versions'][$language][$name] = '??';
		}
	}
	$This_dir->close();

	// Sort the file versions by filename.
	if (!empty($versionOptions['sort_results']))
	{
		ksort($version_info['file_versions']);
		ksort($version_info['default_template_versions']);
		ksort($version_info['template_versions']);
		ksort($version_info['default_language_versions']);

		// For languages sort each language too.
		foreach ($version_info['default_language_versions'] as $language => $dummy)
			ksort($version_info['default_language_versions'][$language]);
	}

	$context += array(
		'file_versions' => $version_info['file_versions'],
		'default_template_versions' => $version_info['default_template_versions'],
		'template_versions' => $version_info['template_versions'],
		'default_language_versions' => $version_info['default_language_versions'],
		'default_known_languages' => array_keys($version_info['default_language_versions']),
	);
}

function get_server_software()
{
	if (isset($_SERVER['SERVER_SOFTWARE'])) {
		return $_SERVER['SERVER_SOFTWARE'];
	} else if (($sf = getenv('SERVER_SOFTWARE'))) {
		return $sf;
	} else {
		return 'n/a';
	}
}

function get_php_setting($val, $rec = '')
{
	global $txt;
	$r = (ini_get($val) == '1' ? 1 : 0) ? $txt['on'] : $txt['off'];
	if (!empty($rec) && strcmp($r, $txt[$rec]) != 0)
		$r .= '&nbsp;<strong>(' . $txt['recommended'] . ': ' . $txt[$rec] . ')</strong>';
	return $r;
}

function get_smf_setting($val, $rec = '')
{
	global $txt;
	global $modSettings, $settings;
	$r = (!empty($GLOBALS[$val]) ? $txt['on'] : (!empty($modSettings[$val]) ? $txt['on'] : (!empty($settings[$val]) ? $txt['on'] : $txt['off'])));
	if (!empty($rec) && strcmp($r, $txt[$rec]) != 0)
		$r .= '&nbsp;<strong>(' . $txt['recommended'] . ': ' . $txt[$rec] . ')</strong>';
	return $r;
}

function generate_password()
{
	global $sourcedir, $smfInfo, $forum_version, $boardurl;

	if (strpos($forum_version, '1.') === 0)
		require_once($sourcedir . '/Admin.php');
	else
		require_once($sourcedir . '/Subs-Admin.php');

	$password = '';
	$possible = 'abcdfghjkmnpqrstvwxyz0123456789ABCDEFGHJKLMNOPQRSTUVXYZ';
	$i = 0;
	while ($i < 12)
	{
		$password .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
		$i++;
	}

	updateSettings(array('smfInfo' => $password));

	$smfInfo = $password;
}

function get_linux_data()
{
	global $context;

	$context['current_time'] = strftime('%B %d, %Y, %I:%M:%S %p');

	$context['load_averages'] = @implode('', @get_file_data('/proc/loadavg'));
	if (!empty($context['load_averages']) && preg_match('~^([^ ]+?) ([^ ]+?) ([^ ]+)~', $context['load_averages'], $matches) != 0)
		$context['load_averages'] = array($matches[1], $matches[2], $matches[3]);
	elseif (($context['load_averages'] = @`uptime 2>/dev/null`) != null && preg_match('~load average[s]?: (\d+\.\d+), (\d+\.\d+), (\d+\.\d+)~i', $context['load_averages'], $matches) != 0)
		$context['load_averages'] = array($matches[1], $matches[2], $matches[3]);
	else
		unset($context['load_averages']);

	$context['cpu_info'] = array();
	$cpuinfo = @implode('', @get_file_data('/proc/cpuinfo'));
	if (!empty($cpuinfo))
	{
		// This only gets the first CPU!
		if (preg_match('~model name\s+:\s*([^\n]+)~i', $cpuinfo, $match) != 0)
			$context['cpu_info']['model'] = $match[1];
		if (preg_match('~cpu mhz\s+:\s*([^\n]+)~i', $cpuinfo, $match) != 0)
			$context['cpu_info']['mhz'] = $match[1];
	}
	else
	{
		// Solaris, perhaps?
		$cpuinfo = @`psrinfo -pv 2>/dev/null`;
		if (!empty($cpuinfo))
		{
			if (preg_match('~clock (\d+)~', $cpuinfo, $match) != 0)
				$context['cpu_info']['mhz'] = $match[1];
			$cpuinfo = explode("\n", $cpuinfo);
			if (isset($cpuinfo[2]))
				$context['cpu_info']['model'] = trim($cpuinfo[2]);
		}
		else
		{
			// BSD?
			$cpuinfo = @`sysctl hw.model 2>/dev/null`;
			if (preg_match('~hw\.model:(.+)~', $cpuinfo, $match) != 0)
				$context['cpu_info']['model'] = trim($match[1]);
			$cpuinfo = @`sysctl dev.cpu.0.freq 2>/dev/null`;
			if (preg_match('~dev\.cpu\.0\.freq:(.+)~', $cpuinfo, $match) != 0)
				$context['cpu_info']['mhz'] = trim($match[1]);
		}
	}

	$context['memory_usage'] = array();

	function unix_memsize($str)
	{
		$str = strtr($str, array(',' => ''));

		if (strtolower(substr($str, -1)) == 'g')
			return $str * 1024 * 1024;
		elseif (strtolower(substr($str, -1)) == 'm')
			return $str * 1024;
		elseif (strtolower(substr($str, -1)) == 'k')
			return (int) $str;
		else
			return $str / 1024;
	}

	$meminfo = @get_file_data('/proc/meminfo');
	if (!empty($meminfo))
	{
		if (preg_match('~:\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)~', $meminfo[1], $matches) != 0)
		{
			$context['memory_usage']['total'] = $matches[1] / 1024;
			$context['memory_usage']['used'] = $matches[2] / 1024;
			$context['memory_usage']['free'] = $matches[3] / 1024;
			/*$context['memory_usage']['shared'] = $matches[4] / 1024;
			$context['memory_usage']['buffers'] = $matches[5] / 1024;
			$context['memory_usage']['cached'] = $matches[6] / 1024;*/
		}
		else
		{
			$mem = implode('', $meminfo);
			if (preg_match('~memtotal:\s*(\d+ [kmgb])~i', $mem, $match) != 0)
				$context['memory_usage']['total'] = unix_memsize($match[1]);
			if (preg_match('~memfree:\s*(\d+ [kmgb])~i', $mem, $match) != 0)
				$context['memory_usage']['free'] = unix_memsize($match[1]);
			if (isset($context['memory_usage']['total'], $context['memory_usage']['free']))
				$context['memory_usage']['used'] = $context['memory_usage']['total'] - $context['memory_usage']['free'];

			/*if (preg_match('~buffers:\s*(\d+ [kmgb])~i', $mem, $match) != 0)
				$context['memory_usage']['buffers'] = unix_memsize($match[1]);
			if (preg_match('~cached:\s*(\d+ [kmgb])~i', $mem, $match) != 0)
				$context['memory_usage']['cached'] = unix_memsize($match[1]);*/

			if (preg_match('~swaptotal:\s*(\d+ [kmgb])~i', $mem, $match) != 0)
				$context['memory_usage']['swap_total'] = unix_memsize($match[1]);
			if (preg_match('~swapfree:\s*(\d+ [kmgb])~i', $mem, $match) != 0)
				$context['memory_usage']['swap_free'] = unix_memsize($match[1]);
			if (isset($context['memory_usage']['swap_total'], $context['memory_usage']['swap_free']))
				$context['memory_usage']['swap_used'] = $context['memory_usage']['swap_total'] - $context['memory_usage']['swap_free'];

		}
		if (preg_match('~:\s+(\d+)\s+(\d+)\s+(\d+)~', $meminfo[2], $matches) != 0)
		{
			$context['memory_usage']['swap_total'] = $matches[1] / 1024;
			$context['memory_usage']['swap_used'] = $matches[2] / 1024;
			$context['memory_usage']['swap_free'] = $matches[3] / 1024;
		}

		$meminfo = false;
	}
	// Maybe a generic free?
	elseif (empty($context['memory_usage']))
	{
		$meminfo = explode("\n", @`free -k 2>/dev/null | awk '{ if ($2 * 1 > 0) print $2, $3, $4; }'`);
		if (!empty($meminfo[0]))
		{
			$meminfo[0] = explode(' ', $meminfo[0]);
			$meminfo[1] = explode(' ', $meminfo[1]);
			$context['memory_usage']['total'] = $meminfo[0][0] / 1024;
			$context['memory_usage']['used'] = $meminfo[0][1] / 1024;
			$context['memory_usage']['free'] = $meminfo[0][2] / 1024;
			$context['memory_usage']['swap_total'] = $meminfo[1][0] / 1024;
			$context['memory_usage']['swap_used'] = $meminfo[1][1] / 1024;
			$context['memory_usage']['swap_free'] = $meminfo[1][2] / 1024;
		}
	}
	// Solaris, Mac OS X, or FreeBSD?
	if (empty($context['memory_usage']))
	{
		// Well, Solaris will have kstat.
		$meminfo = explode("\n", @`kstat -p unix:0:system_pages:physmem unix:0:system_pages:freemem 2>/dev/null | awk '{ print $2 }'`);
		if (!empty($meminfo[0]))
		{
			$pagesize = `/usr/bin/pagesize`;
			$context['memory_usage']['total'] = unix_memsize($meminfo[0] * $pagesize);
			$context['memory_usage']['free'] = unix_memsize($meminfo[1] * $pagesize);
			$context['memory_usage']['used'] = $context['memory_usage']['total'] - $context['memory_usage']['free'];

			$meminfo = explode("\n", @`swap -l 2>/dev/null | awk '{ if ($4 * 1 > 0) print $4, $5; }'`);
			$context['memory_usage']['swap_total'] = 0;
			$context['memory_usage']['swap_free'] = 0;
			foreach ($meminfo as $memline)
			{
				$memline = explode(' ', $memline);
				if (empty($memline[0]))
					continue;

				$context['memory_usage']['swap_total'] += $memline[0];
				$context['memory_usage']['swap_free'] += $memline[1];
			}
			$context['memory_usage']['swap_used'] = $context['memory_usage']['swap_total'] - $context['memory_usage']['swap_free'];
		}
	}
	if (empty($context['memory_usage']))
	{
		// FreeBSD should have hw.physmem.
		$meminfo = @`sysctl hw.physmem 2>/dev/null`;
		if (!empty($meminfo) && preg_match('~hw\.physmem: (\d+)~i', $meminfo, $match) != 0)
		{
			$context['memory_usage']['total'] = unix_memsize($match[1]);

			$meminfo = @`sysctl hw.pagesize vm.stats.vm.v_free_count 2>/dev/null`;
			if (!empty($meminfo) && preg_match('~hw\.pagesize: (\d+)~i', $meminfo, $match1) != 0 && preg_match('~vm\.stats\.vm\.v_free_count: (\d+)~i', $meminfo, $match2) != 0)
			{
				$context['memory_usage']['free'] = $match1[1] * $match2[1] / 1024;
				$context['memory_usage']['used'] = $context['memory_usage']['total'] - $context['memory_usage']['free'];
			}

			$meminfo = @`swapinfo 2>/dev/null | awk '{ print $2, $4; }'`;
			if (preg_match('~(\d+) (\d+)~', $meminfo, $match) != 0)
			{
				$context['memory_usage']['swap_total'] = $match[1];
				$context['memory_usage']['swap_free'] = $match[2];
				$context['memory_usage']['swap_used'] = $context['memory_usage']['swap_total'] - $context['memory_usage']['swap_free'];
			}
		}
		// Let's guess Mac OS X?
		else
		{
			$meminfo = @`top -l1 2>/dev/null`;

			if (!empty($meminfo) && preg_match('~PhysMem: (?:.+?) ([\d\.]+\w) used, ([\d\.]+\w) free~', $meminfo, $match) != 0)
			{
				$context['memory_usage']['used'] = unix_memsize($match[1]);
				$context['memory_usage']['free'] = unix_memsize($match[2]);
				$context['memory_usage']['total'] = $context['memory_usage']['used'] + $context['memory_usage']['total'];
			}
		}
	}

	$context['operating_system']['type'] = 'unix';

	$check_release = array('centos', 'fedora', 'gentoo', 'redhat', 'slackware', 'yellowdog');

	foreach ($check_release as $os)
	{
		if (@file_exists('/etc/' . $os . '-release'))
			$context['operating_system']['name'] = implode('', get_file_data('/etc/' . $os . '-release'));
	}

	if (isset($context['operating_system']['name']))
		true;
	elseif (@file_exists('/etc/debian_version'))
		$context['operating_system']['name'] = 'Debian ' . implode('', get_file_data('/etc/debian_version'));
	elseif (@file_exists('/etc/SuSE-release'))
	{
		$temp = get_file_data('/etc/SuSE-release');
		$context['operating_system']['name'] = trim($temp[0]);
	}
	elseif (@file_exists('/etc/release'))
	{
		$temp = get_file_data('/etc/release');
		$context['operating_system']['name'] = trim($temp[0]);
	}
	else
		$context['operating_system']['name'] = trim(@`uname -s -r 2>/dev/null`);

	$context['running_processes'] = array();
	$processes = @`ps auxc 2>/dev/null | awk '{ print $2, $3, $4, $8, $11, $12 }'`;
	if (empty($processes))
		$processes = @`ps aux 2>/dev/null | awk '{ print $2, $3, $4, $8, $11, $12 }'`;

	// Maybe it's Solaris?
	if (empty($processes))
		$processes = @`ps -eo pid,pcpu,pmem,s,fname 2>/dev/null | awk '{ print $1, $2, $3, $4, $5, $6 }'`;

	// Okay, how about QNX?
	if (empty($processes))
		$processes = @`ps -eo pid,pcpu,comm 2>/dev/null | awk '{ print $1, $2, 0, "", $5, $6 }'`;
	if (!empty($processes))
	{
		$processes = explode("\n", $processes);

		$context['num_zombie_processes'] = 0;
		$context['num_sleeping_processes'] = 0;
		$context['num_running_processes'] = 0;

		for ($i = 1, $n = count($processes) - 1; $i < $n; $i++)
		{
			$proc = explode(' ', $processes[$i], 5);
			$additional = @implode('', @get_file_data('/proc/' . $proc[0] . '/statm'));

			if ($proc[4]{0} != '[' && strpos($proc[4], ' ') !== false)
				$proc[4] = strtok($proc[4], ' ');

			$context['running_processes'][$proc[0]] = array(
				'id' => $proc[0],
				'cpu' => $proc[1],
				'mem' => $proc[2],
				'title' => $proc[4],
			);

			if (strpos($proc[3], 'Z') !== false)
				$context['num_zombie_processes']++;
			elseif (strpos($proc[3], 'S') !== false)
				$context['num_sleeping_processes']++;
			else
				$context['num_running_processes']++;

			if (!empty($additional))
			{
				$additional = explode(' ', $additional);
				$context['running_processes'][$proc[0]]['mem_usage'] = $additional[0];
			}
		}

		$context['top_memory_usage'] = array('(other)' => array('name' => '(other)', 'percent' => 0, 'number' => 0));
		$context['top_cpu_usage'] = array('(other)' => array('name' => '(other)', 'percent' => 0, 'number' => 0));
		foreach ($context['running_processes'] as $proc)
		{
			$id = basename($proc['title']);

			if (!isset($context['top_memory_usage'][$id]))
				$context['top_memory_usage'][$id] = array('name' => $id, 'percent' => $proc['mem'], 'number' => 1);
			else
			{
				$context['top_memory_usage'][$id]['percent'] += $proc['mem'];
				$context['top_memory_usage'][$id]['number']++;
			}

			if (!isset($context['top_cpu_usage'][$id]))
				$context['top_cpu_usage'][$id] = array('name' => $id, 'percent' => $proc['cpu'], 'number' => 1);
			else
			{
				$context['top_cpu_usage'][$id]['percent'] += $proc['cpu'];
				$context['top_cpu_usage'][$id]['number']++;
			}
		}

		// TODO: shared memory?
		foreach ($context['top_memory_usage'] as $proc)
		{
			if ($proc['percent'] >= 1 || $proc['name'] == '(other)')
				continue;

			unset($context['top_memory_usage'][$proc['name']]);
			$context['top_memory_usage']['(other)']['percent'] += $proc['percent'];
			$context['top_memory_usage']['(other)']['number']++;
		}

		foreach ($context['top_cpu_usage'] as $proc)
		{
			if ($proc['percent'] >= 0.6 || $proc['name'] == '(other)')
				continue;

			unset($context['top_cpu_usage'][$proc['name']]);
			$context['top_cpu_usage']['(other)']['percent'] += $proc['percent'];
			$context['top_cpu_usage']['(other)']['number']++;
		}
	}
}

function get_windows_data()
{
	global $context;

	$context['current_time'] = strftime('%B %d, %Y, %I:%M:%S %p');

	function windows_memsize($str)
	{
		$str = strtr($str, array(',' => ''));

		if (strtolower(substr($str, -2)) == 'gb')
			return $str * 1024 * 1024;
		elseif (strtolower(substr($str, -2)) == 'mb')
			return $str * 1024;
		elseif (strtolower(substr($str, -2)) == 'kb')
			return (int) $str;
		elseif (strtolower(substr($str, -2)) == ' b')
			return $str / 1024;
		else
			trigger_error('Unknown memory format \'' . $str . '\'', E_USER_NOTICE);
	}

	$systeminfo = @`systeminfo /fo csv`;
	if (!empty($systeminfo))
	{
		$systeminfo = explode("\n", $systeminfo);

		$headings = explode('","', substr($systeminfo[1], 1, -1));
		$values = explode('","', substr($systeminfo[2], 1, -1));

		$context['cpu_info'] = array();
		if ($i = array_search('Processor(s)', $headings))
			if (preg_match('~\[01\]: (.+?) (\~?\d+) Mhz$~i', $values[$i], $match) != 0)
			{
				$context['cpu_info']['model'] = $match[1];
				$context['cpu_info']['mhz'] = $match[2];
			}

		$context['memory_usage'] = array();
		if ($i = array_search('Total Physical Memory', $headings))
			$context['memory_usage']['total'] = windows_memsize($values[$i]);
		if ($i = array_search('Available Physical Memory', $headings))
			$context['memory_usage']['free'] = windows_memsize($values[$i]);
		if (isset($context['memory_usage']['total'], $context['memory_usage']['free']))
			$context['memory_usage']['used'] = $context['memory_usage']['total'] - $context['memory_usage']['free'];

		if ($i = array_search('Virtual Memory: Available', $headings))
			$context['memory_usage']['swap_total'] = windows_memsize($values[$i]);
		if ($i = array_search('Virtual Memory: In Use', $headings))
			$context['memory_usage']['swap_used'] = windows_memsize($values[$i]);
		if (isset($context['memory_usage']['swap_total'], $context['memory_usage']['swap_free']))
			$context['memory_usage']['swap_free'] = $context['memory_usage']['swap_total'] - $context['memory_usage']['swap_used'];
	}

	$context['operating_system']['type'] = 'windows';
	$context['operating_system']['name'] = `ver`;
	if (empty($context['operating_system']['name']))
		$context['operating_system']['name'] = 'Microsoft Windows';

	$context['running_processes'] = array();
	$processes = @`tasklist /fo csv /v /nh`;
	if (!empty($processes))
	{
		$processes = explode("\n", $processes);
		$total_mem = 0;
		$total_cpu = 0;

		$context['num_zombie_processes'] = 0;
		$context['num_sleeping_processes'] = 0;
		$context['num_running_processes'] = 0;

		foreach ($processes as $proc)
		{
			if (empty($proc))
				continue;

			$proc = explode('","', substr($proc, 1, -1));

			$proc[7] = explode(':', $proc[7]);
			$proc[7] = ($proc[7][0] * 3600) + ($proc[7][1] * 60) + $proc[7][2];

			if (substr($proc[4], -1) == 'K')
				$proc[4] = (int) $proc[4];
			elseif (substr($proc[4], -1) == 'M')
				$proc[4] = $proc[4] * 1024;
			elseif (substr($proc[4], -1) == 'G')
				$proc[4] = $proc[4] * 1024 * 1024;
			else
				$proc[4] = $proc[4] / 1024;

			$context['running_processes'][$proc[1]] = array(
				'id' => $proc[1],
				'cpu_time' => $proc[7],
				'mem_usage' => $proc[4],
				'title' => $proc[0],
			);

			if (strpos($proc[5], 'Not') !== false)
				$context['num_zombie_processes']++;
			else
				$context['num_running_processes']++;

			$total_mem += $proc[4];
			$total_cpu += $proc[7];
		}

		foreach ($context['running_processes'] as $proc)
		{
			$context['running_processes'][$proc['id']]['cpu'] = ($proc['cpu_time'] * 100) / $total_cpu;
			$context['running_processes'][$proc['id']]['mem'] = ($proc['mem_usage'] * 100) / $total_mem;
		}

		$context['top_memory_usage'] = array('(other)' => array('name' => '(other)', 'percent' => 0, 'number' => 0));
		$context['top_cpu_usage'] = array('(other)' => array('name' => '(other)', 'percent' => 0, 'number' => 0));
		foreach ($context['running_processes'] as $proc)
		{
			$id = basename($proc['title']);

			if (!isset($context['top_memory_usage'][$id]))
				$context['top_memory_usage'][$id] = array('name' => $id, 'percent' => $proc['mem'], 'number' => 1);
			else
			{
				$context['top_memory_usage'][$id]['percent'] += $proc['mem'];
				$context['top_memory_usage'][$id]['number']++;
			}

			if (!isset($context['top_cpu_usage'][$id]))
				$context['top_cpu_usage'][$id] = array('name' => $id, 'percent' => $proc['cpu'], 'number' => 1);
			else
			{
				$context['top_cpu_usage'][$id]['percent'] += $proc['cpu'];
				$context['top_cpu_usage'][$id]['number']++;
			}
		}

		// TODO: shared memory?
		foreach ($context['top_memory_usage'] as $proc)
		{
			if ($proc['percent'] >= 1 || $proc['name'] == '(other)')
				continue;

			unset($context['top_memory_usage'][$proc['name']]);
			$context['top_memory_usage']['(other)']['percent'] += $proc['percent'];
			$context['top_memory_usage']['(other)']['number']++;
		}

		foreach ($context['top_cpu_usage'] as $proc)
		{
			if ($proc['percent'] >= 0.6 || $proc['name'] == '(other)')
				continue;

			unset($context['top_cpu_usage'][$proc['name']]);
			$context['top_cpu_usage']['(other)']['percent'] += $proc['percent'];
			$context['top_cpu_usage']['(other)']['number']++;
		}
	}
}

function get_mysql_data()
{
	global $context, $db_prefix;

	if (!isset($db_prefix) || $db_prefix === false)
		return;

	$request = mysql_query("
		SELECT CONCAT(SUBSTRING(VERSION(), 1, LOCATE('.', VERSION(), 3)), 'x')");
	list ($context['mysql_version']) = mysql_fetch_row($request);
	mysql_free_result($request);

	$request = mysql_query("
		SHOW VARIABLES");
	$context['mysql_variables'] = array();
	while ($row = @mysql_fetch_row($request))
		$context['mysql_variables'][$row[0]] = array(
			'name' => $row[0],
			'value' => htmlspecialchars($row[1]),
		);
	@mysql_free_result($request);

	$request = mysql_query("
		SHOW /*!50000 GLOBAL */ STATUS");
	$context['mysql_status'] = array();
	while ($row = @mysql_fetch_row($request))
		$context['mysql_status'][$row[0]] = array(
			'name' => $row[0],
			'value' => $row[1],
		);
	@mysql_free_result($request);

	$context['mysql_num_sleeping_processes'] = 0;
	$context['mysql_num_locked_processes'] = 0;
	$context['mysql_num_running_processes'] = 0;

	$request = mysql_query("
		SHOW FULL PROCESSLIST");
	$context['mysql_processes'] = array();
	while ($row = @mysql_fetch_assoc($request))
	{
		if ($row['State'] == 'Locked' || $row['State'] == 'Waiting for tables')
			$context['mysql_num_locked_processes']++;
		elseif ($row['Command'] == 'Sleep')
			$context['mysql_num_sleeping_processes']++;
		elseif (trim($row['Info']) == 'SHOW FULL PROCESSLIST' && $row['Time'] == 0 || trim($row['Info']) == '')
			$context['mysql_num_running_processes']++;
		else
		{
			$context['mysql_num_running_processes']++;

			$context['mysql_processes'][] = array(
				'id' => $row['Id'],
				'database' => $row['db'],
				'time' => $row['Time'],
				'state' => $row['State'],
				'query' => $row['Info'],
			);
		}
	}
	@mysql_free_result($request);

	$context['mysql_statistics'] = array();

	if (isset($context['mysql_status']['Connections'], $context['mysql_status']['Uptime']))
		$context['mysql_statistics'][] = array(
			'description' => 'Connections per second',
			'value' => $context['mysql_status']['Connections']['value'] / max(1, $context['mysql_status']['Uptime']['value']),
		);

	if (isset($context['mysql_status']['Bytes_received'], $context['mysql_status']['Uptime']))
		$context['mysql_statistics'][] = array(
			'description' => 'Kilobytes received per second',
			'value' => ($context['mysql_status']['Bytes_received']['value'] / max(1, $context['mysql_status']['Uptime']['value'])) / 1024,
		);

	if (isset($context['mysql_status']['Bytes_sent'], $context['mysql_status']['Uptime']))
		$context['mysql_statistics'][] = array(
			'description' => 'Kilobytes sent per second',
			'value' => ($context['mysql_status']['Bytes_sent']['value'] / max(1, $context['mysql_status']['Uptime']['value'])) / 1024,
		);

	if (isset($context['mysql_status']['Questions'], $context['mysql_status']['Uptime']))
		$context['mysql_statistics'][] = array(
			'description' => 'Queries per second',
			'value' => $context['mysql_status']['Questions']['value'] / max(1, $context['mysql_status']['Uptime']['value']),
		);

	if (isset($context['mysql_status']['Slow_queries'], $context['mysql_status']['Questions']))
		$context['mysql_statistics'][] = array(
			'description' => 'Percentage of slow queries',
			'value' => $context['mysql_status']['Slow_queries']['value'] / max(1, $context['mysql_status']['Questions']['value']),
		);

	if (isset($context['mysql_status']['Opened_tables'], $context['mysql_status']['Open_tables']))
		$context['mysql_statistics'][] = array(
			'description' => 'Opened vs. Open tables',
			'value' => $context['mysql_status']['Opened_tables']['value'] / max(1, $context['mysql_status']['Open_tables']['value']),
			'setting' => 'table_cache',
			'max' => 80,
		);

	if (isset($context['mysql_status']['Opened_tables'], $context['mysql_variables']['table_cache']['value']))
		$context['mysql_statistics'][] = array(
			'description' => 'Table cache usage',
			'value' => $context['mysql_status']['Open_tables']['value'] / max(1, $context['mysql_variables']['table_cache']['value']),
			'setting' => 'table_cache',
			'min' => 0.5,
			'max' => 0.9,
		);

	if (isset($context['mysql_status']['Key_reads'], $context['mysql_status']['Key_read_requests']))
		$context['mysql_statistics'][] = array(
			'description' => 'Key buffer read hit rate',
			'value' => $context['mysql_status']['Key_reads']['value'] / max(1, $context['mysql_status']['Key_read_requests']['value']),
			'setting' => 'key_buffer_size',
			'max' => 0.01,
		);

	if (isset($context['mysql_status']['Key_writes'], $context['mysql_status']['Key_write_requests']))
		$context['mysql_statistics'][] = array(
			'description' => 'Key buffer write hit rate',
			'value' => $context['mysql_status']['Key_writes']['value'] / max(1, $context['mysql_status']['Key_write_requests']['value']),
			'setting' => 'key_buffer_size',
			'max' => 0.5,
		);

	if (isset($context['mysql_status']['Threads_created'], $context['mysql_status']['Connections']))
		$context['mysql_statistics'][] = array(
			'description' => 'Thread cache hit rate',
			'value' => $context['mysql_status']['Connections']['value'] / max(1, $context['mysql_status']['Threads_created']['value']),
			'setting' => 'thread_cache_size',
			'min' => 30,
		);

	if (isset($context['mysql_status']['Threads_created'], $context['mysql_variables']['thread_cache_size']))
		$context['mysql_statistics'][] = array(
			'description' => 'Thread cache usage',
			'value' => $context['mysql_status']['Threads_cached']['value'] / max(1, $context['mysql_variables']['thread_cache_size']['value']),
			'setting' => 'thread_cache_size',
			'min' => 0.7,
			'max' => 0.9,
		);

	if (isset($context['mysql_status']['Created_tmp_tables'], $context['mysql_status']['Created_tmp_disk_tables']))
		$context['mysql_statistics'][] = array(
			'description' => 'Temporary table disk usage',
			'value' => $context['mysql_status']['Created_tmp_disk_tables']['value'] / max(1, $context['mysql_status']['Created_tmp_tables']['value']),
			'setting' => 'tmp_table_size',
			'max' => 0.5,
		);

	if (isset($context['mysql_status']['Sort_merge_passes'], $context['mysql_status']['Sort_rows']))
		$context['mysql_statistics'][] = array(
			'description' => 'Sort merge pass rate',
			'value' => $context['mysql_status']['Sort_merge_passes']['value'] / max(1, $context['mysql_status']['Sort_rows']['value']),
			'setting' => 'sort_buffer',
			'max' => 0.001,
		);

	$context['mysql_statistics'][] = array(
		'description' => 'Query cache enabled',
		'value' => !empty($context['mysql_variables']['query_cache_type']['value']) ? (int) ($context['mysql_variables']['query_cache_type']['value'] == 'ON') : 0,
		'setting' => 'query_cache_type',
		'min' => 1,
		'max' => 1,
	);

	if (isset($context['mysql_status']['Qcache_not_cached'], $context['mysql_status']['Com_select']))
		$context['mysql_statistics'][] = array(
			'description' => 'Query cache miss rate',
			'value' => 1 - $context['mysql_status']['Qcache_hits']['value'] / max(1, $context['mysql_status']['Com_select']['value'] + $context['mysql_status']['Qcache_hits']['value']),
			'setting' => 'query_cache_limit',
			'max' => 0.5,
		);

	if (isset($context['mysql_status']['Qcache_lowmem_prunes'], $context['mysql_status']['Com_select']))
		$context['mysql_statistics'][] = array(
			'description' => 'Query cache prune rate',
			'value' => $context['mysql_status']['Qcache_lowmem_prunes']['value'] / max(1, $context['mysql_status']['Com_select']['value']),
			'setting' => 'query_cache_size',
			'max' => 0.05,
		);
}

function get_file_data($filename)
{
	$data = @file($filename);
	if (is_array($data))
		return $data;

	if (strpos(strtolower(PHP_OS), 'win') !== false)
		@exec('type ' . preg_replace('~[^/a-zA-Z0-9\-_:]~', '', $filename), $data);
	else
		@exec('cat ' . preg_replace('~[^/a-zA-Z0-9\-_:]~', '', $filename) . ' 2>/dev/null', $data);

	if (!is_array($data))
		return false;

	foreach ($data as $k => $dummy)
		$data[$k] .= "\n";

	return $data;
}

function get_server_versions($checkFor)
{
	global $txt, $db_connection, $_PHPA, $smcFunc, $memcached, $modSettings;

	loadLanguage('Admin');

	$versions = array();

	// Is GD available?  If it is, we should show version information for it too.
	if (in_array('gd', $checkFor) && function_exists('gd_info'))
	{
		$temp = gd_info();
		$versions['gd'] = array('title' => $txt['support_versions_gd'], 'version' => $temp['GD Version']);
	}

	// If we're using memcache we need the server info.
	if (empty($memcached) && function_exists('memcache_get') && isset($modSettings['cache_memcached']) && trim($modSettings['cache_memcached']) != '')
		get_memcached_server();

	// Check to see if we have any accelerators installed...
	if (in_array('mmcache', $checkFor) && defined('MMCACHE_VERSION'))
		$versions['mmcache'] = array('title' => 'Turck MMCache', 'version' => MMCACHE_VERSION);
	if (in_array('eaccelerator', $checkFor) && defined('EACCELERATOR_VERSION'))
		$versions['eaccelerator'] = array('title' => 'eAccelerator', 'version' => EACCELERATOR_VERSION);
	if (in_array('phpa', $checkFor) && isset($_PHPA))
		$versions['phpa'] = array('title' => 'ionCube PHP-Accelerator', 'version' => $_PHPA['VERSION']);
	if (in_array('apc', $checkFor) && extension_loaded('apc'))
		$versions['apc'] = array('title' => 'Alternative PHP Cache', 'version' => phpversion('apc'));
	if (in_array('memcache', $checkFor) && function_exists('memcache_set'))
		$versions['memcache'] = array('title' => 'Memcached', 'version' => empty($memcached) ? '???' : memcache_get_version($memcached));

	return $versions;
}

function get_database_info()
{
	// This is sloooowwwwwwwww
	global $context, $db_name, $db_prefix;

	$match = array();
	$temp_prefix = preg_match('~(?:.*\.)?([^.]*)~', $db_prefix, $match) === 1 ? $match[1] : $db_prefix;



	$result = mysql_query('SHOW TABLE STATUS FROM `' . $db_name . '` LIKE \'' . $temp_prefix . '%\'');

	$context['database_tables'] = array();
	$context['database_size'] = 0;
	while ($row = mysql_fetch_assoc($result))
	{
		$context['database_tables'][$row['Name']] = array(
			'name' => str_replace($db_prefix, '', '`' . $db_name . '`.' . $row['Name']),
			'engine' => $row['Engine'],
			'rows' => $row['Rows'],
			'size' => convert_memory($row['Data_length']),
			'max_size' => convert_memory($row['Max_data_length']),
			'overhead' => convert_memory($row['Data_free']),
			'auto_increment' => !empty($row['Auto_increment']) ? $row['Auto_increment'] : 'n/a',
			'collation' => $row['Collation'],
			'columns' => array(),
		);
		$context['database_size'] += $row['Data_length'];
	}
	@mysql_free_result($result);

	$context['database_size'] = convert_memory($context['database_size']);

	foreach($context['database_tables'] as $table => $info)
	{
		// Get the columns of the table, and thier stuff...
		$result = mysql_query('SHOW FULL COLUMNS FROM ' . $table . ' FROM `' . $db_name . '`');
		echo mysql_error();
		while ($column = mysql_fetch_assoc($result))
			$context['database_tables'][$table]['columns'][$column['Field']] = array(
				'name' => $column['Field'],
				'type' => $column['Type'],
				'collation' => $column['Collation'],
				'null' => $column['Null'],
				'default' => $column['Default'],
				'extra' => $column['Extra'],
			);
		@mysql_free_result($result);
	}
}

function get_error_log()
{
	global $context, $db_prefix, $smcFunc, $scripturl, $txt;

	$context['errors'] = array();

	// 1.0 queries first... (regular ol' mysql calls)
	if(empty($smcFunc))
	{
		// Just how many errors are there?
		$result = mysql_query("
			SELECT COUNT(*)
			FROM {$db_prefix}log_errors");
		list ($context['num_errors']) = mysql_fetch_row($result);
		mysql_free_result($result);

		if ($context['num_errors'] == 0)
			return;

		// Find and sort out the errors.
		$request = mysql_query("
			SELECT ID_ERROR, ID_MEMBER, url, logTime, message
			FROM {$db_prefix}log_errors
			ORDER BY ID_ERROR DESC
			LIMIT 100");

		while ($row = mysql_fetch_assoc($request))
		{
			$show_message = strtr(strtr(preg_replace('~&lt;span class=&quot;remove&quot;&gt;(.+?)&lt;/span&gt;~', '$1', $row['message']), array("\r" => '', '<br />' => "\n", '<' => '&lt;', '>' => '&gt;', '"' => '&quot;')), array("\n" => '<br />'));

			$context['errors'][] = array(
				'error_id' => $row['ID_ERROR'],
				'member_id' => $row['ID_MEMBER'],
				'time' => timeformat($row['logTime']),
				'url_html' => htmlspecialchars($scripturl . $row['url']),
				'message_html' => $show_message,
			);
		}
		mysql_free_result($request);
	}
	else
	{
		// Just how many errors are there?
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}log_errors',
			array()
		);
		list ($context['num_errors']) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		if ($context['num_errors'] == 0)
			return;

		// Find and sort out the errors.
		$request = $smcFunc['db_query']('', '
			SELECT id_error, id_member, url, log_time, message, error_type, file, line
			FROM {db_prefix}log_errors
			ORDER BY id_error DESC
			LIMIT 100',
			array()
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$show_message = strtr(strtr(preg_replace('~&lt;span class=&quot;remove&quot;&gt;(.+?)&lt;/span&gt;~', '$1', $row['message']), array("\r" => '', '<br />' => "\n", '<' => '&lt;', '>' => '&gt;', '"' => '&quot;')), array("\n" => '<br />'));
			if (!empty($row['file']) && !empty($row['line']))
				$show_message .= '<br />' . $txt['error_file'] . ': ' . $row['file'] . '<br />' . $txt['error_line'] . ': ' . $row['line'];

			$context['errors'][] = array(
				'error_id' => $row['id_error'],
				'member_id' => $row['id_member'],
				'time' => timeformat($row['log_time']),
				'url_html' => htmlspecialchars($scripturl . $row['url']),
				'message_html' => $show_message,
				'type' => $row['error_type'],
				'file' => $row['file'],
				'line' => $row['line'],
			);
		}
		$smcFunc['db_free_result']($request);
	}
}

function convert_memory($number, $bytes=true)
{
  $bitsOrBytes = ($bytes) ? 'B' : 'b';

  $thousandArray = array();
  $thousandArray[0] = '';
  $thousandArray[1] = 'K';
  $thousandArray[2] = 'M';
  $thousandArray[3] = 'G';
  $thousandArray[4] = 'T';
  $thousandArray[5] = 'P';

  for ($i = 0; $number > 1024 && $i < count($thousandArray); $i++)
    $number /= 1024;

  return number_format($number,2) . ' ' . $thousandArray[$i] . $bitsOrBytes;
}
