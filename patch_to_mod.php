<?php
/**
 * Patch to Mod
 *
 * This script should convert patches (at the moment git generated diffs)
 * to fully functional mods for SMF
 * To set up the script:
 *  - put it into the same directory as a working SMF
 *  - set the variable $create_path to an absolute path writable by the 
 *    script (the package will be saved there, remember to delete it)
 *  - point the browser to http://yourdomain.tld/forum/patch_to_mod.php
 *  - enjoy! :P
 *
 * @package PtM
 * @author emanuele
 * @copyright 2012 emanuele, Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 0.1.0
 */

$txt['PtM_menu'] = 'Mod creation script';
$txt['add_a_file'] = 'Add a file';
$txt['add_new_space'] = 'Add new space';
$txt['path_not_writable'] = 'The configured path is not writable or doesn\'t exist';
$txt['cannot_create_package'] = 'Cannot create the zip package';
$txt['package_creation_failed'] = 'Package creation failed with the following code: %1$s';
$txt['package_not_found'] = 'Cannot find the package you are looking for';
$txt['board_dir'] = '$boarddir';
$txt['source_dir'] = '$sourcedir';
$txt['theme_dir'] = '$themedir';
$txt['language_dir'] = '$languagedir';
$txt['image_dir'] = '$imagesdir';
$txt['is_code'] = 'Run code (install+uninstall)';
$txt['is_code_unin'] = 'Run code (uninstall-only)';
$txt['is_database'] = 'Run database code (install-only)';

$create_path = '';

// ---------------------------------------------------------------------------------------------------------------------
define('SMF_INTEGRATION_SETTINGS', serialize(array(
	'integrate_menu_buttons' => 'create_menu_button',)));

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	exit('<b>Error:</b> please verify you put this in the same place as SMF\'s SSI.php.');

if (SMF == 'SSI')
{
	// Let's start the main job
	create_mod();
	// and then let's throw out the template! :P
	obExit(null, null, true);
}

function create_mod()
{
	global $context, $smcFunc, $boardurl, $create_path, $txt;

	// Guess is fun
	if (empty($create_path))
		$create_path = dirname(__FILE__) . '/Packages/create';

	if (isset($_REQUEST['download']))
	{
		$file_name = basename($_REQUEST['download']);
		$file_path = $create_path . '/' . $file_name . '/' . $file_name . '.zip';
		if (!file_exists($file_path))
			fatal_error($txt['package_not_found'], false);

		$file_name = $file_name . '.zip';

		ob_end_clean();
		header('Pragma: ');
		if (!$context['browser']['is_gecko'])
			header('Content-Transfer-Encoding: binary');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 525600 * 60) . ' GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file_path)) . ' GMT');
		header('Accept-Ranges: bytes');
		header('Connection: close');
		header('Content-type: application/zip');

		// Convert the file to UTF-8, cuz most browsers dig that.
		$utf8name = !$context['utf8'] && function_exists('iconv') ? iconv($context['character_set'], 'UTF-8', $file_name) : (!$context['utf8'] && function_exists('mb_convert_encoding') ? mb_convert_encoding($file_name, 'UTF-8', $context['character_set']) : $file_name);
		$fixchar = create_function('$n', '
			if ($n < 32)
				return \'\';
			elseif ($n < 128)
				return chr($n);
			elseif ($n < 2048)
				return chr(192 | $n >> 6) . chr(128 | $n & 63);
			elseif ($n < 65536)
				return chr(224 | $n >> 12) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);
			else
				return chr(240 | $n >> 18) . chr(128 | $n >> 12 & 63) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);');

		if ($context['browser']['is_firefox'])
			header('Content-Disposition: attachment; filename*="UTF-8\'\'' . preg_replace('~&#(\d{3,8});~e', '$fixchar(\'$1\')', $utf8name) . '"');
		elseif ($context['browser']['is_opera'])
			header('Content-Disposition: attachment; filename="' . preg_replace('~&#(\d{3,8});~e', '$fixchar(\'$1\')', $utf8name) . '"');
		elseif ($context['browser']['is_ie'])
			header('Content-Disposition: attachment; filename="' . urlencode(preg_replace('~&#(\d{3,8});~e', '$fixchar(\'$1\')', $utf8name)) . '"');
		else
			header('Content-Disposition: attachment; filename="' . $utf8name . '"');

		header('Cache-Control: no-cache');

		header('Content-Length: ' . filesize($file_path));

		// Try to buy some time...
		@set_time_limit(600);

		// Forcibly end any output buffering going on.
		if (function_exists('ob_get_level'))
		{
			while (@ob_get_level() > 0)
				@ob_end_clean();
		}
		else
		{
			@ob_end_clean();
			@ob_end_clean();
			@ob_end_clean();
		}

		$fp = fopen($file_path, 'rb');
		while (!feof($fp))
		{
			if (isset($callback))
				echo $callback(fread($fp, 8192));
			else
				echo fread($fp, 8192);
			flush();
		}
		fclose($fp);

		obExit(false);
