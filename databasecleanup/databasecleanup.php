<?php
/**
* Simple Machines Forum (SMF)
*
* @package SMF
* @author Simple Machines
* @copyright 2011 Simple Machines
* @license http://www.simplemachines.org/about/smf/license.php BSD
*
* This file resets a database back to SMF default by removing rows / cols / tables / settigns added by mods
* http://www.simplemachines.org/community/index.php?topic=249192.0
*
*/

if (!file_exists(dirname(__FILE__) . '/SSI.php'))
	exit('Please verify you put this in the same place as SSI.php file.');
require_once(dirname(__FILE__) . '/SSI.php');

// Memory is always good
if (@ini_get('memory_limit') < 128)
	@ini_set('memory_limit', '128M');

// now get to work ...
file_source();
obExit(true);

/**
* file_source()
*
* - initialises all the basic context required for the database cleanup.
* - passes execution onto the relevant section.
* - if the passed action is not found it shows the main page.
*
* @return
*/
function file_source()
{
	global $smcFunc, $context, $table_prefix, $version;
	
	// You have to be allowed to do this
	isAllowedTo('admin_forum');
	
	// SMF 1 or 2 ?
	if (isset($smcFunc))
	{
		db_extend('packages');
		$version = 2;
		$table_prefix = '{db_prefix}';
	}
	else
	{
		db_compat();
		$version = 1;
		$table_prefix = '';
	}

	$actions = array(
		'examine' => 'examine',
		'execute' => 'execute',
		);

	$titles = array(
		'examine' => 'Examine Database',
		'execute' => 'Execute Changes',
		);

	// Set a default action if none or an unsupported one is given
	if (!isset($_GET['action']) || !isset($actions[$_GET['action']]))
		$current_action = 'examine';
	else
		$current_action = $actions[$_GET['action']];

	// Set up the template information and language
	loadtext();
	$context['sub_template'] = $current_action;
	$context['page_title'] = $titles[$current_action];
	$context['page_title_html_safe'] = $titles[$current_action];
	$context['robot_no_index'] = true;
	$context['html_headers'] .= '
	<style type="text/css">
		.normallist li
		{
			list-style: circle;
			line-height: 1.5em;
		}
		.submit_button
		{
			text-align: center;
		}
		.error
		{
			background-color: #FFECEC;
		}
		.success
		{
			color: #00CC00;
		}
		.fail
		{
			color: #EE0000;
		}
	</style>';

	$current_action();
}

/**
* examine()
*
* - Loads in the structure of a fresh SMF install via sql_to_array
* - Looks at each table in the current installation and determines if it is a default table
* and if so finds extra columns or indexes that should be removed.
*
* @return
*/
function examine()
{
	global $smcFunc, $db_prefix, $extra, $table_prefix, $version;
	
	// will allow for this == THIS and thisVar == this_var on col / index names to avoid false positives, set to true for more hits
	$strict_case = false;

	$tables = sql_to_array(dirname(__FILE__) . '/sql' . $version . '.sql');
	$mset = file_to_array(dirname(__FILE__) . '/modsettings' . $version . '.txt');
	$real_prefix = preg_match('~^(`?)(.+?)\\1\\.(.*?)$~', $db_prefix, $match) === 1 ? $match[3] : $db_prefix;
	$extra = array();

	// make sure this is gone for a clean run
	if (isset($_SESSION['db_cleaner']))
		unset($_SESSION['db_cleaner']);

	// examine each table in this installation
	$current_tables = $smcFunc['db_list_tables']();
	foreach ($current_tables as $table)
	{
		$table = preg_replace('~^' . $real_prefix . '~', '', $table);

		// Table itself does not exist in a fresh install
		if (!isset($tables[$table]))
		{
			$extra['tables'][] = $table;
			continue;
		}

		// It exists in a fresh install, lets check if there are any extra columns in it
		$current_columns = $smcFunc['db_list_columns']($table_prefix . $table, false);
		foreach ($current_columns as $column)
		if ((!isset($tables[$table]['columns'][$column])) && $strict_case)
			$extra['columns'][$table][] = $column;
		elseif (!$strict_case && !isset($tables[$table]['columns'][strtolower($column)]))
			$extra['columns'][$table][] = $column;
			
		// or extra indexes taking up space
		$current_indexes = $smcFunc['db_list_indexes']($table_prefix . $table, true);
		foreach ($current_indexes as $key => $index)
		{
			if (!isset($tables[$table]['indexes'][$key]))
			{
				// keys don't match ... v1 to v2 upgrade appears to reuse old smf1 index names (upper or camel) ..
				if ($version == 2 && $key != 'PRIMARY' && !$strict_case)
				{
					// lets see if a lowercase or camelCase version of the key will work
					$tempkey = (isset($tables[$table]['indexes'][strtolower($key)])) ? strtolower($key) : (isset($tables[$table]['indexes'][unCamelCase($key)]) ? unCamelCase($key) : '');
					if (!empty($tempkey))
					{
						// Probably found it, but are the index type and index cols exactly the same as well?
						if (($current_indexes[$key]['type'] != $tables[$table]['indexes'][$tempkey]['type']) || (count(array_diff($current_indexes[$key]['columns'], $tables[$table]['indexes'][$tempkey]['columns'])) != 0))
							unset($tempkey);
					}

					if (empty($tempkey))
						$extra['indexes'][$table][] = $index;
				}
				else
					$extra['indexes'][$table][] = $index;
			}
		}
	}

	// modSettings that are not from standard SMF
	$current_settings = array();
	$request = $smcFunc['db_query']('', '
		SELECT variable
		FROM {db_prefix}settings',
		array(
			)
		);

	while ($row = ($version == 1 ? mysql_fetch_row($request) : $smcFunc['db_fetch_row']($request)))
	$current_settings[$row[0]] = $row[0];
	($version == 1) ? mysql_free_result($request) : $smcFunc['db_free_result']($request);

	// check what could be there vs what it ;)
	foreach ($current_settings as $mod)
	{
		if (!isset($mset[$mod]))
		{
			// check for a multi settings like bbc or integration tags
			$submod = explode('_', $mod);
			if (isset($mset[$submod[0] . '_']) && (strpos($mod, $mset[$submod[0] . '_']) == 0))
				continue;
			$extra['settings'][] = $mod;
		}
	}
}

