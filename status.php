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

// Forum slow? Having  performance problems?  This little blue pill will assist in finding the problem!

// !!! eAccelerator, etc.?

initialize_inputs();

$command_line = php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']);

generate_status();

function initialize_inputs()
{
	global $db_prefix, $context, $db_show_debug;

	// Turn off magic quotes runtime and enable error reporting.
	@set_magic_quotes_runtime(0);
	error_reporting(E_ALL);
	$db_show_debug = false;

	$possible = array(
		dirname(__FILE__),
		dirname(dirname(__FILE__)),
		dirname(dirname(dirname(__FILE__))),
		dirname(__FILE__) . '/forum',
		dirname(__FILE__) . '/forums',
		dirname(__FILE__) . '/community',
		dirname(dirname(__FILE__)) . '/forum',
		dirname(dirname(__FILE__)) . '/forums',
		dirname(dirname(__FILE__)) . '/community',
	);

	foreach ($possible as $dir)
	{
		if (@file_exists($dir . '/SSI.php'))
			break;
	}

	if (!@file_exists($dir . '/Settings.php'))
	{
		// It's search time!  This could take a while!
		$possible = array(dirname(__FILE__));
		$checked = array();
		while (!empty($possible))
		{
			$dir = array_pop($possible);
			if (@file_exists($dir . '/SSI.php') && @file_exists($dir . '/Settings.php'))
				break;
			$checked[] = $dir;

			$dp = dir($dir);
			while ($entry = $dp->read())
			{
				// Do the parents last, children first.
				if ($entry == '..' && !in_array(dirname($dir), $checked))
					array_unshift($possible, dirname($dir));
				elseif (is_dir($dir . '/' . $entry) && $entry != '.' && $entry != '..')
					array_push($possible, $dir . '/' . $entry);
			}
			$dp->close();
		}
		if (!@file_exists($dir . '/Settings.php'))
			return;
	}

	require_once($dir . '/Settings.php');

	if (empty($db_persist))
		$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);
	else
		$db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);
	if ($db_connection === false)
		$db_prefix = false;
	@mysql_select_db($db_name, $db_connection);
}

