<?php

/**
 * This file has a single job - database backup.
 *
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1 Alpha 1
 */

require_once('SSI.php');

if ($user_info['is_admin'])
	DumpDatabase2();

/**
 * Dumps the database.
 * It writes all of the database to standard output.
 * It uses gzip compression if compress is set in the URL/post data.
 * It may possibly time out, and mess up badly if you were relying on it. :P
 * The data dumped depends on whether "struct" and "data" are passed.
 * It requires an administrator and the session hash by post.
 * It is called from ManageMaintenance.php.
 */
function DumpDatabase2()
{
	global $db_name, $scripturl, $context, $modSettings, $crlf, $smcFunc, $db_prefix, $db_show_debug;

	// Administrators only!
	if (!allowedTo('admin_forum'))
		fatal_lang_error('no_dump_database', 'critical');

	// We don't need debug when dumping the database
	$modSettings['disableQueryCheck'] = true;
	$db_show_debug = false;

	// You can't dump nothing!
// 	if (!isset($_REQUEST['struct']) && !isset($_REQUEST['data']))
		$_REQUEST['data'] = true;
		//$_REQUEST['struct'] = false;

// 	checkSession('post');

	// We will need this, badly!
	db_extend();

	$smcFunc['db_table_sql'] = 'exp_db_table_sql';
	$smcFunc['db_insert_sql'] = 'exp_db_insert_sql';
	$smcFunc['db_escape_string'] = 'addslashes';

	// Attempt to stop from dying...
	@set_time_limit(600);
	$time_limit = ini_get('max_execution_time');
	$start_time = time();
	
	// @todo ... fail on not getting the requested memory?
	@set_time_limit(600);
	if (@ini_get('memory_limit') < 256)
		@ini_set('memory_limit', '256M');
	$memory_limit = @ini_get('memory_limit');
	$memory_limit = (empty($memory_limit) ? 4 : $memory_limit) * 1024 * 1024 / 4;
	$current_used_memory = 0;
	$db_backup = '';
	$output_function = 'un_compressed';

	@ob_end_clean();

	// Get rid of the gzipping alreading being done.
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_end_clean();
	// If we can, clean anything already sent from the output buffer...
	elseif (ob_get_length() != 0)
		ob_clean();

	// Tell the client to save this file, even though it's text.
	header('Content-Type: ' . ($context['browser']['is_ie'] || $context['browser']['is_opera'] ? 'application/octetstream' : 'application/octet-stream'));
	header('Content-Encoding: none');

	// This time the extension should just be .sql.
	$extension = '.sql';

	// This should turn off the session URL parser.
	$scripturl = '';

	// Send the proper headers to let them download this file.
	header('Content-Disposition: filename="' . $db_name . '-' . (empty($_REQUEST['struct']) ? 'data' : (empty($_REQUEST['data']) ? 'structure' : 'complete')) . '_' . strftime('%Y-%m-%d') . $extension . '"');
	header('Cache-Control: private');
	header('Connection: close');

	// This makes things simpler when using it so very very often.
	$crlf = "\r\n";

	// SQL Dump Header.
	$db_chunks = 
		'-- ==========================================================' . $crlf .
		'--' . $crlf .
		'-- Database dump of tables in `' . $db_name . '`' . $crlf .
		'-- ' . timeformat(time(), false) . $crlf .
		'--' . $crlf .
		'-- ==========================================================' . $crlf .
		$crlf;

	// Get all tables in the database....
	if (preg_match('~^`(.+?)`\.(.+?)$~', $db_prefix, $match) != 0)
	{
		$db = strtr($match[1], array('`' => ''));
		$dbp = str_replace('_', '\_', $match[2]);
	}
	else
	{
		$db = false;
		$dbp = $db_prefix;
	}

	// Dump each table.
	$tables = $smcFunc['db_list_tables'](false, $db_prefix . '%');
	foreach ($tables as $tableName)
	{
		// Are we dumping the structures?
		if (isset($_REQUEST['struct']))
		{
			$db_chunks .= 
				$crlf .
				'--' . $crlf .
				'-- Table structure for table `' . $tableName . '`' . $crlf .
				'--' . $crlf .
				$crlf .
				$smcFunc['db_table_sql']($tableName) . ';' . $crlf;
		}
		else
			// This is needed to speedup things later
			$smcFunc['db_table_sql']($tableName);

		// How about the data?
		if (substr($tableName, -10) == 'log_errors')
			continue;

		$first_round = true;
		$close_table = false;

		// Are there any rows in this table?
		while ($get_rows = $smcFunc['db_insert_sql']($tableName, $first_round))
		{
			if (empty($get_rows))
				break;

			// Time is what we need here!
			if (function_exists('apache_reset_timeout'))
				@apache_reset_timeout();
			elseif (!empty($time_limit) && ($start_time + $time_limit - 20 > time()))
			{
				$start_time = time();
				@set_time_limit(150);
			}

			if ($first_round)
			{
				$db_chunks .= 
					$crlf .
					'--' . $crlf .
					'-- Dumping data in `' . $tableName . '`' . $crlf .
					'--' . $crlf .
					$crlf;
				$first_round = false;
			}
			$db_chunks .= 
				$get_rows;
			$current_used_memory += $smcFunc['strlen']($db_chunks);

			$db_backup .= $db_chunks;
			unset($db_chunks);
			$db_chunks = '';
			if ($current_used_memory > $memory_limit)
			{
				echo $output_function($db_backup);
				$current_used_memory = 0;
				// This is probably redundant
				unset($db_backup);
				$db_backup = '';
			}
			$close_table = true;
		}

		// No rows to get - skip it.
		if ($close_table)
			$db_backup .= 
			'-- --------------------------------------------------------' . $crlf;
	}

	$db_backup .= 
		$crlf .
		'-- Done' . $crlf;

	echo $output_function($db_backup);

	exit;
}