/**
* execute()
*
* @return
*/
function execute()
{
	global $smcFunc, $execute, $table_prefix;

	checkSession();

	if (empty($_POST['agree']) || empty($_POST['submit_ok']))
		fatal_error('How did you get here?', false);

	// how many actioins to do on each loop
	$chunk = 5;

	// Only do these steps if we have not already started our loop
	if (!isset($_SESSION['db_cleaner']))
	{
		// init
		$execute = array(
			'tables' => array(),
			'columns' => array(),
			'indexes' => array(),
			'settings' => array(),
			'results' => array(),
			'actions' => array(),
			);

		// build our to do arrays based on what the user selected.
		if (!empty($_POST['columns']))
		{
			foreach ($_POST['columns'] as $table_name => $table)
			{
				if (!preg_match('~^[A-Za-z0-9_]+$~', $table_name))
					continue;

				foreach ($table as $column)
				{
					if (preg_match('~^[A-Za-z0-9_]+$~', $column))
						$execute['columns'][$table_name][] = $column;
				}
			}
		}

		if (!empty($_POST['indexes']))
		{
			foreach ($_POST['indexes'] as $table_name => $table)
			{
				if (!preg_match('~^[A-Za-z0-9_]+$~', $table_name))
					continue;

				foreach ($table as $index)
				{
					if (preg_match('~^[A-Za-z0-9_]+$~', $index))
						$execute['indexes'][$table_name][] = $index;
				}
			}
		}

		if (!empty($_POST['tables']))
		{
			foreach ($_POST['tables'] as $table)
			{
				if (preg_match('~^[A-Za-z0-9_]+$~', $table))
					$execute['tables'][] = $table;
			}
		}

		if (!empty($_POST['settings']))
		{
			foreach ($_POST['settings'] as $table)
			{
				if (preg_match('~^[A-Za-z0-9_]+$~', $table))
					$execute['settings'][] = $table;
			}
		}

		// Step 1: build the remove data for extra index's
		foreach ($execute['indexes'] as $table_name => $table)
		{
			foreach ($table as $index)
			$execute['actions'][] = array(
				'prefix' => $table_prefix,
				'table' => $table_name,
				'index' => $index
				);
		}

		// Step 2: build the remove data for extra columns
		foreach ($execute['columns'] as $table_name => $table)
		{
			foreach ($table as $column)
			$execute['actions'][] = array(
				'prefix' => $table_prefix,
				'table' => $table_name,
				'column' => $column
				);
		}

		// Step 3: build the drop data for tables
		foreach ($execute['tables'] as $table)
		$execute['actions'][] = array(
			'prefix' => $table_prefix,
			'table' => $table
			);

		// Step 4: All those unused settings
		$execute['actions'][] = array(
			'prefix' => $table_prefix,
			'settings' => $execute['settings']
			);

		// save the data for future generations
		$_SESSION['db_cleaner']['execute'] = $execute;
		$_SESSION['db_cleaner']['work'] = count($execute['actions']);
		$_SESSION['db_cleaner']['done'] = 0;
	}

	// load up where we are on this pass
	$execute = $_SESSION['db_cleaner']['execute'];
	$done = $_SESSION['db_cleaner']['done'];
	$work = $_SESSION['db_cleaner']['work'];

	// Do some database work, but not to much :)
	$this_loop = 0;
	$done = (isset($_SESSION['db_cleaner']['done'])) ? $_SESSION['db_cleaner']['done'] : 0;
	for ($i = $done; ($i < $work && $this_loop < $chunk); $i++, $this_loop++)
	{
		// lazy and don't want to type the whole thing in :)
		$todo = $execute['actions'][$i];

		// remove index, then columns, then tables
		if (isset($todo['index']))
			$execute['results']['index'][$todo['table']][$todo['index']] = $smcFunc['db_remove_index']($todo['prefix'] . $todo['table'], $todo['index']);
		if (isset($todo['column']))
			$execute['results']['column'][$todo['table']][$todo['column']] = $smcFunc['db_remove_column']($todo['prefix'] . $todo['table'], $todo['column']);
		if (isset($todo['table']))
			$execute['results']['table'][$todo['table']] = $smcFunc['db_drop_table']($todo['prefix'] . $todo['table']);
		if (isset($todo['settings']) && count($todo['settings']) != 0)
		{
			global $modSettings;
			
			// Settings, do these as a 'single' step, first remove them from memory
			foreach ($todo['settings'] as $setting)
			if (isset($modSettings[$setting]))
				unset($modSettings[$setting]);

			// And now from sight
			$execute['results']['settings'] = $smcFunc['db_query']('', 'DELETE FROM ' . $todo['prefix'] . 'settings WHERE variable IN ({array_string:variables})', array('variables' => $todo['settings']));

			// And let SMF know we have been mucking about
			updateSettings(array('settings_updated' => time()));
		}
	}

	// Are we done yet?? ... if not go round again
	$_SESSION['db_cleaner']['execute'] = $execute;
	if ($i < $work)
		nextStep('db_cleaner', $i);
	else
		unset($_SESSION['db_cleaner']);
}