function get_linux_data()
{
	global $context;

	$context['current_time'] = strftime('%B %d, %Y, %I:%M:%S %p');

	$context['load_averages'] = @implode('', @get_file_data('/proc/loadavg'));
	if (!empty($context['load_averages']) && preg_match('~^([^ ]+?) ([^ ]+?) ([^ ]+)~', $context['load_averages'], $matches) != 0)
		$context['load_averages'] = array($matches[1], $matches[2], $matches[3]);
	elseif (($context['load_averages'] = @`uptime 2>/dev/null`) != null && preg_match('~load average[s]?: (\d+\.\d+),? (\d+\.\d+),? (\d+\.\d+)~i', $context['load_averages'], $matches) != 0)
		$context['load_averages'] = array($matches[1], $matches[2], $matches[3]);
	else
		unset($context['load_averages']);

	$context['cpu_info'] = array(
		'frequency' => 'MHz',
	);
	$cpuinfo = @implode('', @get_file_data('/proc/cpuinfo'));
	if (!empty($cpuinfo))
	{
		// This only gets the first CPU!
		if (preg_match('~model name\s+:\s*([^\n]+)~i', $cpuinfo, $match) != 0)
			$context['cpu_info']['model'] = $match[1];
		if (preg_match('~cpu mhz\s+:\s*([^\n]+)~i', $cpuinfo, $match) != 0)
			$context['cpu_info']['hz'] = $match[1];
	}
	else
	{
		// Solaris, perhaps?
		$cpuinfo = @`psrinfo -pv 2>/dev/null`;
		if (!empty($cpuinfo))
		{
			if (preg_match('~clock (\d+)~', $cpuinfo, $match) != 0)
				$context['cpu_info']['hz'] = $match[1];
			$cpuinfo = explode("\n", $cpuinfo);
			if (isset($cpuinfo[2]))
				$context['cpu_info']['model'] = trim($cpuinfo[2]);
		}
		else
		{
			// Mac OS X?
			if (strpos(strtolower(PHP_OS), 'darwin') === 0)
			{
				$cpuinfo = @`sysctl machdep.cpu.brand_string 2>/dev/null`;
				if (preg_match('~machdep\.cpu\.brand_string:(.+)@([\s\d\.]+)(.+)~', $cpuinfo, $match) != 0)
				{
					$context['cpu_info']['model'] = trim($match[1]);
					$context['cpu_info']['hz'] = trim($match[2]);
					$context['cpu_info']['frequency'] = strtolower(trim($match[3])) == 'ghz' ? 'GHz' : 'MHz';
				}
			}

			// BSD?
			$cpuinfo = @`sysctl hw.model 2>/dev/null`;
			if (empty($context['cpu_info']['model']) && preg_match('~hw\.model:(.+)~', $cpuinfo, $match) != 0)
				$context['cpu_info']['model'] = trim($match[1]);
			$cpuinfo = @`sysctl dev.cpu.0.freq 2>/dev/null`;
			if (empty($context['cpu_info']['hz']) && preg_match('~dev\.cpu\.0\.freq:(.+)~', $cpuinfo, $match) != 0)
				$context['cpu_info']['hz'] = trim($match[1]);
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
		if (strpos(strtolower(PHP_OS), 'darwin') !== 0 && !empty($meminfo) && preg_match('~hw\.physmem: (\d+)~i', $meminfo, $match) != 0)
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

			if (!empty($meminfo) && preg_match('~PhysMem:\s+(?:.+?)\s+([\d\.]+\w) used,\s+([\d\.]+\w) free~', $meminfo, $match) != 0)
			{
				$context['memory_usage']['used'] = unix_memsize($match[1]);
				$context['memory_usage']['free'] = unix_memsize($match[2]);
				$context['memory_usage']['total'] = $context['memory_usage']['used'] + $context['memory_usage']['free'];
			}
		}
	}

	// Can we obtain an uptime?
	$lastreboot = @get_file_data('/proc/uptime');
	if (!empty($lastreboot))
	{
		$lastreboot = explode(' ', $lastreboot[0]);
		$context['lastserverreboot'] = time() - trim($lastreboot[0]);
	}
	// Mac OS X and others?
	if (empty($context['lastserverreboot']))
	{
		$lastreboot = @`sysctl kern.boottime 2>/dev/null`;

		if (!empty($lastreboot) && preg_match('~kern\.boottime: { sec\s+=\s+(\d+),~', $lastreboot, $match) != 0)
			$context['lastserverreboot'] = $match[1];
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

			if ($proc[4][0] != '[' && strpos($proc[4], ' ') !== false)
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
				$context['cpu_info']['hz'] = $match[2];
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
			'value' => $row[1],
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

function generate_status()
{
	global $context, $command_line, $db_show_debug;

	$context['debug'] = empty($db_show_debug) ? false : true;
	show_header();

	if (strpos(strtolower(PHP_OS), 'win') === 0)
		get_windows_data();
	else
		get_linux_data();
	get_mysql_data();

	if ($command_line)
	{
		if (!empty($context['operating_system']['name']))
			echo 'Operating System:   ', trim($context['operating_system']['name']), "\n";
		if (!empty($context['cpu_info']))
			echo 'Processor:          ', trim($context['cpu_info']['model']), ' (', trim($context['cpu_info']['hz']), $context['cpu_info']['frequency'], ')', "\n";
		if ($context['debug'] && !empty($context['lastserverreboot']))
			echo 'Server Last Reboot:        ', date(DATE_RSS, $context['lastserverreboot']), "\n";
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

			<div class="righttext">', $context['current_time'], '</div>
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
					<td>', strtr($context['cpu_info']['model'], array('(R)' => '&reg;')), ' (', $context['cpu_info']['hz'], $context['cpu_info']['frequency'], ')</td>
				</tr>';

	if ($context['debug'] && !empty($context['lastserverreboot']))
		echo '
				<tr>
					<th valign="top" style="text-align: left; width: 30%;">Server Last Reboot:</th>
					<td>', date(DATE_RSS, $context['lastserverreboot']), '</td>
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

			<div class="righttext">MySQL ', $context['mysql_version'], '</div>
			<table width="100%" cellpadding="2" cellspacing="0" border="0">';

		// Has this server been running less than 1 day?
		if (!empty($context['lastserverreboot']) && time() - $context['lastserverreboot'] < 86400)
			echo '
				<tr>
					<th valign="top" colspan="2" style="color: red;">We have detected this server was restarted less than 24 Hours ago. These recommendations may not be accurate.</td>
				</tr>';

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
						', $warning ? '<strong>' : '', '(should be ', isset($stat['min']) ? '&gt;= ' . $stat['min'] . ' ' : '', isset($stat['max'], $stat['min']) ? 'and ' : '', isset($stat['max']) ? '&lt;= ' . $stat['max'] : '', ')', $warning ? '</strong>' : '';

			echo '
					</td>
				</tr>';
		}

		echo '
			</table>';

		if (isset($_GET['mysql_info']))
		{
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
		}
		else
			echo '
			<br />
			<a href="', $_SERVER['PHP_SELF'], '?mysql_info=1">Show more information...</a><br />';

		echo '
		</div>';
	}

	show_footer();
}

function show_header()
{
	global $settings, $command_line;

	if ($command_line)
		return;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta name="robots" content="noindex" />
		<title>Server Status</title>
		<style type="text/css">
			body
			{
				background-color: #e9eef2;
				margin: 0px;
				padding: 0px;
			}
			body, td
			{
				color: #444444;
				font-size: small;
				font-family: verdana, sans-serif;
			}
			div#header
			{
				background-image: url(Themes/default/images/catbg.jpg);
				background-repeat: repeat-x;
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
				background-color: #f0f4f7;
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
			', file_exists(dirname(__FILE__) . '/Themes/default/images/smflogo.gif') ? '<a href="http://www.simplemachines.org/" target="_blank"><img src="Themes/default/images/smflogo.gif" style="width: 250px; float: right;" alt="Simple Machines" border="0" /></a>
			' : '', '<div title="Wheat Thins">Server Status</div>
		</div>
		<div id="content">';
}

function show_footer()
{
	global $command_line;

	if ($command_line)
		return;

	echo '
		</div>
	</body>
</html>';
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
