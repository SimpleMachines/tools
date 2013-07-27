<?php
require_once('SSI.php');

error_reporting(0);
global $sourcedir, $smcFunc, $boardurl;
require_once($sourcedir . '/Subs-Auth.php');

$start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
$request = $smcFunc['db_query']('', '
	SELECT COUNT(*)
	FROM {db_prefix}members',
	array()
);
list($usercount) = $smcFunc['db_fetch_row']($request);
$smcFunc['db_free_result']($request);

echo 'Users to process: ', $usercount - $start . '<br />';

$time = time();
// Let's do 5 seconds
while (time() < ($time + 5))
{
	$request = $smcFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}members
		LIMIT {int:start}, 10',
		array(
			'start' => $start,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		resetPassword($row['id_member']);
	$smcFunc['db_free_result']($request);
	$start += 10;
}

if ($usercount >= $start)
{
	echo '<a id="cont" href="', $boardurl, '/resetpwds.php?start=', $start, '">click here to continue</a><script>
			var href = document.getElementById("cont").href;
			document.getElementById("cont").innerHTML="wait...";
			setTimeout(function(){window.location = href;},3000);
			</script>';
	die();
}

echo 'nothing left!';