/**
* sql_to_array()
*
* - reads in an default SMF SQL file either v1 or v2
* - creates arrays to represent all tables, cols and indexes
* - used in examine function to deterime whats not standard
*
* @param mixed $file
* @return
*/
function sql_to_array($file)
{
	global $db_prefix, $txt;

	if (!file_exists($file))
		fatal_error('Could not locate SQL file.', false);

	$sql_lines = explode("\n", file_get_contents($file));

	if (empty($sql_lines))
		fatal_error('Could not process SQL file.', false);

	$tables = array();
	$table_names = array();
	$table_name = '';

	// go line by line through this smf database definiton
	foreach ($sql_lines as $line)
	{
		$line = trim($line);

		// New table line, lets create a new array with this tables name
		if (preg_match('~^CREATE TABLE `?(?:\{\$db_prefix\}|[A-Za-z0-9_-]+`?\.`?)?([A-Za-z0-9_]+)`?\s*\($~i', $line, $matches))
		{
			$table_names[] = $table_name = $matches[1];
			$tables[$table_name]['indexes'] = array();
			$tables[$table_name]['columns'] = array();

			continue;
		}

		// column definiton for a table ...
		preg_match_all('~^`?([A-Za-z0-9_]+)`? ([A-Za-z0-9\(\)]+)\s*(unsigned)?\s*(NOT NULL)?\s*(default \'?([A-Za-z0-9-,_ ])*\'?)?(auto_increment)?(/\*.+\*/)?,?$~i', $line, $matches, PREG_SPLIT_DELIM_CAPTURE);
		if (!empty($matches[0]))
		{
			$column_info = $matches[0];
			preg_match('~([a-z]+)(\(([0-9]+)\))?~i', $column_info[2], $type);

			$tables[$table_name]['columns'][$column_info[1]] = array(
				'name' => $column_info[1],
				'null' => empty($column_info[4]),
				'default' => isset($column_info[6]) ? $column_info[6] : '',
				'type' => $type[1],
				'size' => !empty($type[3]) ? $type[3] : '',
				'auto' => !empty($column_info[7]),
				'unsigned' => !empty($column_info[3]),
				);

			continue;
		}

		// index defintion for a table
		preg_match_all('~^(PRIMARY KEY|KEY|UNIQUE|UNIQUE KEY|INDEX)\s*`?([A-Za-z0-9_]*)`?\s*\((.+)\),?$~i', $line, $matches, PREG_SPLIT_DELIM_CAPTURE);
		if (!empty($matches[0]))
		{
			$index_info = $matches[0];
			$index_columns = explode(', ', $index_info[3]);
			$index_types = array(
				'PRIMARY KEY' => 'primary',
				'UNIQUE' => 'unique',
				'UNIQUE KEY' => 'unique',
				'UNIQUE INDEX' => 'unique',
				'KEY' => 'index',
				'INDEX' => 'index',
				);

			$tables[$table_name]['indexes'][$index_info[1] == 'PRIMARY KEY' ? 'PRIMARY' : $index_info[2]] = array(
				'name' => $index_info[1] == 'PRIMARY KEY' ? 'PRIMARY' : $index_info[2],
				'type' => $index_types[$index_info[1]],
				'columns' => $index_columns,
				);

			continue;
		}
	}

	// Nothing found?
	if (empty($tables))
		fatal_error($txt['no_sql_file'], false);

	return $tables;
}