// 		redirectexit($boardurl . '/patch_to_mod.php');
	}

	$context['mod_patch'] = isset($_FILES['mod_patch']) && empty($_FILES['mod_patch']['error']);
	$context['mod_name'] = !empty($_POST['mod_name']) ? $smcFunc['htmlspecialchars']($_POST['mod_name']) : '';
	$context['mod_author'] = !empty($_POST['mod_author']) ? $smcFunc['htmlspecialchars']($_POST['mod_author']) : '';
	$context['mod_version'] = !empty($_POST['mod_version']) ? $smcFunc['htmlspecialchars']($_POST['mod_version']) : '';
	$context['mod_smf_version'] = !empty($_POST['mod_smf_version']) ? (int) $_POST['mod_smf_version'] : '';

	$context['smf_versions'] = array(
		1 => 'SMF 1.0.x',
		2 => 'SMF 1.1.x',
		3 => 'SMF 2.0.x',
		4 => 'SMF 2.1.x',
	);
	$context['mod_smf_version'] = isset($context['smf_versions'][$context['mod_smf_version']]) ? $context['mod_smf_version'] : 0;
	if (!empty($context['mod_smf_version']))
		$context['mod_smf_selected_version'] = $context['smf_versions'][$context['mod_smf_version']];

	$context['mod_current_file'] = $context['mod_patch'] ? $_FILES['mod_patch']['tmp_name'] : '';

	$context['sub_template'] = 'create_script';
	$context['page_title_html_safe'] = 'Path to Mod script';

	$context['html_headers'] .= '
	<style type="text/css">
		dt {
			margin: 0px 0px 0px 0.5em;
			padding: 0.2em;
			clear: both;
			float: left;
			width: 25%;
		}
		dd {
			padding: 0.2em;
			float: left;
			width: 90%;
		}
	</style>';

	if (!empty($context['mod_patch']) && !empty($context['mod_name']) && !empty($context['mod_author']) && !empty($context['mod_version']) && !empty($context['mod_smf_version']))
		do_create();
}

function do_create()
{
	global $context, $create_path, $txt, $sourcedir, $boardurl;

	if (!file_exists($create_path))
		@mkdir($create_path);
	if (!file_exists($create_path) || !is_writable($create_path))
		fatal_error($txt['path_not_writable'], false);
	$context['clean_name'] = htmlspecialchars(str_replace(array(' ', ',', ':', '.', ';', '#', '@', '='), array('_'), $context['mod_name']));
	$current_path = $create_path . '/' . $context['clean_name'];

	// Let's start fresh everytime
	if (file_exists($current_path))
	{
		require_once($sourcedir . '/Subs-Package.php');
		deltree($current_path);
	}

	@mkdir($current_path);
	if (!file_exists($current_path) || !is_writable($current_path))
		fatal_error($txt['path_not_writable'], false);
	$context['current_path'] = $current_path;

	if (!prepare_files())
		return;
	create_mod_xml();
	create_package_xml();
	// Everything seems fine, now it's time to package everything
	create_package();
	$context['creation_done'] = true;
	$context['download_url'] = $boardurl . '/patch_to_mod.php?download=' . $context['clean_name'];
}

function create_package()
{
	global $context, $txt;


	$zip_package = $context['current_path'] . '/' . $context['clean_name'] . '.zip';

	if (file_exists($zip_package))
		@unlink($zip_package);
	if (file_exists($zip_package))
		fatal_error($txt['cannot_create_package'], false);

	$zip = new ZipArchive();
	$error = $zip->open($zip_package, ZIPARCHIVE::CREATE);
	if ($error !== true)
		fatal_error(sprintf($txt['package_creation_failed'], $error), false);

	if (!empty($context['up_files']))
		foreach ($context['up_files'] as $file)
			$zip->addFile($context['current_path'] . '/' . $file['name'], $file['name']);
	if (!empty($context['modifications']))
			$zip->addFile($context['current_path'] . '/modifications.xml', 'modifications.xml');
	if (!empty($context['up_files']) || !empty($context['modifications']))
			$zip->addFile($context['current_path'] . '/package-info.xml', 'package-info.xml');

	$zip->close();
}