/**
 * Dummy/helper function, it simply returns the string passed as argument
 * @param $string, a string
 * @return the string passed
 */
function un_compressed($string = '')
{
	return $string;
}


/**
 * Gets all the necessary INSERTs for the table named table_name.
 * It goes in 250 row segments.
 * @param string $tableName - the table to create the inserts for.
 * @return string, the query to insert the data back in, or an empty
 *  string if the table was empty.
 */
function exp_db_insert_sql($tableName, $new_table = false)
{
	global $smcFunc, $db_prefix, $detected_id;
	static $start = 0, $num_rows, $fields, $limit, $last_id;

	if ($new_table)
	{
		$limit = strstr($tableName, 'log_') !== false ? 500 : 250;
		$start = 0;
		$last_id = 0;
	}

	$data = '';
	$tableName = str_replace('{db_prefix}', $db_prefix, $tableName);

	// This will be handy...
	$crlf = "\r\n";

	// This is done this way because retrieve data only with LIMIT will become slower after each query
	// and for long tables (e.g. {db_prefix}messages) it could be a pain...
	// Instead using WHERE speeds up thing *a lot* (especially after the first 50'000 records)
	$result = $smcFunc['db_query']('', '
		SELECT /*!40001 SQL_NO_CACHE */ *
		FROM ' . $tableName . '' .
		(!empty($last_id) && !empty($detected_id) ? '
		WHERE ' . $detected_id . ' > ' . $last_id : '') . '
		LIMIT ' . (empty($last_id) ? $start . ', ' : '') . $limit,
		array(
			'security_override' => true,
		)
	);

	// The number of rows, just for record keeping and breaking INSERTs up.
	$num_rows = $smcFunc['db_num_rows']($result);

	if ($num_rows == 0)
		return false;

	if ($new_table)
	{
		$fields = array_keys($smcFunc['db_fetch_assoc']($result));

		// SQLite fetches an array so we need to filter out the numberic index for the columns.
		foreach ($fields as $key => $name)
			if (is_numeric($name))
				unset($fields[$key]);

		$smcFunc['db_data_seek']($result, 0);

	}
		// Start it off with the basic INSERT INTO.
		$data = 'INSERT INTO `' . $tableName . '`' . $crlf . "\t" . '(`' . implode('`, `', $fields) . '`)' . $crlf . 'VALUES ';

	// Loop through each row.
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		// Get the fields in this row...
		$field_list = array();

		foreach ($row as $key => $item)
		{
			if (is_numeric($key))
				continue;

			// Try to figure out the type of each field. (NULL, number, or 'string'.)
			if (!isset($item))
				$field_list[] = 'NULL';
			elseif (is_numeric($item) && (int) $item == $item)
				$field_list[] = $item;
			else
				$field_list[] = '\'' . str_replace("\r", '', str_replace("\n", '\n', $smcFunc['db_escape_string']($item))) . '\'';
		}
		if (!empty($detected_id) && isset($row[$detected_id]))
			$last_id = $row[$detected_id];

		$data .= '(' . implode(', ', $field_list) . ')' . ',' . $crlf . "\t";
	}

	$smcFunc['db_free_result']($result);
	$data = substr(trim($data), 0, -1) . ';' . $crlf;

	$start += $limit;

	return $data;
}

/**
 * Dumps the schema (CREATE) for a table.
 * @todo why is this needed for?
 * @param string $tableName - the table
 * @return string - the CREATE statement as string
 */
function exp_db_table_sql($tableName)
{
	global $smcFunc, $db_prefix, $detected_id;

	$tableName = str_replace('{db_prefix}', $db_prefix, $tableName);
	$detected_id = '';

	// This will be needed...
	$crlf = "\r\n";

	// Start the create table...
	$schema_create = '';
	$index_create = '';

	// Let's get the create statement directly from SQLite.
	$result = $smcFunc['db_query']('', '
		SELECT sql
		FROM sqlite_master
		WHERE type = {string:type}
			AND name = {string:table_name}',
		array(
			'type' => 'table',
			'table_name' => $tableName,
		)
	);
	list ($schema_create) = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);

	// Now the indexes.
	$result = $smcFunc['db_query']('', '
		SELECT sql
		FROM sqlite_master
		WHERE type = {string:type}
			AND tbl_name = {string:table_name}',
		array(
			'type' => 'index',
			'table_name' => $tableName,
		)
	);
	$indexes = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		if (trim($row['sql']) != '')
			$indexes[] = $row['sql'];
	$smcFunc['db_free_result']($result);

	$index_create .= implode(';' . $crlf, $indexes);
	$schema_create = empty($indexes) ? rtrim($schema_create) : $schema_create . ';' . $crlf . $crlf;

	return $schema_create . $index_create;
}


?>