/**
* db_compat()
*
* - creates a compatable smcFunc array for smf 1
* - uses smf1_db_list_tables, smf1_db_list_columns, smf1_db_list_indexes, smf1_db_drop_table, smf1_db_remove_column, 
* smf1_db_remove_index smf1_db_query & smf1_db_replacement__callback which provide the approriate mysql functions for the array.
*
* @return
*/
function db_compat()
{
	global $smcFunc, $db_prefix, $reservedTables;

	// Set up an smcFunc array with SMF1 database commands
	$smcFunc = array();
	$smcFunc += array(
		'db_list_tables' => 'smf1_db_list_tables',
		'db_list_columns' => 'smf1_db_list_columns',
		'db_list_indexes' => 'smf1_db_list_indexes',
		'db_drop_table' => 'smf1_db_drop_table',
		'db_remove_column' => 'smf1_db_remove_column',
		'db_remove_index' => 'smf1_db_remove_index',
		'db_query' => 'smf1_db_query',
		);

	$reservedTables = array('attachments', 'ban_groups', 'ban_items', 'board_permissions',
		'boards', 'calendar', 'calendar_holidays', 'categories', 'collapsed_categories',
		'log_actions', 'log_activity', 'log_banned', 'log_boards', 'log_errors',
		'log_floodcontrol', 'log_karma', 'log_mark_read', 'log_notify', 'log_online',
		'log_polls', 'log_search_messages', 'log_search_results', 'log_search_subjects',
		'log_search_topics', 'log_topics', 'membergroups', 'members', 'message_icons',
		'messages', 'moderators', 'package_servers', 'permissions', 'personal_messages',
		'pm_recipients', 'poll_choices', 'polls', 'sessions', 'settings', 'smileys',
		'themes', 'topics');

	foreach ($reservedTables as $k => $table_name)
		$reservedTables[$k] = strtolower($db_prefix . $table_name);
}