function prepare_files()
{
	global $context, $sourcedir;

	$destinations = array(
		'source' => '$sourcedir',
		'board' => '$boarddir',
		'theme' => '$themedir',
		'language' => '$languagedir',
		'image' => '$imagesdir',
	);

	if (!empty($_FILES['mod_file']))
	{
		$context['up_files'] = array();
		foreach ($_FILES['mod_file']['name'] as $key => $file)
		{
			// Something wrong, stop here and go back to the upload screen
			if (!empty($_FILES['mod_patch']['error'][$key]))
				return false;

			// If no files are specified the array contains an empty item
			if (empty($_FILES['mod_file']['tmp_name'][$key]))
				continue;

			// That one goes into a subdir
			if (isset($_POST['mod_file_subdir'][$key]))
				$context['up_files'][$key]['sub_dir'] = $_POST['mod_file_subdir'][$key];
			// Let's see where this should go
			if (isset($_POST['mod_file_type'][$key]))
				$context['up_files'][$key]['type'] = $destinations[$_POST['mod_file_type'][$key]];
			// And finally where the file actually is and its name
			$context['up_files'][$key]['path'] = $_FILES['mod_file']['tmp_name'][$key];
			$context['up_files'][$key]['name'] = $_FILES['mod_file']['name'][$key];
		}

		// Let's not make things too complex for the moment: all the files go to the same location
		foreach ($context['up_files'] as $data)
			move_uploaded_file($data['path'], $context['current_path'] . '/' . $data['name']);
	}
	return true;
}

function create_package_xml()
{
	global $context;

	$context['install_for'] = htmlspecialchars(substr($context['mod_smf_selected_version'], 4, 3) . ' - ' . substr($context['mod_smf_selected_version'], 4, 3) . '.99');

	$write = '<?xml version="1.0"?>
<!DOCTYPE package-info SYSTEM "http://www.simplemachines.org/xml/package-info">
<package-info xmlns="http://www.simplemachines.org/xml/package-info" xmlns:smf="http://www.simplemachines.org/">
	<id>' . htmlspecialchars($context['mod_author']) . ':' . $context['clean_name'] . '</id>
	<name>' . htmlspecialchars($context['mod_name']) . '</name>
	<version>' . htmlspecialchars($context['mod_version']) . '</version>
	<type>modification</type>';

	foreach (array('install', 'uninstall') as $action)
	{
		$write .= '
	<' . $action . ' for="' . $context['install_for'] . '">';
		if (!empty($context['up_files']))
		{
			foreach ($context['up_files'] as $upfile)
			{
				if ($upfile['type'] == 'code' || ($upfile['type'] == 'code_unin' && $action == 'uninstall'))
					$write .= '
		<code>' . $upfile['name'] . '</code>';
				elseif ($upfile['type'] == 'database' && $action == 'install')
					$write .= '
		<database>' . $upfile['name'] . '</database>';
				elseif ($action == 'install')
					$write .= '
		<require-file name="' . $upfile['name'] . '" destination="' . $upfile['type'] . (!empty($upfile['sub_dir']) ? '/' . $upfile['sub_dir'] : '') . '" />';
				elseif ($action == 'uninstall')
					$write .= '
		<remove-file name="' . $upfile['type'] . (!empty($upfile['sub_dir']) ? '/' . $upfile['sub_dir'] : '') . '/' . $upfile['name'] . '" />';
			}
		}
		if (!empty($context['modifications']))
			$write .= '
		<modification' . ($action == 'uninstall' ? ' reverse="true"' : '') . '>modifications.xml</modification>';
						$write .= '
	</' . $action . '>';
	}

	$write .= '
</package-info>';

	file_put_contents($context['current_path'] . '/package-info.xml', $write);
}

