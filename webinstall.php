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

// !!! On upgrade, warn about installed.list!

$GLOBALS['required_php_version'] = '4.1.0';
$GLOBALS['required_mysql_version'] = '3.23.28';

// Initialize everything and load the language files.
@set_time_limit(0);
$start_time = time();
load_language_data();
initialize_inputs();

template_webinstall_above();

if (function_exists('doStep' . $_GET['step']))
	call_user_func('doStep' . $_GET['step']);

template_webinstall_below();

function template_webinstall_above()
{
	global $txt;

	echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="robots" content="noindex" />
		<title>', $txt['smf_installer'], '</title>
		<style type="text/css">
			body
			{
				background-color: #e5e5e8;
				margin: 0;
				padding: 0;
			}
			body, td
			{
				color: #000000;
				font-size: small;
				font-family: verdana, sans-serif;
			}
			div#header
			{
				background-color: white;
				padding: 22px 4% 12px 4%;
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
				border: 2px dashed #cc5566;
				background-color: #ffd9df;
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
				padding-right: 2ex;
			}
			#language-list
			{
				max-height: 22ex;
				overflow: auto;
			}
			#language-list label
			{
				display: block;
				float: left;
				width: 50%;
			}
			#language-list input
			{
				clear: left;
				float: left;
			}
			#language-list strong
			{
				float: left;
				padding-left: 1ex;
				width: 90%;
			}
			#language-list .supported-ver
			{
				font-size: x-small;
				font-style: italic;
				font-weight: normal;
				white-space: nowrap;
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
		</style>
	</head>
	<body>
		<div id="header">
			<div title="Akihabara">', $txt['smf_installer'], '</div>
		</div>
		<div id="content">';
}

function template_webinstall_below()
{
	echo '
		</div>
	</body>
</html>';
}

function initialize_inputs()
{
	// Turn off magic quotes runtime and enable error reporting.
	@set_magic_quotes_runtime(0);
	error_reporting(E_ALL);

	// Fun.  Low PHP version...
	if (!isset($_GET))
	{
		$GLOBALS['_GET']['step'] = 0;
		return;
	}

	ob_start();
	if (ini_get('session.save_handler') == 'user')
		ini_set('session.save_handler', 'files');
	@session_start();
	ignore_user_abort(true);

	// Add slashes, as long as they aren't already being added.
	if (@get_magic_quotes_gpc() == 0)
	{
		foreach ($_POST as $k => $v)
		{
			if (is_array($v))
			{
				foreach ($v as $k2 => $v2)
					$_POST[$k][$k2] = addslashes($v2);
			}
			else
				$_POST[$k] = addslashes($v);
		}
	}

	if (!isset($_SESSION['webinstall_state']))
		$_SESSION['webinstall_state'] = array();

	// Create a file - this is defined in PHP 5, just use the same function name.
	if (!function_exists('file_put_contents'))
	{
		function file_put_contents($filename, $data)
		{
			$text_filetypes = array('php', 'txt', '.js', 'css', 'vbs', 'tml', 'htm');

			$fp = fopen($filename, in_array(substr($filename, -3), $text_filetypes) ? 'wt' : 'wb');

			if (!$fp)
				return 0;

			fwrite($fp, $data);
			fclose($fp);

			return strlen($data);
		}
	}

	if (isset($_GET['ftphelp']))
	{
		global $txt;

		echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>', $txt['ftp_path'], '</title>
	</head>
	<body style="background-color: #D4D4D4; font: 10pt verdana, sans-serif; margin-top: 5%;">
		<div style="background-color: #F0F0F0; border: 1px solid gray; margin: 1ex 0; padding: 1.2ex;">
			<strong>', $txt['ftp_path'], '</strong><br />
			<br />
			', $txt['ftp_path_help'], '<br />
			<br />
			<div class="righttext"><a href="javascript:self.close();">', $txt['ftp_path_help_close'], '</a></div>
		</div>
	</body>
</html>';
		exit;
	}

	// Force an integer step, defaulting to 0.
	$_GET['step'] = (int) @$_GET['step'];
	$_GET['substep'] = (int) @$_GET['substep'];
}

function doStep0()
{
	global $txt;

	// Just so people using older versions of PHP aren't left in the cold.
	if (!isset($_SERVER['PHP_SELF']))
		$_SERVER['PHP_SELF'] = isset($GLOBALS['HTTP_SERVER_VARS']['PHP_SELF']) ? $GLOBALS['HTTP_SERVER_VARS']['PHP_SELF'] : basename(__FILE__);

	// Check the PHP version.
	if ((!function_exists('version_compare') || version_compare($GLOBALS['required_php_version'], PHP_VERSION) > 0) && !isset($_GET['overphp']))
	{
		echo '
				<div class="error_message">
					<div>', $txt['error_php_too_low'], '</div>
					<br />
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_bad_try_again'], '
				</div>';

		return false;
	}

	// Very simple check on the session.save_path for Windows.
	if (session_save_path() === '/tmp' && substr(__FILE__, 1, 2) === ':\\')
		$error = 'error_session_save_path';

	// Since each of the three messages would look the same, anyway...
	if (isset($error))
	{
		echo '
				<div class="error_message">
					<div>', $txt[$error], '</div>
					<br />
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
				</div>';

		return false;
	}

	// Make sure this stuff is set, if not go to the default values.
	if (!isset($_SESSION['webinstall_state']['is_logged_in']))
	{
		$_SESSION['webinstall_state']['is_logged_in'] = false;
		$_SESSION['webinstall_state']['is_charter'] = false;
		$_SESSION['webinstall_state']['is_beta_tester'] = false;
		$_SESSION['webinstall_state']['is_team'] = false;

		$_SESSION['webinstall_state']['access'] = array(0);

		$_SESSION['webinstall_state']['can_svn'] = false;
		$_SESSION['webinstall_state']['user_data'] = '';
	}

	$install_info = fetch_install_info();

	if ($install_info === false)
	{
		echo '
				<div class="error_message">
					<div>', $txt['cant_fetch_install_info'], '</div>
					<br />
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
				</div>';
		return false;
	}

	echo '
				<form action="', $_SERVER['PHP_SELF'], '?step=1" method="post">';

	if (file_exists(dirname(__FILE__) . '/Settings.php') && !file_exists(dirname(__FILE__) . '/install.php'))
	{
		echo '
					<div class="panel">
						<h2>', $txt['upgrade_process'], '</h2>
						<h3>', $txt['upgrade_process_info'], '</h3>';

		if (!is_dir(dirname(__FILE__) . '/Sources') || !is_dir(dirname(__FILE__) . '/Themes'))
			echo '
						<div class="error_message">
							', $txt['source_theme_location_problem'], '
						</div>';
		echo '
					</div>';
	}

	echo '
					<div class="panel">
						<h2>', $txt['package_info'], '</h2>
						<h3>', $txt['package_info_info'], '</h3>

						<table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 1ex;">
							<tr>
								<td width="26%" valign="top" class="textbox" style="padding-bottom: 1ex; white-space: normal;"><label>', $txt['member_login'], ':</label></td>
								<td valign="top">';

	if (empty($_SESSION['webinstall_state']['is_logged_in']))
		echo '
									<span style="white-space: nowrap;"><label for="member_username">', $txt['member_username'], ':</label> <input type="text" size="20" name="member_username" id="member_username" value="', isset($_POST['member_username']) ? $_POST['member_username'] : '', '" style="margin-right: 3ex;" onchange="if (this.value != \'\' && this.form.member_password.value != \'\') this.form.verify.click();" /></span>
									<span style="white-space: nowrap;"><label for="member_password">', $txt['member_password'], ':</label> <input type="password" size="20" name="member_password" id="member_password" value="" style="margin-right: 3ex;" onchange="if (this.value != \'\' && this.form.member_password.value != \'\') this.form.verify.click();" /></span> <input type="submit" name="verify" value="', $txt['member_verify'], '" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['member_login_info'], '</div>';
	else
		echo '
									', $txt['member_verify_done'], ' (<a href="', $_SERVER['PHP_SELF'], '?step=1&amp;logout=1">', $txt['member_verify_logout'], '</a>)';

	echo '
								</td>
							</tr><tr>
								<td width="26%" valign="top" class="textbox"><label for="filename">', $txt['package_info_version'], ':</label></td>
								<td style="padding-bottom: 2ex;">';

	if (count($install_info['install']) > 1)
	{
		echo '
									<select name="filename" id="filename" style="width: 40%;">';
		foreach ($install_info['install'] as $file => $name)
			echo '
										<option value="', $file, '">', $name, '</option>';
		echo '
									</select>';
	}
	else
	{
		foreach ($install_info['install'] as $file => $name)
			echo '
										<input type="hidden" name="filename" value="', $file, '" />', $name;
	}

	echo '
								</td>';

	if (!empty($_SESSION['webinstall_state']['can_svn']))
		echo '
							</tr><tr>
								<td width="26%" valign="top" class="textbox"><label for="use_svn">', $txt['download_svn'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="checkbox" name="use_svn" id="use_svn" value="1" /> ', $txt['yes'], '
								</td>';

	if (count($install_info['mirrors']) > 1)
	{
		echo '				</tr><tr>
								<td width="26%" valign="top" class="textbox"><label for="mirror">', $txt['package_info_mirror'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<select name="mirror" id="mirror" style="width: 40%;">';

		foreach ($install_info['mirrors'] as $url => $name)
			echo '
										<option value="', $url, '">', $name, '</option>';

		echo '
									</select>
								</td>';
	}
	else
	{
		@list ($k) = array_keys($install_info['mirrors']);

		echo '
							</tr><tr>
								<td colspan="2"><input type="hidden" name="mirror" id="mirror" value="', $k, '" /></td>';
	}

	echo '
							</tr><tr>
								<td width="26%" valign="top" class="textbox"><label>', $txt['package_info_languages'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<div id="language-list">';

	foreach ($install_info['languages'] as $file => $data)
	{
		echo '
										<label for="language-', $file, '">
											<input type="checkbox" name="languages[]" id="language-', $file, '" value="', $file, '" />
											<strong>
												', $data['name'], '
												<span class="supported-ver">(SMF ', implode(', SMF ', $data['versions']), ')</span>
											</strong>
										</label>';
	}

	echo '
										<div style="clear: both;"></div>
									</div>
								</td>
							</tr>
						</table>';

	if (empty($_SESSION['webinstall_state']['agree']))
		echo '
						<br />
						<div style="margin: 0 1ex 2ex 1ex; padding: 1.5ex; border: 2px dashed #33cc44; background-color: #dfffe9;">
							<div style="float: left; width: 1.5ex; font-size: 2em; color: #33cc44;">!</div>
							', $txt['read_the_license'], '<br />
							<div class="righttext" style="margin-top: 1ex;"><label for="agree"><input type="checkbox" name="agree" id="agree" /> ', $txt['read_the_license_done'], '</label></div>
						</div>';

	echo '
						<div style="margin-right: 1ex;" align="right"><input type="submit" value="', $txt['package_info_ready'], '" /></div>
					</div>
				</form>';
}

function doStep1()
{
	global $txt, $ftp;

	// Remember if they agreed (even if they are only verifying.)
	if (isset($_POST['agree']))
		$_SESSION['webinstall_state']['agree'] = true;

	if (!empty($_POST['verify']) || (!empty($_POST['member_username']) && !empty($_POST['member_password'])))
	{
		$pass_data = 'web_user=' . base64_encode($_POST['member_username']) . '&check&web_pass=' . sha1(sha1(strtolower($_POST['member_username']) . $_POST['member_password']) . 'w$--IN5~2a');

		$data = (int) fetch_web_data('http://download.simplemachines.org/index.php', $pass_data . '&verify=1');

		$_SESSION['webinstall_state']['is_logged_in'] = !empty($data);
		$_SESSION['webinstall_state']['is_charter'] = $data === 2;
		$_SESSION['webinstall_state']['is_beta_tester'] = $data === 3;
		$_SESSION['webinstall_state']['is_team'] = $data === 4;

		if ($_SESSION['webinstall_state']['is_team'])
			$_SESSION['webinstall_state']['access'] = array(0,1,2);
		elseif ($_SESSION['webinstall_state']['is_charter'] || $_SESSION['webinstall_state']['is_beta_tester'])
			$_SESSION['webinstall_state']['access'] = array(0,2);
		else
			$_SESSION['webinstall_state']['access'] = array(0);

		$_SESSION['webinstall_state']['can_svn'] = $_SESSION['webinstall_state']['is_team'] || $_SESSION['webinstall_state']['is_beta_tester'];

		$_SESSION['webinstall_state']['user_data'] = $_SESSION['webinstall_state']['is_logged_in'] ? '?' . $pass_data : '';

		$_SESSION['webinstall_state']['member_info'] = $_SESSION['webinstall_state']['can_svn'] ? array($_POST['member_username'], $_POST['member_password']) : array();

		if (empty($data))
		{
			echo '
						<br />
						<div class="error_message" style="margin: 0 1ex 2ex 1ex;">
							<div style="float: left; width: 2ex; font-size: 2em; color: red;">X</div>
							', $txt['error_not_member'], '
						</div>';

		}

		return doStep0();
	}
	elseif (isset($_GET['logout']))
	{
		$_SESSION['webinstall_state']['is_logged_in'] = false;
		$_SESSION['webinstall_state']['is_charter'] = false;
		$_SESSION['webinstall_state']['is_beta_tester'] = false;
		$_SESSION['webinstall_state']['is_team'] = false;

		$_SESSION['webinstall_state']['access'] = array(0);

		$_SESSION['webinstall_state']['can_svn'] = false;
		$_SESSION['webinstall_state']['user_data'] = '';

		return doStep0();
	}

	// Perhaps they don't want to use a chmod of 777.
	if (isset($_REQUEST['chmod']) && is_numeric($_REQUEST['chmod']))
	{
		// Make sure they passed us a valid mode.
		if (preg_match('~^([0]?[0-7]{3})$~', $_REQUEST['chmod']) !== 0)
			$_SESSION['webinstall_state']['chmod'] = octdec($_REQUEST['chmod']);
	}

	if (!isset($_SESSION['webinstall_state']['chmod']))
		$_SESSION['webinstall_state']['chmod'] = 0777;

	if (empty($_SESSION['webinstall_state']['agree']))
	{
		echo '
						<br />
						<div style="margin: 0 1ex 2ex 1ex; padding: 1.5ex; border: 2px dashed #cc5566; background-color: #ffd9df;">
							<div style="float: left; width: 2ex; font-size: 2em; color: red;">X</div>
							<div style="padding: 1ex;">', $txt['error_read_the_license'], '</div>
						</div>';

		return doStep0();
	}

	if (!empty($_POST['mirror']))
	{
		// Verify mirror.
		$install_info = fetch_install_info();
		if (!isset($install_info['mirrors'][$_POST['mirror']]))
			die('Security error.');

		// Build file list ;).
		$files_to_download = array();

		if (function_exists('gzinflate'))
			$ext = '.tar.gz';
		else
			$ext = '.tar';

		if (file_exists(dirname(__FILE__) . '/Settings.php') && !file_exists(dirname(__FILE__) . '/install.php'))
			$type = 'upgrade';
		else
			$type = 'install';

		if (!empty($_SESSION['webinstall_state']['can_svn']) && !empty($_POST['use_svn']))
		{
			// SVN files only have the branch numbers on them and not the actual version.
			preg_match('~(smf_[\d]-[\d])(.*)~', $_POST['filename'], $match);
			$_POST['filename_unmodified'] = $_POST['filename'];
			$_POST['filename'] = $match[1] . '-dev' . strftime('%Y%m%d') . '_';

			$_POST['mirror'] = 'https://devel.simplemachines.org/mkbuild/release/';
		}

		$files_to_download[] = $_POST['mirror'] . $_POST['filename'] . $type . $ext;

		if (isset($_POST['languages']))
		{
			$version_selected = str_replace('SMF ', '', $_SESSION['webinstall_state']['install_info']['install'][isset($_POST['filename_unmodified']) ? $_POST['filename_unmodified'] : $_POST['filename']]);
			foreach ($_POST['languages'] as $lang)
				if (isset($_SESSION['webinstall_state']['install_info']['languages'][$lang]) && in_array($version_selected, $_SESSION['webinstall_state']['install_info']['languages'][$lang]['versions']))
					$files_to_download[] = $_POST['mirror'] . $_POST['filename'] . $lang . $ext;
		}
		$_SESSION['webinstall_state']['files_to_download'] = $files_to_download;
		$_SESSION['webinstall_state']['files_to_download_total'] = count($files_to_download);
	}

	// Windows isn't helped by chmod settings and FTP.
	if (strpos(__FILE__, ':\\') === 1)
		return doStep2();

	if (!isset($ftp))
		$ftp = new ftp_connection(null);
	// Save the error so we can mess with listing...
	elseif ($ftp->error !== false)
		$ftp_error = $ftp->last_message === null ? '' : $ftp->last_message;

	list ($username, $detect_path, $found_path) = $ftp->detect_path(dirname(__FILE__));

	if ($found_path || !isset($_POST['ftp_path']))
		$_POST['ftp_path'] = $detect_path;

	if (!isset($_POST['ftp_username']))
		$_POST['ftp_username'] = $username;

	echo '
				<form action="', $_SERVER['PHP_SELF'], '?step=2" method="post">
					<div class="panel">
						<h2>', $txt['ftp_login'], '</h2>
						<h3>', $txt['ftp_login_info'], '</h3>';

			if (isset($ftp_error))
				echo '
					<div class="error_message">
						<div>
							', $txt['error_ftp_no_connect'], '<br />
							<br />
							<code>', $ftp_error, '</code>
						</div>
					</div>
					<br />';

			echo '
						<div style="margin: 0 1ex 2ex 1ex; padding: 1.5ex; border: 2px dashed #33cc44; background-color: #dfffe9;">
							<div style="float: left; width: 1.5ex; font-size: 2em; color: #33cc44;">!</div>
							', $txt['ftp_please_note'], '
						</div>

						<table width="520" cellspacing="0" cellpadding="0" border="0" align="center" style="margin-bottom: 1ex;">
							<tr>
								<td width="26%" valign="top" class="textbox"><label for="ftp_server">', $txt['ftp_server'], ':</label></td>
								<td>
									<div style="float: right; margin-right: 1px;"><label for="ftp_port" class="textbox"><strong>', $txt['ftp_port'], ':&nbsp;</strong></label> <input type="text" size="3" name="ftp_port" id="ftp_port" value="', isset($_POST['ftp_port']) ? $_POST['ftp_port'] : '21', '" /></div>
									<input type="text" size="30" name="ftp_server" id="ftp_server" value="', isset($_POST['ftp_server']) ? $_POST['ftp_server'] : 'localhost', '" style="width: 70%;" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['ftp_server_info'], '</div>
								</td>
							</tr><tr>
								<td width="26%" valign="top" class="textbox"><label for="ftp_username">', $txt['ftp_username'], ':</label></td>
								<td>
									<input type="text" size="50" name="ftp_username" id="ftp_username" value="', isset($_POST['ftp_username']) ? $_POST['ftp_username'] : '', '" style="width: 99%;" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['ftp_username_info'], '</div>
								</td>
							</tr><tr>
								<td width="26%" valign="top" class="textbox"><label for="ftp_password">', $txt['ftp_password'], ':</label></td>
								<td>
									<input type="password" size="50" name="ftp_password" id="ftp_password" style="width: 99%;" />
									<div style="font-size: smaller; margin-bottom: 3ex;">', $txt['ftp_password_info'], '</div>
								</td>
							</tr><tr>
								<td width="26%" valign="top" class="textbox"><label for="ftp_path">', $txt['ftp_path'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="text" size="50" name="ftp_path" id="ftp_path" value="', $_POST['ftp_path'], '" style="width: 99%;" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', !empty($found_path) ? $txt['ftp_path_found_info'] : $txt['ftp_path_info'], '</div>
								</td>
							</tr>
						</table>

						<div class="righttext" style="margin: 1ex;"><input type="submit" value="', $txt['ftp_connect'], '" /></div>
					</div>';
}

function doStep2()
{
	global $txt, $ftp;

	$chmod = &$_SESSION['webinstall_state']['chmod'];

	if (isset($_POST['ftp_username']))
	{
		$ftp = new ftp_connection($_POST['ftp_server'], $_POST['ftp_port'], $_POST['ftp_username'], $_POST['ftp_password']);

		if ($ftp->error === false)
		{
			// Try it without /home/abc just in case they messed up.
			if (!$ftp->chdir($_POST['ftp_path']))
				$ftp->chdir(preg_replace('~^/home[2]?/[^/]+?~', '', $_POST['ftp_path']));
		}

		if ($ftp->error === false)
		{
			foreach ($_SESSION['webinstall_state']['files_to_download'] as $i => $file)
			{
				$ftp->create_file('smf_install' . $i . '.tmp');
				$ftp->chmod('smf_install' . $i . '.tmp', $chmod);
			}

			if (!file_exists(dirname(__FILE__) . '/smf_install0.tmp'))
				$ftp->error = true;
		}

		if ($ftp->error === false)
		{
			$_SESSION['installer_temp_ftp'] = array(
				'server' => $_POST['ftp_server'],
				'port' => $_POST['ftp_port'],
				'username' => $_POST['ftp_username'],
				'password' => $_POST['ftp_password'],
				'path' => $_POST['ftp_path']
			);

			run_chmod_test($ftp, $chmod);
		}
		elseif ($_POST['ftp_username'] != '')
		{
			echo '
					<div class="error_message">
						<div>', $txt['error_not_right_path'], '</div>
					</div>';

			return doStep1();
		}
	}

	if (ini_get('memory_limit') < 64)
		ini_set('memory_limit', '64M');

	foreach ($_SESSION['webinstall_state']['files_to_download'] as $i => $file)
	{
		if ($i < $_GET['substep'])
			continue;

		$data = fetch_web_data($file, isset($_SESSION['webinstall_state']['user_data']) ? $_SESSION['webinstall_state']['user_data'] : '');

		if (function_exists('gzinflate'))
			$data = extract_gzip($data);

		if ($data === false)
		{
			echo '
					<div class="error_message">
						<div>', sprintf($txt['error_unable_download'], $file), '</div>
						<br />
						<a href="', $_SERVER['PHP_SELF'], '?step=1&substep=', $i, '">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
					</div>';

			return false;
		}

		file_put_contents(dirname(__FILE__) . '/smf_install' . $i . '.tmp', $data);

		if ($i < ($_SESSION['webinstall_state']['files_to_download_total'] - 1))
		{
			$query_string = '?step=2&substep=' . ($i + 1);
			$percent_done_total = round((($i + 1) / $_SESSION['webinstall_state']['files_to_download_total']) * 100, 2);
			// Pausing time!
			echo '
				<div class="panel">
					<h2 style="margin-top: 2ex;">', $txt['not_done_yet'], '</h2>
					<h3>', $txt['download_paused'], '</h3>

					<div style="padding-left: 20%; padding-right: 20%; margin-top: 1ex;">
						<strong>', $txt['download_progress'], ':</strong>
						<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
							<div style="padding-top: 1pt; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $percent_done_total, '%</div>
							<div style="width: ', $percent_done_total, '%; height: 12pt; z-index: 1; background-color: red;">&nbsp;</div>
						</div>
					</div>
					<form action="', $_SERVER['PHP_SELF'], $query_string, '" method="post" name="autoSubmit">
						<div class="righttext" style="margin: 1ex;"><input name="b" type="submit" value="', $txt['continue'], '" /></div>
					</form>
					<script type="text/javascript"><!-- // --><![CDATA[
						window.onload = doAutoSubmit;
						var countdown = 3;

						function doAutoSubmit()
						{
							if (countdown == 0)
								document.autoSubmit.submit();
							else if (countdown == -1)
								return;

							document.autoSubmit.b.value = "', $txt['continue'], ' (" + countdown + ")";
							countdown--;

							setTimeout(doAutoSubmit, 1000);
						}
					// ]]></script>
				</div>';
			return true;
		}
	}

	$_SESSION['webinstall_state']['is_logged_in'] = false;
	$_SESSION['webinstall_state']['is_charter'] = false;
	$_SESSION['webinstall_state']['is_beta_tester'] = false;
	$_SESSION['webinstall_state']['is_team'] = false;
	$_SESSION['webinstall_state']['access'] = array(0);
	$_SESSION['webinstall_state']['can_svn'] = false;
	$_SESSION['webinstall_state']['user_data'] = '';
	$_SESSION['webinstall_state']['member_info'] = array();

	echo '
				<div class="panel">
					<h2>', $txt['download_successful'], '</h2>
					<h3>', $txt['download_successful_info'], '</h3>

					<form action="', $_SERVER['PHP_SELF'], '?step=3" method="post" name="autoSubmit">
						<div class="righttext" style="margin: 1ex;"><input type="submit" name="b" value="', $txt['continue'], '" /></div>
					</form>
					<script type="text/javascript"><!-- // --><![CDATA[
						window.onload = doAutoSubmit;
						var countdown = 3;

						function doAutoSubmit()
						{
							if (countdown == 0)
								document.autoSubmit.submit();
							else if (countdown == -1)
								return;

							document.autoSubmit.b.value = "', $txt['continue'], ' (" + countdown + ")";
							countdown--;

							setTimeout(doAutoSubmit, 1000);
						}
					// ]]></script>
				</div>';

	return true;
}

function doStep3()
{
	global $txt;

	$chmod = &$_SESSION['webinstall_state']['chmod'];

	if (ini_get('memory_limit') < 64)
		ini_set('memory_limit', '64M');

	foreach ($_SESSION['webinstall_state']['files_to_download'] as $i => $file)
	{
		if ($i < $_GET['substep'])
			continue;

		$fp = fopen(dirname(__FILE__) . '/smf_install' . $i . '.tmp', 'rb');
		$data = '';
		while (!feof($fp))
			$data .= fread($fp, 4096);
		fclose($fp);

		if (!empty($_SESSION['installer_temp_ftp']))
		{
			$ftp = new ftp_connection($_SESSION['installer_temp_ftp']['server'], $_SESSION['installer_temp_ftp']['port'], $_SESSION['installer_temp_ftp']['username'], $_SESSION['installer_temp_ftp']['password']);
			$ftp->chdir($_SESSION['installer_temp_ftp']['path']);

			extract_tar($data, dirname(__FILE__), $ftp);

			$ftp->unlink('smf_install' . $i . '.tmp');
			$ftp->close();
		}
		else
		{
			extract_tar($data, dirname(__FILE__), null);

			unlink('smf_install' . $i . '.tmp');
		}

		if ($i < ($_SESSION['webinstall_state']['files_to_download_total'] - 1))
		{
			$query_string = '?step=3&substep=' . ($i + 1);
			$percent_done_total = round((($i + 1) / $_SESSION['webinstall_state']['files_to_download_total']) * 100, 2);

			// Pausing time!
			echo '
				<div class="panel">
					<h2 style="margin-top: 2ex;">', $txt['not_done_yet'], '</h2>
					<h3>', $txt['extraction_paused'], '</h3>

					<div style="padding-left: 20%; padding-right: 20%; margin-top: 1ex;">
						<strong>', $txt['extraction_progress'], ':</strong>
						<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
							<div style="padding-top: 1pt; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $percent_done_total, '%</div>
							<div style="width: ', $percent_done_total, '%; height: 12pt; z-index: 1; background-color: red;">&nbsp;</div>
						</div>
					</div>
					<form action="', $_SERVER['PHP_SELF'], $query_string, '" method="post" name="autoSubmit">
						<div class="righttext" style="margin: 1ex;"><input name="b" type="submit" value="', $txt['continue'], '" /></div>
					</form>
					<script type="text/javascript"><!-- // --><![CDATA[
						window.onload = doAutoSubmit;
						var countdown = 3;

						function doAutoSubmit()
						{
							if (countdown == 0)
								document.autoSubmit.submit();
							else if (countdown == -1)
								return;

							document.autoSubmit.b.value = "', $txt['continue'], ' (" + countdown + ")";
							countdown--;

							setTimeout(doAutoSubmit, 1000);
						}
					// ]]></script>
				</div>';
			return true;
		}
	}

	$_SESSION['webinstall_state']['files_to_download'] = array();

	if (file_exists(dirname(__FILE__) . '/install.php'))
	{
		header('Location: http://' . (empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST']) . (strtr(dirname($_SERVER['PHP_SELF']), '\\', '/') == '/' ? '' : strtr(dirname($_SERVER['PHP_SELF']), '\\', '/')) . '/install.php');
		exit;
	}
	elseif (file_exists(dirname(__FILE__) . '/upgrade.php'))
	{
		header('Location: http://' . (empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST']) . (strtr(dirname($_SERVER['PHP_SELF']), '\\', '/') == '/' ? '' : strtr(dirname($_SERVER['PHP_SELF']), '\\', '/')) . '/upgrade.php');
		exit;
	}

	echo '
				<div class="panel">
					<h2>', $txt['extraction_complete'], '</h2>
					<h3>', $txt['extraction_complete_info'], '</h3>

					<form action="', strtr(dirname($_SERVER['PHP_SELF']), array(basename(__FILE__) => 'install.php')), '" method="post">
						<div class="righttext" style="margin: 1ex;"><input type="submit" value="', $txt['continue'], '" /></div>
					</form>
				</div>';

	unset($_SESSION['webinstall_state']);

	return true;
}

function run_chmod_test(&$ftp, &$chmod)
{
	global $txt;

	$attempt_perms = array(
		0755,
		0775,
		0777,
	);

	// At this point, we have valid FTP information.  Time to do a chmod test.
	$ftp_test_file = 'smf_install_chmod_test.php';
	$local_test_file = dirname(__FILE__) . '/smf_install_chmod_test.php';
	$ftp->create_file($ftp_test_file);

	$chmod = false;
	foreach ($attempt_perms as $attempt)
	{
		$ftp->chmod($ftp_test_file, $attempt);
		if (!is_writable($local_test_file))
			continue;

		file_put_contents($local_test_file, '<' . '?php echo 1; ?' . '>');

		$chop = preg_replace('~/[^/]*$~', '/', $_SERVER['PHP_SELF']);
		$result = fetch_web_data('http://' . $_SERVER['HTTP_HOST'] . $chop . basename($ftp_test_file));

		// Aha, it works.  This is the best chmod, then.
		if ($result === '1')
		{
			$chmod = $attempt;
			break;
		}
	}

	if ($chmod === false)
	{
		echo '
			<div class="error_message">
				<div>', $txt['error_horrible_chmod'], '</div>
				<br />
				<a href="', $_SERVER['PHP_SELF'], '?step=1&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
			</div>';

		exit;
	}

	$ftp->unlink($ftp_test_file);
}

// Get the contents of a URL, irrespective of allow_url_fopen.
function fetch_web_data($url, $post_data = '', $keep_alive = false, $redirection_level = 0)
{
	global $webmaster_email;
	static $keep_alive_dom = null, $keep_alive_fp = null;

	preg_match('~^(http|ftp)(s)?://([^/:]+)(:(\d))?(.+)$~', $url, $match);

	// An FTP url. We should try connecting and RETRieving it...
	if (empty($match[1]))
		return false;
	elseif ($match[1] == 'ftp')
	{
		// Establish a connection and attempt to enable passive mode.
		$ftp = new ftp_connection(($match[2] ? 'ssl://' : '') . $match[3], empty($match[5]) ? 21 : $match[5], 'anonymous', 'installer@simplemachines.org');
		if ($ftp->error !== false || !$ftp->passive())
			return false;

		// I want that one *points*!
		$ftp->send_command('RETR ' . $match[6]);

		// Since passive mode worked (or we would have returned already!) open the connection.
		$fp = $ftp->connect_passive();
		if (!$fp)
			return false;

		// The server should now say something in acknowledgement.
		$ftp->check_response(150);

		$data = '';
		while (!feof($fp))
			$data .= fread($fp, 4096);
		fclose($fp);

		// All done, right?  Good.
		$ftp->check_response(226);
		$ftp->close();
	}
	// This is more likely; a standard HTTP URL.
	elseif (isset($match[1]) && $match[1] == 'http')
	{
		// Figure out the origin (Host header, also for keepalive.)
		$origin = $match[3];
		if (!empty($match[5]))
			$origin .= ':' . $match[5];
		elseif (!empty($match[2]))
			$origin .= ':443';

		if ($keep_alive && $origin == $keep_alive_dom)
			$fp = $keep_alive_fp;
		if (empty($fp) || feof($fp))
		{
			// Open the socket on the port we want...
			$fp = @fsockopen(($match[2] ? 'ssl://' : '') . $match[3], empty($match[5]) ? ($match[2] ? 443 : 80) : $match[5], $err, $err, 5);
			if (!$fp)
				return false;
		}

		if ($keep_alive)
		{
			$keep_alive_dom = $origin;
			$keep_alive_fp = $fp;
		}

		// I want this, from there, and I'm not going to be bothering you for more (probably.)
		if (empty($post_data))
			fwrite($fp, 'GET ' . $match[6] . ' HTTP/1.0' . "\r\n");
		else
			fwrite($fp, 'POST ' . $match[6] . ' HTTP/1.0' . "\r\n");

		fwrite($fp, 'Host: ' . $origin . "\r\n");
		fwrite($fp, 'User-Agent: PHP/SMF' . "\r\n");

		if (!empty($_SESSION['webinstall_state']['can_svn']) && strpos($match[6], '-dev') !== false && !empty($_SESSION['webinstall_state']['member_info']))
		{
			// Don't go giving this to the wrong places.
			if ($origin === 'devel.simplemachines.org:443')
				fwrite($fp, 'Authorization: Basic ' . base64_encode(strtolower($_SESSION['webinstall_state']['member_info'][0]) . ':' . $_SESSION['webinstall_state']['member_info'][1]) . "\r\n");
		}

		if ($keep_alive)
			fwrite($fp, 'Connection: Keep-Alive' . "\r\n");
		else
			fwrite($fp, 'Connection: close' . "\r\n");

		if (!empty($post_data))
		{
			fwrite($fp, 'Content-Type: application/x-www-form-urlencoded' . "\r\n");
			fwrite($fp, 'Content-Length: ' . strlen($post_data) . "\r\n\r\n");
			fwrite($fp, $post_data);
		}
		else
			fwrite($fp, "\r\n");

		$response = fgets($fp, 768);

		// Redirect in case this location is permanently or temporarily moved.
		if ($redirection_level < 3 && preg_match('~^HTTP/\S+\s+30[127]~i', $response) === 1)
		{
			$header = '';
			$location = '';
			while (!feof($fp) && trim($header = fgets($fp, 4096)) != '')
				if (strpos($header, 'Location:') !== false)
					$location = trim(substr($header, strpos($header, ':') + 1));

			if (empty($location))
				return false;
			else
			{
				if (!$keep_alive)
					fclose($fp);
				return fetch_web_data($location, $post_data, $keep_alive, $redirection_level + 1);
			}
		}

		// Make sure we get a 200 OK.
		elseif (preg_match('~^HTTP/\S+\s+20[01]~i', $response) === 0)
			return false;

		// Skip the headers...
		while (!feof($fp) && trim($header = fgets($fp, 4096)) != '')
		{
			if (preg_match('~content-length:\s*(\d+)~i', $header, $match) != 0)
				$content_length = $match[1];
			elseif (preg_match('~connection:\s*close~i', $header) != 0)
			{
				$keep_alive_dom = null;
				$keep_alive = false;
			}

			continue;
		}

		$data = '';
		if (isset($content_length))
		{
			while (!feof($fp) && strlen($data) < $content_length)
				$data .= fread($fp, $content_length - strlen($data));
		}
		else
		{
			while (!feof($fp))
				$data .= fread($fp, 4096);
		}

		if (!$keep_alive)
			fclose($fp);
	}
	else
	{
		// Umm, this shouldn't happen?
		trigger_error('fetch_web_data(): Bad URL', E_USER_NOTICE);
		$data = false;
	}

	return $data;
}

function extract_gzip($data)
{
	// If this doesn't return the right signature, it's not a gzip.
	$id = unpack('H2a/H2b', substr($data, 0, 2));
	if (strtolower($id['a'] . $id['b']) != '1f8b')
		return false;

	$flags = unpack('Ct/Cf', substr($data, 2, 2));

	// Not deflate!
	if ($flags['t'] != 8)
		return false;
	$flags = $flags['f'];

	$offset = 10;

	// "Read" the filename and comment. // !!! Might be mussed.
	if ($flags & 12)
	{
		while ($flags & 8 && $data{$offset++} != "\0")
			$offset;
		while ($flags & 4 && $data{$offset++} != "\0")
			$offset;
	}

	$crc = unpack('Vcrc32/Visize', substr($data, strlen($data) - 8, 8));
	$data = gzinflate(substr($data, $offset, strlen($data) - 8 - $offset));

	// crc32 doesn't work as expected on 64-bit functions - make our own.
	// http://www.php.net/crc32#79567
	function smf_crc32_check($data, $verify)
	{
		$crc = crc32($data);
		if ($crc == $verify)
			return true;

		if ($crc & 0x80000000)
		{
			$crc ^= 0xffffffff;
			$crc += 1;
			$crc = -$crc;
		}

		return $crc == $verify;
	}

	if (!smf_crc32_check($data, $crc['crc32']))
		return false;

	return $data;
}

function extract_tar($data, $destination, $ftp)
{
	$octdec = array('mode', 'uid', 'gid', 'size', 'mtime', 'checksum', 'type');
	$blocks = strlen($data) / 512 - 1;
	$offset = 0;

	$chmod = &$_SESSION['webinstall_state']['chmod'];

	$return = array();

	while ($offset < $blocks)
	{
		$header = substr($data, $offset << 9, 512);
		$current = unpack('a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100linkname/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155path', $header);

		if ($current['type'] == 5 && substr($current['filename'], -1) != '/')
			$current['filename'] .= '/';

		// Blank record?  This is probably at the end of the file.
		if (empty($current['filename']))
		{
			$offset += 512;
			continue;
		}

		foreach ($current as $k => $v)
		{
			if (in_array($k, $octdec))
				$current[$k] = octdec(trim($v));
			else
				$current[$k] = trim($v);
		}

		$checksum = 256;
		for ($i = 0; $i < 148; $i++)
			$checksum += ord($header{$i});
		for ($i = 156; $i < 512; $i++)
			$checksum += ord($header{$i});

		if ($current['checksum'] != $checksum)
			break;

		$size = ceil($current['size'] / 512);
		$current['data'] = substr($data, ++$offset << 9, $current['size']);
		$offset += $size;

		$filename = array_pop(explode('/', $current['filename']));

		// Not a directory and doesn't exist already...
		if (substr($current['filename'], -1, 1) != '/' && strpos($filename, '.') !== false)
		{
			if (strpos($current['filename'], '/') !== false)
			{
				$dirs = explode('/', $current['filename']);
				array_pop($dirs);

				$dirpath = '';
				foreach ($dirs as $dir)
				{
					if (!file_exists($destination . '/' . $dirpath . $dir))
					{
						if ($ftp != null)
						{
							$ftp->create_dir($dirpath . $dir);
							$ftp->chmod($dirpath . $dir, $chmod);
						}
						else
							mkdir($destination . '/' . $dirpath . $dir, $chmod);
					}

					$dirpath .= $dir . '/';
				}
			}

			if ($ftp != null)
			{
				$ftp->create_file($current['filename']);
				$ftp->chmod($current['filename'], $chmod);
			}
			file_put_contents($destination . '/' . $current['filename'], $current['data']);
		}
		// Folder... create.
		else
		{
			// Protect from accidental parent directory writing...
			$current['filename'] = strtr($current['filename'], array('../' => '', '/..' => ''));

			if (!file_exists($destination . '/' . $current['filename']))
			{
				if ($ftp != null)
				{
					$ftp->create_dir($current['filename']);
					$ftp->chmod($current['filename'], $chmod);
				}
				else
					mkdir($destination . '/' . $current['filename'], $chmod);
			}
		}
	}
}

function fetch_install_info()
{
	$install_info = fetch_web_data('http://www.simplemachines.org/smf/mirrors.xml');

	if ($install_info === false)
		return false;

	$info = array(
		'mirrors' => array(),
		'install' => array(),
		'languages' => array(),
	);

	$vers = array();

	// Get mirrors.
	preg_match_all('~<mirror name="([^"]+)">([^<]+)</mirror>~', $install_info, $matches, PREG_SET_ORDER);
	foreach ($matches as $match)
		$info['mirrors'][$match[2]] = $match[1];

	// Get install packages.
	preg_match_all('~<install access="([^"]+)" name="([^"]+)">([^<]+)</install>~', $install_info, $matches, PREG_SET_ORDER);
	foreach ($matches as $match)
		if (in_array($match[1], $_SESSION['webinstall_state']['access']))
		{
			$info['install'][$match[3]] = $match[2];
			$vers[] = str_replace('SMF ', '', $match[2]);
		}

	// Get language packages.
	preg_match_all('~<language name="([^"]+)" versions="([^"]+)">([^<]+)</language>~', $install_info, $matches, PREG_SET_ORDER);
	foreach ($matches as $match)
	{
		$versions = explode(', ', $match[2]);
		foreach ($versions as $id => $ver)
			if (!in_array($ver, $vers))
				unset($versions[$ver]);

		if (empty($versions))
			continue;

		$info['languages'][$match[3]] = array(
			'name' => $match[1],
			'versions' => explode(', ', $match[2]),
		);
	}

	// Put it into the session data for later use.
	$_SESSION['webinstall_state']['install_info'] = $info;

	return $info;
}

if (!function_exists('sha1'))
{
	function sha1($str)
	{
		// If we have mhash loaded in, use it instead!
		if (function_exists('mhash') && defined('MHASH_SHA1'))
			return bin2hex(mhash(MHASH_SHA1, $str));

		$nblk = (strlen($str) + 8 >> 6) + 1;
		$blks = array_pad(array(), $nblk * 16, 0);

		for ($i = 0; $i < strlen($str); $i++)
			$blks[$i >> 2] |= ord($str{$i}) << (24 - ($i % 4) * 8);

		$blks[$i >> 2] |= 0x80 << (24 - ($i % 4) * 8);

		return sha1_core($blks, strlen($str) * 8);
	}

	// This is the core SHA-1 calculation routine, used by sha1().
	function sha1_core($x, $len)
	{
		@$x[$len >> 5] |= 0x80 << (24 - $len % 32);
		$x[(($len + 64 >> 9) << 4) + 15] = $len;

		$w = array();
		$a = 1732584193;
		$b = -271733879;
		$c = -1732584194;
		$d = 271733878;
		$e = -1009589776;

		for ($i = 0, $n = count($x); $i < $n; $i += 16)
		{
			$olda = $a;
			$oldb = $b;
			$oldc = $c;
			$oldd = $d;
			$olde = $e;

			for ($j = 0; $j < 80; $j++)
			{
				if ($j < 16)
					$w[$j] = @$x[$i + $j];
				else
					$w[$j] = sha1_rol($w[$j - 3] ^ $w[$j - 8] ^ $w[$j - 14] ^ $w[$j - 16], 1);

				$t = sha1_rol($a, 5) + sha1_ft($j, $b, $c, $d) + $e + $w[$j] + sha1_kt($j);
				$e = $d;
				$d = $c;
				$c = sha1_rol($b, 30);
				$b = $a;
				$a = $t;
			}

			$a += $olda;
			$b += $oldb;
			$c += $oldc;
			$d += $oldd;
			$e += $olde;
		}

		return dechex($a) . dechex($b) . dechex($c) . dechex($d) . dechex($e);
	}

	function sha1_ft($t, $b, $c, $d)
	{
		if ($t < 20)
			return ($b & $c) | ((~$b) & $d);
		if ($t < 40)
			return $b ^ $c ^ $d;
		if ($t < 60)
			return ($b & $c) | ($b & $d) | ($c & $d);

		return $b ^ $c ^ $d;
	}

	function sha1_kt($t)
	{
		return $t < 20 ? 1518500249 : ($t < 40 ? 1859775393 : ($t < 60 ? -1894007588 : -899497514));
	}

	function sha1_rol($num, $cnt)
	{
		$z = hexdec(80000000);
		if ($z & $num)
			$a = ($num >> 1 & (~$z | 0x40000000)) >> (31 - $cnt);
		else
			$a = $num >> (32 - $cnt);

		return ($num << $cnt) | $a;
	}
}

function load_language_data()
{
	global $txt;

	$txt['smf_installer'] = 'SMF Installer';
	$txt['error_message_click'] = 'Click here';
	$txt['error_message_try_again'] = 'to try this step again.';
	$txt['error_message_bad_try_again'] = 'to try installing anyway, but note that this is <em>strongly</em> discouraged.';
	$txt['error_php_too_low'] = 'Warning!  You do not appear to have a version of PHP installed on your webserver that meets SMF\'s <strong>minimum installations requirements</strong>.<br />If you are not the host, you will need to ask your host to upgrade, or use a different host - otherwise, please upgrade PHP to a recent version.<br /><br />If you know for a fact that your PHP version is high enough you may continue, although this is strongly discouraged.';
	$txt['error_session_save_path'] = 'Please inform your host that the <strong>session.save_path specified in php.ini</strong> is not valid!  It needs to be changed to a directory that <strong>exists</strong>, and is <strong>writable</strong> by the user PHP is running under.<br />';
	$txt['error_mysql_missing'] = 'The installer was unable to detect MySQL support in PHP.  Please ask your host to ensure that PHP was compiled with MySQL, or that the proper extension is being loaded.';
	$txt['error_not_right_path'] = 'Sorry, the FTP path you entered wasn\'t the same place as this installer was uploaded to.';
	$txt['error_unable_download'] = 'The installer was unable to download the archive (%1$s) from the server.  <a href="%1$s" target="_blank">Please try using the regular installer package instead.</a>';
	$txt['error_ftp_no_connect'] = 'Unable to connect to FTP server with this combination of details.';
	$txt['error_horrible_chmod'] = 'The installer couldn\'t find any way to write files to your server.  Please contact your server administrator or check your settings.';
	$txt['ftp_please_note'] = 'Before you proceed, please note that <strong>the contents of the directory this file is in may be overwritten</strong>.  This installer will check to make sure that the path you specify points to where this file is, but please be careful not to overwrite anything important!';
	$txt['ftp_login'] = 'Your FTP connection information';
	$txt['ftp_login_info'] = 'This web installer needs your FTP information in order to automate the installation for you.  Please note that none of this information is saved in your installation, it is just used to setup SMF.';
	$txt['ftp_server'] = 'Server';
	$txt['ftp_server_info'] = 'The address (often localhost) and port for your FTP server.';
	$txt['ftp_port'] = 'Port';
	$txt['ftp_username'] = 'Username';
	$txt['ftp_username_info'] = 'The username to login with. <em>This will not be saved anywhere.</em>';
	$txt['ftp_password'] = 'Password';
	$txt['ftp_password_info'] = 'The password to login with. <em>This will not be saved anywhere.</em>';
	$txt['ftp_path'] = 'Install Path';
	$txt['ftp_path_info'] = 'This is the <em>relative</em> path you use in your FTP client <a href="' . $_SERVER['PHP_SELF'] . '?ftphelp" onclick="window.open(this.href, \'\', \'width=450,height=250\');return false;" target="_blank">(more help)</a>.';
	$txt['ftp_path_found_info'] = 'The path in the box above was automatically detected.';
	$txt['ftp_path_help'] = 'Your FTP path is the path you see when you log in to your FTP client.  It commonly starts with &quot;<tt>www</tt>&quot;, &quot;<tt>public_html</tt>&quot;, or &quot;<tt>httpdocs</tt>&quot; - but it should include the directory SMF is in too, such as &quot;/public_html/forum&quot;.  It is different from your URL and full path.<br /><br />Files in this path may be overwritten, so make sure it\'s correct.';
	$txt['ftp_path_help_close'] = 'Close';
	$txt['ftp_connect'] = 'Connect';
	$txt['download_successful'] = 'Download successful';
	$txt['download_successful_info'] = 'The installation archive has been downloaded successfully.  Next, the files within it will be extracted to their destination.';
	$txt['continue'] = 'Continue';
	$txt['extraction_complete'] = 'Extraction complete!';
	$txt['extraction_complete_info'] = 'The download and extraction seemed to complete successfully.  Please click continue to finish the rest of the installation.';

	$txt['package_info'] = 'Package information';
	$txt['package_info_info'] = 'Here you can optionally select your package, languages, and other options.  If you log into your Simple Machines Community Forum account you will be able to install all packages available to you.';
	$txt['member_login'] = 'Simple Machines Community Forum member login';
	$txt['member_login_info'] = '<noscript>Please enable JavaScript.</noscript> (leave blank if you don\'t have a membership.)';
	$txt['member_username'] = 'Username';
	$txt['member_password'] = 'Password';
	$txt['member_verify'] = 'Verify';
	$txt['member_verify_done'] = 'Account verified.';
	$txt['member_verify_logout'] = 'logout';
	$txt['error_not_member'] = 'The username and password you provided were rejected.<br />Either you are not a member of Simple Machines Community Forum, your password is wrong, or you need to wait to try to login again.';
	$txt['package_info_version'] = 'Version to install';
	$txt['package_info_mirror'] = 'Mirror';
	$txt['package_info_languages'] = 'Additional languages';
	$txt['package_info_ready'] = 'Continue';

	$txt['read_the_license'] = 'Before you download and install SMF, please <a href="http://www.simplemachines.org/about/license.php" target="_blank">read the license</a>.  It contains important agreements in it.';
	$txt['read_the_license_done'] = 'I have read the license and agree to be bound by it.';
	$txt['error_read_the_license'] = 'Sorry, but unless you read and agree to the license, you cannot download and install SMF.';

	$txt['upgrade_process'] = 'Performing an upgrade';
	$txt['upgrade_process_info'] = 'The installer found an installation of SMF in this directory.  The package you select below will be upgraded over your current version if you continue.  If you want to install fresh, please empty this directory first.';

	$txt['yes'] = 'Yes';
	$txt['download_svn'] = 'Download the SVN version (latest \'nightly\')';

	$txt['source_theme_location_problem'] = 'It appears that your source file or theme file directory is not in the default location.  After the package file is downloaded and uncompressed you will need to manually move the files to the correct location.';

	$txt['not_done_yet'] = 'Not quite done yet!';

	$txt['download_paused'] = 'This downloading of the files has been paused to avoid overloading your server.  Don\'t worry, nothing\'s wrong - simply click the <label for="continue">continue button</label> below to keep going.';

	$txt['extraction_paused'] = 'This extraction of the files has been paused to avoid overloading your server.  Don\'t worry, nothing\'s wrong - simply click the <label for="continue">continue button</label> below to keep going.';

	$txt['extraction_progress'] = 'Extraction Progress';
	$txt['download_progress'] = 'Download Progress';

	$txt['cant_fetch_install_info'] = 'We are sorry but the installer was unable to download the installation package details from the Simple Machines website.  You may download the packages manually by using the <a href="http://download.simplemachines.org/">SMF Download</a> page.';

	$txt['chmod_desc'] = 'Some hosts require that PHP scripts not have a file permission of 777.  If you are on one of these hosts, or if you recieve an error code of 500 after the packages are downloaded and extracted, please change the file permission in the below field.  A common alternate value is 755.';
	$txt['chmod_header'] = 'File Permission';
}

// http://www.faqs.org/rfcs/rfc959.html
class ftp_connection
{
	var $hostname;
	var $connection = 'no_connection', $error = false, $last_message, $pasv = array();

	// Create a new FTP connection...
	function ftp_connection($ftp_server, $ftp_port = 21, $ftp_user = 'anonymous', $ftp_pass = 'ftpclient@simplemachines.org')
	{
		if ($ftp_server !== null)
			$this->connect($ftp_server, $ftp_port, $ftp_user, $ftp_pass);
	}

	function connect($ftp_server, $ftp_port = 21, $ftp_user = 'anonymous', $ftp_pass = 'ftpclient@simplemachines.org')
	{
		if (strpos($ftp_server, 'ftp://') === 0)
			$ftp_server = substr($ftp_server, 6);
		elseif (strpos($ftp_server, 'ftps://') === 0)
			$ftp_server = 'ssl://' . substr($ftp_server, 7);
		if (strpos($ftp_server, 'http://') === 0)
			$ftp_server = substr($ftp_server, 7);
		$ftp_server = strtr($ftp_server, array('/' => '', ':' => '', '@' => ''));

		// Connect to the FTP server.
		$this->hostname = $ftp_server;
		$this->connection = @fsockopen($ftp_server, $ftp_port, $err, $err, 5);
		if (!$this->connection)
		{
			$this->error = 'bad_server';
			return;
		}

		// Get the welcome message...
		if (!$this->expect_response(null, 220, 'bad_response'))
			return;

		// Send the username, it should ask for a password.
		if (!$this->expect_response('USER ' . $ftp_user, 331, 'bad_username'))
			return;

		// Now send the password... and hope it goes okay.
		if (!$this->expect_response('PASS ' . $ftp_pass, 230, 'bad_password'))
			return;
	}

	function chdir($ftp_path)
	{
		if (!is_resource($this->connection))
			return false;

		// No slash on the end, please...
		if (substr($ftp_path, -1) == '/')
			$ftp_path = substr($ftp_path, 0, -1);

		return $this->expect_response('CWD ' . $ftp_path, 250, 'bad_path');
	}

	function chmod($ftp_file, $chmod)
	{
		if (!is_resource($this->connection))
			return false;

		// Convert the chmod value from octal (0777) to text ("777").
		$command = 'SITE chmod ' . decoct($chmod) . ' ' . $ftp_file;
		return $this->expect_response($command, 200, 'bad_file');
	}

	function unlink($ftp_file)
	{
		// We are actually connected, right?
		if (!is_resource($this->connection))
			return false;

		// Delete file X.
		if (!$this->expect_response('DELE ' . $ftp_file, 250, null))
			return $this->expect_response('RMD ' . $ftp_file, 250, 'bad_file');

		return true;
	}

	function check_response($desired)
	{
		// Wait for a response that isn't continued with -, but don't wait too long.
		$time = time();
		do
			$this->last_message = fgets($this->connection, 1024);
		while (strpos($this->last_message, ' ') !== 3 && time() - $time < 5);

		// Was the desired response returned?
		return is_array($desired) ? in_array(substr($this->last_message, 0, 3), $desired) : substr($this->last_message, 0, 3) == $desired;
	}

	function expect_response($command, $desired, $error)
	{
		if ($command !== null)
			$this->send_command($command);

		if ($this->check_response($desired))
			return true;
		else
		{
			if ($error !== null)
				$this->error = $error;
			return false;
		}
	}

	function send_command($command)
	{
		fwrite($this->connection, $command . "\r\n");
	}

	function passive()
	{
		// We can't create a passive data connection without a primary one first being there.
		if (!is_resource($this->connection))
			return false;

		// Request a passive connection - this means, we'll talk to you, you don't talk to us.
		$this->send_command('PASV');

		$time = time();
		do
			$response = fgets($this->connection, 1024);
		while (strpos($response, ' ') !== 3 && time() - $time < 5);

		// If it's not 227, we weren't given an IP and port, which means it failed.
		if (strpos($response, '227 ') !== 0)
		{
			$this->error = 'bad_response';
			return false;
		}

		// Snatch the IP and port information, or die horribly trying...
		if (preg_match('~\((\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))\)~', $response, $match) == 0)
		{
			$this->error = 'bad_response';
			return false;
		}

		// This is pretty simple - store it for later use ;).
		$this->pasv = array('ip' => $match[1] . '.' . $match[2] . '.' . $match[3] . '.' . $match[4], 'port' => $match[5] * 256 + $match[6]);

		return true;
	}

	function connect_passive()
	{
		// If we connected to localhost, but we got a real IP back from the server... it may be "too smart."
		if ($this->hostname == 'localhost')
		{
			// Note, we're just ignoring the ip it gave us (since we can't go outside -> inside the firewall.)
			$fp = @fsockopen('localhost', $this->pasv['port'], $err, $err, 2);
			if ($fp)
				return $fp;
		}

		// We try to connect now on the ip and port the server told us too.
		$fp = @fsockopen($this->pasv['ip'], $this->pasv['port'], $err, $err, 5);
		if (!$fp)
			$this->error = 'bad_server';

		return $fp;
	}

	function create_file($ftp_file)
	{
		// First, we have to be connected... very important.
		if (!is_resource($this->connection))
			return false;

		// I'd like one passive mode, please!
		if (!$this->passive())
			return false;

		// Seems logical enough, so far...
		$this->send_command('STOR ' . $ftp_file);

		// Okay, now we connect to the data port.  If it doesn't work out, it's probably "file already exists", etc.
		$fp = $this->connect_passive();
		if (!$fp)
			return false;

		if (!$this->expect_response(null, 150, 'bad_file'))
		{
			fclose($fp);
			return false;
		}

		// This may look strange, but we're just closing it to indicate a zero-byte upload.
		fclose($fp);

		if (!$this->expect_response(null, 226, 'bad_response'))
			return false;

		return true;
	}

	function list_dir($ftp_path = '', $search = false)
	{
		// Are we even connected...?
		if (!is_resource($this->connection))
			return false;

		// Passive... non-agressive...
		if (!$this->passive())
			return false;

		// Get the listing!
		$this->send_command('LIST -1' . ($search ? 'R' : '') . ($ftp_path == '' ? '' : ' ' . $ftp_path));

		// Connect, assuming we've got a connection.
		$fp = $this->connect_passive();
		if (!$fp)
			return false;
		if (!$this->expect_response(null, array(150, 125), 'bad_response'))
		{
			fclose($fp);
			return false;
		}

		// Read in the file listing.
		$data = '';
		while (!feof($fp))
			$data .= fread($fp, 4096);;
		fclose($fp);

		// Everything go okay?
		if (!$this->expect_response(null, 226, 'bad_response'))
			return false;

		return $data;
	}

	function locate($file, $listing = null)
	{
		if ($listing === null)
			$listing = $this->list_dir('', true);
		$listing = explode("\n", $listing);

		$this->send_command('PWD');

		$time = time();
		do
			$response = fgets($this->connection, 1024);
		while (strpos($response, ' ') !== 3 && time() - $time < 5);

		// Check for 257!
		if (preg_match('~^257 "(.+?)" ~', $response, $match) != 0)
			$current_dir = strtr($match[1], array('""' => '"'));
		else
			$current_dir = '';

		for ($i = 0, $n = count($listing); $i < $n; $i++)
		{
			if (trim($listing[$i]) == '' && isset($listing[$i + 1]))
			{
				$current_dir = substr(trim($listing[++$i]), 0, -1);
				$i++;
			}

			// Okay, this file's name is:
			$listing[$i] = $current_dir . '/' . trim(strlen($listing[$i]) > 30 ? strrchr($listing[$i], ' ') : $listing[$i]);

			if ($file[0] == '.' && substr($listing[$i], -(strlen($file) - 1)) == substr($file, 1))
				return $listing[$i];
			if (substr($file, -1) == '*' && substr($listing[$i], 0, strlen($file) - 1) == substr($file, 0, -1))
				return $listing[$i];
			if (basename($listing[$i]) == $file || $listing[$i] == $file)
				return $listing[$i];
		}

		return false;
	}

	function create_dir($ftp_dir)
	{
		// We must be connected to the server to do something.
		if (!is_resource($this->connection))
			return false;

		// Make this new beautiful directory!
		return $this->expect_response('MKD ' . $ftp_dir, 257, 'bad_file');
	}

	function detect_path($filesystem_path, $lookup_file = null)
	{
		$username = '';

		if (isset($_SERVER['DOCUMENT_ROOT']))
		{
			if (preg_match('~^/home[2]?/([^/]+?)/public_html~', $_SERVER['DOCUMENT_ROOT'], $match))
			{
				$username = $match[1];

				$path = strtr($_SERVER['DOCUMENT_ROOT'], array('/home/' . $match[1] . '/' => '', '/home2/' . $match[1] . '/' => ''));

				if (substr($path, -1) == '/')
					$path = substr($path, 0, -1);

				if (strlen(dirname($_SERVER['PHP_SELF'])) > 1)
					$path .= dirname($_SERVER['PHP_SELF']);
			}
			elseif (strpos($filesystem_path, '/var/www/') === 0)
				$path = substr($filesystem_path, 8);
			else
				$path = strtr(strtr($filesystem_path, array('\\' => '/')), array($_SERVER['DOCUMENT_ROOT'] => ''));
		}
		else
			$path = '';

		if (is_resource($this->connection) && $this->list_dir($path) == '')
		{
			$data = $this->list_dir('', true);

			if ($lookup_file === null)
				$lookup_file = $_SERVER['PHP_SELF'];

			$found_path = dirname($this->locate('*' . basename(dirname($lookup_file)) . '/' . basename($lookup_file), $data));
			if ($found_path == false)
				$found_path = dirname($this->locate(basename($lookup_file)));
			if ($found_path != false)
				$path = $found_path;
		}
		elseif (is_resource($this->connection))
			$found_path = true;

		return array($username, $path, isset($found_path));
	}

	function close()
	{
		// Goodbye!
		$this->send_command('QUIT');
		fclose($this->connection);

		return true;
	}
}