/**
* smf1_db_list_tables()
*
* @return
*/
function smf1_db_list_tables()
{
	global $db_name;

	$db = $db_name;
	$db = trim($db);
	$db = $db{0} == '`' ? strtr($db, array('`' => '')) : $db;

	$request = db_query("
		SHOW TABLES
		FROM `$db`", __FILE__, __LINE__);
	$tables = array();
	while ($row = mysql_fetch_row($request))
	$tables[] = $row[0];
	mysql_free_result($request);

	return $tables;
}

/**
* smf1_db_list_columns()
*
* @param mixed $table_name
* @return
*/
function smf1_db_list_columns($table_name)
{
	global $db_prefix;

	if (strpos($table_name, $db_prefix) === false)
		$table_name = $db_prefix . $table_name;

	$table_name = substr($table_name, 0, 1) == '`' ? $table_name : '`' . $table_name . '`';

	$request = db_query("
		SHOW FIELDS
		FROM $table_name", __FILE__, __LINE__);
	$columns = array();
	while ($row = mysql_fetch_assoc($request))
	$columns[] = $row['Field'];
	mysql_free_result($request);

	return $columns;
}

/**
* smf1_db_list_indexes()
*
* @param mixed $table_name
* @param mixed $detail
* @return
*/
function smf1_db_list_indexes($table_name, $detail = false)
{
	global $smcFunc, $db_prefix;
	$indexes = array();

	if (strpos($table_name, $db_prefix) === false)
		$table_name = $db_prefix . $table_name;

	$table_name = substr($table_name, 0, 1) == '`' ? $table_name : '`' . $table_name . '`';

	$request = db_query("
		SHOW KEYS
		FROM $table_name", __FILE__, __LINE__);

	while ($row = mysql_fetch_assoc($request))
	{
		if (!$detail)
			$indexes[] = $row['Key_name'];
		else
		{
			// What is the type?
			if ($row['Key_name'] == 'PRIMARY')
				$type = 'primary';
			elseif (empty($row['Non_unique']))
				$type = 'unique';
			elseif (isset($row['Index_type']) && $row['Index_type'] == 'FULLTEXT')
				$type = 'fulltext';
			else
				$type = 'index';

			// This is the first column we've seen?
			if (empty($indexes[$row['Key_name']]))
			{
				$indexes[$row['Key_name']] = array(
					'name' => $row['Key_name'],
					'type' => $type,
					'columns' => array(),
					);
			}

			// Is it a partial index?
			if (!empty($row['Sub_part']))
				$indexes[$row['Key_name']]['columns'][] = $row['Column_name'] . '(' . $row['Sub_part'] . ')';
			else
				$indexes[$row['Key_name']]['columns'][] = $row['Column_name'];
		}
	}
	mysql_free_result($request);

	return $indexes;
}

/**
* smf1_db_drop_table()
*
* @param mixed $table_name
* @return
*/
function smf1_db_drop_table($table_name)
{
	global $reservedTables, $smcFunc, $db_prefix;

	$real_prefix = preg_match('~^(`?)(.+?)\\1\\.(.*?)$~', $db_prefix, $match) === 1 ? $match[3] : $db_prefix;

	$complete_table_name = $db_prefix . $table_name;
	$full_table_name = $real_prefix . $table_name;

	if (in_array(strtolower($complete_table_name), $reservedTables))
		return false;

	if (in_array($full_table_name, $smcFunc['db_list_tables']()))
	{
		db_query("
			DROP TABLE $complete_table_name", __FILE__, __LINE__);

		return true;
	}

	return false;
}

/**
* smf1_db_remove_column()
*
* @param mixed $table_name
* @param mixed $column_name
* @return
*/
function smf1_db_remove_column($table_name, $column_name)
{
	global $smcFunc, $db_prefix;

	if (strpos($table_name, $db_prefix) === false)
		$table_name = $db_prefix . $table_name;

	$columns = $smcFunc['db_list_columns']($table_name);
	if (in_array($column_name, $columns))
	{
		db_query("
			ALTER TABLE $table_name
			DROP COLUMN $column_name", __FILE__, __LINE__);

		return true;
	}

	return false;
}

/**
* smf1_db_remove_index()
*
* @param mixed $table_name
* @param mixed $index_name
* @return
*/
function smf1_db_remove_index($table_name, $index_name)
{
	global $smcFunc, $db_prefix;

	if (strpos($table_name, $db_prefix) === false)
		$table_name = $db_prefix . $table_name;

	$indexes = $smcFunc['db_list_indexes']($table_name);

	foreach ($indexes as $index)
	{
		if ($index == 'PRIMARY' && $index_name == 'PRIMARY')
		{
			db_query("
				ALTER TABLE $table_name
				DROP PRIMARY KEY", __FILE__, __LINE__);

			return true;
		}

		if ($index == $index_name)
		{
			db_query("
				ALTER TABLE $table_name
				DROP INDEX $index_name", __FILE__, __LINE__);

			return true;
		}
	}

	return false;
}

/**
* smf1_db_query()
*
* @param mixed $identifier
* @param mixed $db_string
* @param array $db_values
* @return
*/
function smf1_db_query($identifier, $db_string, $db_values = array())
{
	global $db_prefix, $db_connection;

	// if there's something to replace we better do it :)
	if (strpos($db_string, '{') !== false)
	{
		// This is needed by the callback function.
		$db_callback = array($db_values, $db_connection);

		// Do the swap out ....
		$db_string = preg_replace_callback('~{([a-z_]+)(?::([a-zA-Z0-9_-]+))?}~', 'smf1_db_replacement__callback', $db_string);

		// Clear this global variable.
		$db_callback = array();
	}

	return mysql_query($db_string, $db_connection);
}

/**
* smf1_db_replacement__callback()
*
* @param mixed $matches
* @return
*/
function smf1_db_replacement__callback($matches)
{
	global $db_callback, $db_prefix;

	// This my friends is a q&d of db_replacement__callback for just what THIS tool needs and NOTHING more
	list ($values, $db_connection) = $db_callback;
	$replacement = $values[$matches[2]];

	// Only doing two things for now ....
	switch ($matches[1])
	{
		case 'db_prefix':
			return $db_prefix;
		case 'array_string':
			if (is_array($replacement))
			{
				foreach ($replacement as $key => $value)
				$replacement[$key] = sprintf('\'%1$s\'', mysql_real_escape_string($value, $db_connection));
				return implode(', ', $replacement);
			}
			break;
		default:
			return $replacement;
			break;
	}
}

/**
* template_examine()
*
* displays the results of our current to standard comparision
* allows selection of non standard tables, cols and indexes and settings
*
* @return
*/
function template_examine()
{
	global $context, $extra, $txt;

	echo '
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	function check_agree()
	{
		document.forms.cleanup.submit_ok.disabled = !document.forms.cleanup.agree.checked;
		if (document.forms.cleanup.submit_ok.disabled) {
			document.forms.cleanup.submit_ok.style.background="#BBBBBB";
			document.forms.cleanup.submit_ok.style.cursor="none";
		}
		else {
			document.forms.cleanup.submit_ok.style.background="#cde7ff";
			document.forms.cleanup.submit_ok.style.cursor="pointer";
		}
		setTimeout("check_agree();", 100);
	}
	setTimeout("check_agree();", 100);
// ]]></script>

<form action="?action=execute" method="post" accept-charset="', $context['character_set'], '" name="cleanup" id="cleanup">

	<div class="cat_bar">
		<h3 class="catbg ">Examine Database</h3>
	</div>


	<div class="title_bar">
		<h4 class="titlebg"><input type="checkbox" onclick="invertAll(this, this.form, \'tables\');" />', $txt['extra_tables'], '</h4>
	</div>
	<div class="windowbg2">
		<span class="topslice"><span></span></span>
		<div class="content">
			<ul class="normallist">';

	if (!empty($extra['tables']))
	{
		foreach ($extra['tables'] as $table)
			echo '
				<li><input type="checkbox" name="tables[]" value="', $table, '" /> ', $table, '</li>';
	}
	else
		echo '
				<li>', $txt['no_extra_tables'], '</li>';

	echo '
			</ul>
		</div>
		<span class="botslice"><span></span></span>
	</div>


	<div class="title_bar">
		<h4 class="titlebg"><input type="checkbox" onclick="invertAll(this, this.form, \'columns\');" />', $txt['extra_columns'], '</h4>
	</div>
	<div class="windowbg">
		<span class="topslice"><span></span></span>
		<div class="content">
			<ul class="normallist">';

	if (!empty($extra['columns']))
	{
		foreach ($extra['columns'] as $table_name => $table)
		{
			echo '
				<li><input type="checkbox" onclick="invertAll(this, this.form, \'columns[', $table_name, '][]\');" /> ', $table_name, '
					<ul class="normallist">';

			foreach ($table as $column)
			echo '
						<li><input type="checkbox" name="columns[', $table_name, '][]" value="', $column, '" /> ', $column, '</li>';

			echo '
					</ul>
				</li>';
		}
	}
	else
		echo '
				<li>', $txt['no_extra_columns'], '</li>';

	echo '
			</ul>
		</div>
		<span class="botslice"><span></span></span>
	</div>


	<div class="title_bar">
		<h4 class="titlebg"><input type="checkbox" onclick="invertAll(this, this.form, \'indexes\');" />', $txt['extra_indexes'], '</h4>
	</div>
	<div class="windowbg2">
		<span class="topslice"><span></span></span>
		<div class="content">
			<ul class="normallist">';

	if (!empty($extra['indexes']))
	{
		foreach ($extra['indexes'] as $table_name => $table)
		{
			echo '
				<li><input type="checkbox" onclick="invertAll(this, this.form, \'indexes[', $table_name, '][]\');" /> ', $table_name, '
					<ul class="normallist">';

			foreach ($table as $index)
				echo '
						<li><input type="checkbox" name="indexes[', $table_name, '][]" value="', $index['name'], '" /> ', $index['name'], '</li>';

			echo '
					</ul>
				</li>';
		}
	}
	else
		echo '
				<li>', $txt['no_extra_indexes'], '</li>';
	echo '
			</ul>
		</div>
	</div>

	<div class="title_bar">
		<h4 class="titlebg"><input type="checkbox" onclick="invertAll(this, this.form, \'settings\');" />', $txt['extra_settings'], '</h4>
	</div>
	<div class="windowbg">
		<span class="topslice"><span></span></span>
		<div class="content">
			<ul class="normallist">';

	if (!empty($extra['settings']))
	{
		foreach ($extra['settings'] as $table)
			echo '
				<li><input type="checkbox" name="settings[]" value="', $table, '" /> ', $table, '</li>';
	}
	else
		echo '
				<li>', $txt['no_extra_settings'], '</li>';

	echo '
			</ul>
		</div>

		<div class="error">
			<input type="checkbox" name="agree" id="agree" onclick="checkAgree();" value="1" /><strong>', $txt['i_promise'], '</strong>
		</div>

		<div class="windowbg submit_button">
			<input type="submit" name="submit_ok" class="button_submit" value="', $txt['clean_database'], '" />
		</div>
		<span class="botslice"><span></span></span>
	</div>
	<br class="clear" />
	<input type="hidden" name="', isset($context['session_var']) ? $context['session_var'] : 'sc', '" value="', $context['session_id'], '" />
</form>
';
}

/**
* template_execute()
*
* - Shows the results of the actions
*
* @return
*/
function template_execute()
{
	global $execute, $txt;

	echo '
<div id="results" class="tborder">
	<div class="cat_bar">
		<h3 class="catbg ">', $txt['execute_report'], '</h3>
	</div>
	
	<div class="title_bar">
		<h4 class="titlebg ">', $txt['extra_tables'], '</h4>
	</div>
	<div class="windowbg">
		<ul class="normallist">';

	if (!empty($execute['tables']))
	{
		foreach ($execute['tables'] as $table)
			echo '
			<li>', $table, ' - ', empty($execute['results']['table'][$table]) ? '<span class="fail">[' . $txt['failed'] . ']</span>' : '<span class="success">[' . $txt['successfull'] . ']</span>', '</li>';
	}
	else
		echo '
			<li>', $txt['no_tables_affected'], '</li>';

	echo '
		</ul>
	</div>

	
	<div class="title_bar">
		<h4 class="titlebg">', $txt['extra_columns'], '</h4>
	</div>
	<div class="windowbg">
		<ul class="normallist">';

	if (!empty($execute['columns']))
	{
		foreach ($execute['columns'] as $table_name => $table)
		{
			echo '
			<li>', $table_name, '
				<ul class="normallist">';

			foreach ($table as $column)
				echo '
					<li>', $column, ' - ', empty($execute['results']['column'][$table_name][$column]) ? '<span class="fail">[' . $txt['failed'] . ']</span>' : '<span class="success">[' . $txt['successfull'] . ']</span>', '</li>';

			echo '
				</ul>
			</li>';
		}
	}
	else
		echo '
			<li>', $txt['no_columns_affected'], '</li>';

	echo '
		</ul>
	</div>

	
	<div class="title_bar">
		<h4 class="titlebg">', $txt['extra_indexes'], '</h4>
	</div>
	<div class="windowbg">
		<ul class="normallist">';

	if (!empty($execute['indexes']))
	{
		foreach ($execute['indexes'] as $table_name => $table)
		{
			echo '
			<li>', $table_name, '
				<ul class="normallist">';

			foreach ($table as $index)
				echo '
					<li>', $index, ' - ', empty($execute['results']['index'][$table_name][$index]) ? '<span class="fail">[' . $txt['failed'] . ']</span>' : '<span class="success">[' . $txt['successfull'] . ']</span>', '</li>';

			echo '
				</ul>
			</li>';
		}
	}
	else
		echo '
			<li>', $txt['no_indexes_affected'], '</li>';

	echo '
		</ul>
	</div>
	
	
	<div class="title_bar">
		<h4 class="titlebg ">', $txt['extra_settings'], '</h4>
	</div>
	<div class="windowbg">
		<ul class="normallist">';

	if (!empty($execute['settings']))
	{
		foreach ($execute['settings'] as $table)
			echo '
			<li>', $table, ' - ', empty($execute['results']['settings']) ? '<span class="fail">[' . $txt['failed'] . ']</span>' : '<span class="success">[' . $txt['successfull'] . ']</span>', '</li>';
	}
	else
		echo '
			<li>', $txt['no_settings_affected'], '</li>';

	echo '
		</ul>
	</div>
	
	
	<br class="clear" />
</div>';
}

/**
* template_not_done()
*
* - shows a progress bar and pause button while we work our way through the changes
*
* @return
*/
function template_not_done()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<div id="databasecleanup">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['not_done_title'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				', $txt['not_done_reason'];

	if (isset($context['continue_percent']))
		echo '
				<div style="padding-left: 20%; padding-right: 20%; margin-top: 1ex;">
					<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
						<div style="padding-top: ', $context['browser']['is_webkit'] || $context['browser']['is_konqueror'] ? '2pt' : '1pt', '; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $context['continue_percent'], '%</div>
						<div style="width: ', $context['continue_percent'], '%; height: 12pt; z-index: 1; background-color: red;">&nbsp;</div>
					</div>
				</div>';

	echo '
				<form action="', $_SERVER['PHP_SELF'], $context['continue_get_data'], '" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;" name="autoSubmit" id="autoSubmit">
					<div style="margin: 1ex; text-align: right;">
						<input type="submit" name="cont" value="', $txt['not_done_continue'], '" class="button_submit" />
					</div>
					', $context['continue_post_data'], '
				</form>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>
	<br class="clear" />
	<script type="text/javascript"><!-- // --><![CDATA[
		var countdown = ', $context['continue_countdown'], ';
		doAutoSubmit();

		function doAutoSubmit()
		{
			if (countdown == 0)
				document.forms.autoSubmit.submit();
			else if (countdown == -1)
				return;

			document.forms.autoSubmit.cont.value = "', $txt['not_done_continue'], ' (" + countdown + ")";
			countdown--;

			setTimeout("doAutoSubmit();", 1000);
		}
	// ]]></script>';
}

/**
* nextStep()
*
* - called from function execute, uses template not_done to pause the loop
* - sets $_SESSION vars as needed for the next loop
*
* @param mixed $name
* @param integer $i
* @return
*/
function nextStep($name, $i = 0)
{
	global $context, $txt;

	// Try get more time...
	@set_time_limit(300);
	if (function_exists('apache_reset_timeout'))
		@apache_reset_timeout();

	// set the session info for the step
	$_SESSION[$name]['done'] = $i;

	// progress bar
	$context['continue_percent'] = round(((int) $_SESSION[$name]['done'] / $_SESSION[$name]['work']) * 100);
	$context['continue_percent'] = min($context['continue_percent'], 100);

	// set the context vars for display via the admin template 'not_done'
	$context['continue_get_data'] = '?action=execute';
	$context['page_title'] = $txt['not_done_title'];
	$context['continue_post_data'] = '
				<input type="hidden" name="' . (isset($context['session_var']) ? $context['session_var'] : 'sc') . '" value="' . $context['session_id'] . '" />
				<input type="hidden" name="agree" value="' . $_POST['agree'] . '" />
				<input type="hidden" name="submit_ok" value="' . $_POST['submit_ok'] . '" />';
	$context['continue_countdown'] = '5';
	$context['sub_template'] = 'not_done';

	obExit();
}

/**
* loadtext()
*
* - have not made this a mod so the txt strings are still in this file ;)
*
* @return
*/
function loadtext()
{
	global $txt;

	// Yup .... needs to go to a language file ... at some point
	$txt['not_done_title'] = 'Not done yet!';
	$txt['not_done_reason'] = 'To avoid overloading your server, the process has been temporarily paused.  It should automatically continue in a few seconds.  If it doesn\'t, please click continue below.';
	$txt['not_done_continue'] = 'Continue';
	$txt['not_done_title'] = 'Database Cleanup Pause';
	$txt['no_sql_file'] = 'Database structure in defined SQL is empty.';
	$txt['no_extra_tables'] = 'There are no extra tables';
	$txt['extra_tables'] = 'Extra Tables';
	$txt['no_extra_columns'] = 'There are no extra columns';
	$txt['extra_columns'] = 'Extra Columns';
	$txt['no_extra_indexes'] = 'There are no extra indexes';
	$txt['extra_indexes'] = 'Extra Indexes';
	$txt['no_extra_settings'] = 'There are no extra settings';
	$txt['extra_settings'] = 'Extra Settings';
	$txt['i_promise'] = 'I have backed up my database and am ready to run the database clean up tool.';
	$txt['clean_database'] = 'Clean Database';
	$txt['successfull'] = 'successfull';
	$txt['failed'] = 'failed';
	$txt['execute_report'] = 'Cleanup Report';
	$txt['no_tables_affected'] = 'No tables affected';
	$txt['no_columns_affected'] = 'No columns affected';
	$txt['no_indexes_affected'] = 'No indexes affected';
	$txt['no_settings_affected'] = 'No settings affected';
}

/**
* unCamelCase()
*
* - does what the name says inThisCase => in_this_case
*
* @param mixed $str
* @return
*/
function unCamelCase($str)
{
	$str[0] = strtolower($str[0]);
	$func = create_function('$camel', 'return "_" . strtolower($camel[1]);');
	return preg_replace_callback('/([A-Z])/', $func, $str);
}

/**
* file_to_array()
*
* - reads a single column file in to an associative array e.g. array key is the value
*
* @param mixed $file
* @return
*/
function file_to_array($file)
{
	$parts = array();

	// Want the keys as the var, otherwise php file would be cleaner
	$file_handle = fopen($file, "r");
	while (!feof($file_handle))
	{
		$line = trim(fgets($file_handle));

		// wild? yeah we like em like that
		if (substr($line, - 1) == '*')
			$line = substr($line, 0, - 1);

		$parts[$line] = $line;
	}
	fclose($file_handle);

	return $parts;
}

?>