function create_mod_xml()
{
	global $context;

	$content = file_get_contents($context['mod_current_file']);
	$content = explode("\n", $content);
	$operations = array();
	$context['modifications'] = array();
	$counter = 0;
	$opCounter = 0;

	for ($i = 0; $i < count($content); $i++)
	{
		if (string_starts_with($content[$i], '--- a/'))
		{
			$directory = substr($content[$i], 6, strpos($content[$i], '/', 7) - 6);
			if ($directory == 'Sources')
				$dir = '$sourcedir';
			elseif (strpos($content[$i], 'languages') !== false)
				$dir = '$languagedir';
			elseif (strpos($content[$i], 'images') !== false)
				$dir = '$imagesdir';
			elseif (strpos($content[$i], 'default/scripts') !== false)
				$dir = '$themedir/scripts';
			elseif ($directory == 'Themes')
				$dir = '$themedir';
			else
				$dir = '$boarddir';

			$context['modifications'][$counter]['path'] = $dir . '/' . basename($content[$i]);
			while (!string_starts_with($content[$i], '@@'))
				$i++;
			continue;
		}
		// The block of code is finished, let's create an <operation>
		if (
			(string_starts_with($content[$i], '@@')            // A new block
			|| string_starts_with($content[$i], 'diff --git')  // A new file
			|| !isset($content[$i + 1])                        // The end of the file
			) && !empty($operations))
		{
			$context['modifications'][$counter]['operations'][$opCounter]['search'] = str_replace(array('<![CDATA[', ']]>'), array('<![CDA\' . \'TA[', ']\' . \']>'), implode("\n", $operations['search']));
			$context['modifications'][$counter]['operations'][$opCounter]['replace'] = str_replace(array('<![CDATA[', ']]>'), array('<![CDA\' . \'TA[', ']\' . \']>'), implode("\n", $operations['replace']));
			// Reset things.
			$operations = array();
			$opCounter++;
			if (string_starts_with($content[$i], 'diff --git'))
			{
				$dir = '';
				$counter++;
			}
			continue;
		}
		if (!empty($dir))
		{
			if (string_starts_with($content[$i], ' '))
			{
				$operations['replace'][] = $operations['search'][] = substr($content[$i], 1);
			}
			if (string_starts_with($content[$i], '-'))
				$operations['search'][] = substr($content[$i], 1);
			elseif (string_starts_with($content[$i], '+'))
				$operations['replace'][] = substr($content[$i], 1);
		}
	}

	if (!empty($context['modifications']))
		write_mod_xml();
}

function write_mod_xml()
{
	global $context;

	$write = '<?xml version="1.0"?>
<!DOCTYPE modification SYSTEM "http://www.simplemachines.org/xml/modification">
<modification xmlns="http://www.simplemachines.org/xml/modification" xmlns:smf="http://www.simplemachines.org/">

	<id>' . htmlspecialchars($context['mod_author']) . ':' . $context['clean_name'] . '</id>
	<version>' . htmlspecialchars($context['mod_version']) . '</version>';

	foreach ($context['modifications'] as $file)
	{
		$write .= '
	<file name="' . $file['path'] . '">';

		foreach ($file['operations'] as $operations)
			$write .= '
		<operation>
			<search position="replace"><![CDATA[' .
				$operations['search'] . ']]></search>
			<add><![CDATA[' .
				$operations['replace'] . ']]></add>
		</operation>';

		$write .= '
	</file>';
	}

	$write .= '
</modification>';

	file_put_contents($context['current_path'] . '/modifications.xml', $write);
}

function string_starts_with($string, $star)
{
	return substr($string, 0, strlen($star)) == $star;
}

function create_menu_button(&$buttons)
{
	global $boardurl, $context, $txt;

	$context['sub_template'] = 'create_script';
	$context['current_action'] = 'create';

	$buttons['create'] = array(
		'title' => $txt['PtM_menu'],
		'show' => true,
		'href' => $boardurl . '/patch_to_mod.php',
		'active_button' => true,
		'sub_buttons' => array(
		),
	);
}

function template_create_script()
{
	global $boardurl, $context, $txt;

	echo '
	<div class="tborder">
		<div class="cat_bar">
			<h3 class="catbg">
				Welcome to the Patch to Mod script
				<div class="info">This procedure will guide you to the conversion of a patch file to a mod</div>
			</h3>
		</div>
		<span class="upperframe"><span></span></span>
		<div class="roundframe">';
	if (!isset($context['creation_done']))
	{
		echo '
			<form action="', $boardurl, '/patch_to_mod.php?action=create" method="post" accept-charset="', $context['character_set'], '" name="file_upload" id="file_upload" class="flow_hidden" enctype="multipart/form-data">
				<dl>
					<dt', empty($context['mod_patch']) ? ' class="error"' : '', '>
						<strong>Please select the patch file:</strong>
					</dt>
					<dd>
						<input type="file" size="40" name="mod_patch" id="mod_patch" class="input_file" /> (<a href="javascript:void(0);" onclick="cleanFileInput(\'mod_patch\');">Remove file</a>)
					</dd>
					<dt', empty($context['mod_name']) ? ' class="error"' : '', '>
						<strong><label for="mod_name">Mod name:</label></strong>
					</dt>
					<dd>
						<input type="text" name="mod_name" id="mod_name" value="', $context['mod_name'], '" size="40" maxlength="60" class="input_text" />
					</dd>
					<dt', empty($context['mod_author']) ? ' class="error"' : '', '>
						<strong><label for="mod_author">Author name:</label></strong>
					</dt>
					<dd>
						<input type="text" name="mod_author" id="mod_author" value="', $context['mod_author'], '" size="40" maxlength="60" class="input_text" />
					</dd>
					<dt', empty($context['mod_version']) ? ' class="error"' : '', '>
						<strong><label for="mod_version">Mod version:</label></strong>
					</dt>
					<dd>
						<input type="text" name="mod_version" id="mod_version" value="', $context['mod_version'], '" size="40" maxlength="60" class="input_text" />
					</dd>
					<dt', empty($context['mod_smf_version']) ? ' class="error"' : '', '>
						<strong><label for="mod_smf_version">Supported SMF version:</label></strong>
					</dt>
					<dd>
						<select name="mod_smf_version" id="mod_smf_version">';
		foreach ($context['smf_versions'] as $key => $val)
			echo '
							<option value="', $key, '"', $context['mod_smf_version'] == $key ? ' selected="selected"' : '', '>', $val, '</option>';
		echo'
						</select>
					</dd>
					<dd id="add_new_file"></dd>
				</dl>';

		echo '
				<script type="text/javascript"><!-- // --><![CDATA[
					var current_file = 0;
					add_new_file();

					function add_new_file()
					{
						var elem = document.getElementById(\'add_new_file\');
						current_file = current_file + 1;
						setOuterHTML(elem, ', JavaScriptEscape('
					<dt>
						<strong>' . $txt['add_a_file'] . ':</strong>
					</dt>
					<dd>
						<input type="file" size="20" name="mod_file[]" id="mod_file') . ' + current_file + ' . JavaScriptEscape('" class="input_file" /> (<a href="javascript:void(0);" onclick="cleanFieldInput(\'mod_file') . ' + current_file + ' . JavaScriptEscape('\');">Remove file</a>)
						<select name="mod_file_type[]" id="mod_file') . ' + current_file + ' . JavaScriptEscape('_select">
							<option value="source">' . $txt['source_dir'] . '</option>
							<option value="code">' . $txt['is_code'] . '</option>
							<option value="code_unin">' . $txt['is_code_unin'] . '</option>
							<option value="database">' . $txt['is_database'] . '</option>
							<option value="board">' . $txt['board_dir'] . '</option>
							<option value="theme">' . $txt['theme_dir'] . '</option>
							<option value="language">' . $txt['language_dir'] . '</option>
							<option value="image">' . $txt['image_dir'] . '</option>
						</select>
						<label for="mod_file') . ' + current_file + ' . JavaScriptEscape('_subdir">&nbsp;/&nbsp;</label><input type="text" name="mod_file_subdir[]" id="mod_file') . ' + current_file + ' . JavaScriptEscape('_subdir" value="" size="20" maxlength="10" class="input_text" />
					</dd>
					<dd id="add_new_file">[<a href="#" onclick="add_new_file();return false;">' . $txt['add_new_space'] . '</a>]</dd>'), ');
					}
					function cleanFieldInput(id)
					{
						cleanFileInput(id);
						document.getElementById(id + \'_select\')[0].selected = true;
						document.getElementById(id + \'_subdir\').value = \'\';
					}
				// ]]></script>';

		echo '
				<input type="submit" value="create" class="floatright button_submit" />
			</form>';
	}
	else
		echo '<strong>The package has been created successfully!</strong><br />
		You can download it from <a href="', $context['download_url'], '">here</a>';

	echo '
		</div>
		<span class="lowerframe"><span></span></span>
	</div>';
}
?>