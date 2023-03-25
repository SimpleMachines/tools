<?php

/**
 * Ban Script (bans)
 *
 * @package bans
 * @author emanuele
 * @copyright 2011 emanuele, Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 0.1
 */

define('SMF_INTEGRATION_SETTINGS', serialize(array(
	'integrate_menu_buttons' => 'bans_menu_button',)));
// If SSI.php is in the same place as this file, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (file_exists('./SSI.php'))
	require_once('./SSI.php');
// Hmm... no SSI.php?
else
	die('<b>Error:</b> Cannot find SSI - please verify you put this in the same place as SMF\'s index.php.');

/**
 * 
 * Do you want to add a new language?
 * Copy the following function,
 * change 'english' to the language you want
 * and tranlsate it. ;D
 * 
 */
function bans_english()
{
	global $txt;

	$txt['bans'] = 'Ban Script';
	$txt['board_id'] = 'Board id: ';
	$txt['topic_id'] = 'Starting from this topic (ID):';
	$txt['topic_id_desc'] = '<span class="smalltext">Optional, if omitted unread messages will be used</span>';
	$txt['check_board'] = 'Check this board for bans';
	$txt['ban_by'] = 'triggers';
	$txt['ban_time'] = 'length';
	$txt['ban_reason'] = 'reason';
	$txt['ban_time_title'] = 'Empty or 0 for permanent';
	$txt['to_bans'] = 'Members you want to ban';
	$txt['admin_ban_emails'] = 'emails';
	$txt['admin_ban_usernames'] = 'usernames';
	$txt['admin_ban_ips'] = 'IPs';
	$txt['admin_ban_usernames_and_emails'] = 'usernames and emails';
	$txt['admin_ban_usernames_and_ips'] = 'usernames and IPs';
	$txt['admin_ban_emails_and_ips'] = 'emails and IPs';
	$txt['admin_ban_usernames_emails_and_ips'] = 'usernames, emails and IPs';
	$txt['reportedBan_error_name'] = 'The ban name is missing.';
	$txt['reportedBan_error_reason'] = 'The ban reason is missing.';
	$txt['no_member_selected'] = 'No member selected';
	$txt['session_timeout'] = 'Session timed out...sorry, go to the admin panel and then come back here...';
	$txt['enable_select'] = 'Enable/disable triggers select';
// 	$txt['issued_on'] = 'Issued on';
}


// Do not change anything below this line
// ------------------------------------------------------------------------------------------------

// Let's start the main job
bans_main();
// and then let's throw out the template! :P
obExit(null, null, true);

function bans_menu_button(&$buttons)
{
	global $boardurl, $txt, $context;
	bans_loadLanguage();
	$context['sub_template'] = 'ban_script';
	$context['current_action'] = 'bans';

	$buttons['bans'] = array(
		'title' => $txt['bans'],
		'show' => allowedTo('admin_forum'),
		'href' => $boardurl . '/ban_script.php',
		'active_button' => true,
		'sub_buttons' => array(
		),
	);
}

function bans_loadLanguage()
{
	global $user_info;

	bans_english();
	$flang = 'bans_' . (!empty($user_info['language']) ? $user_info['language'] : '');
	if (function_exists($flang) && $flang != 'bans_english')
		return $flang();
}

function bans_main()
{
	global $txt, $sourcedir, $boardurl, $context, $forum_version, $user_info, $smcFunc;

	loadLanguage('Admin');
	bans_loadLanguage();
	$context['sub_template'] = 'ban_script';

	// Sorry, only logged in admins...
	isAllowedTo('admin_forum');

	if (isset($_POST['save']) && empty($context['errors']))
		banScript();
	if (!empty($_POST['check_board']))
	{
		$context['id_board_to_check'] = (int) $_POST['id_board'];
		$context['id_topic_to_start'] = (int) $_POST['id_topic'];
	}

	if (!empty($context['id_board_to_check']) && empty($context['errors']))
	{
		// Making a list is not hard with this beauty.
		require_once($sourcedir . '/Subs-List.php');

		// Use the standard templates for showing this.
		$listOptions = array(
			'id' => 'to_be_banned',
			'title' => $txt['bans'],
			'items_per_page' => 25,
			'base_href' => $boardurl,
			'get_items' => array(
				'function' => 'list_getToBeBans',
			),
			'get_count' => array(
				'function' => 'list_getNumToBeBans',
			),
			'columns' => array(
				'name' => array(
					'header' => array(
						'value' => $txt['username'],
					),
					'data' => array(
						'db' => 'member_name',
					),
				),
				'warning' => array(
					'header' => array(
						'value' => $txt['warning_status'],
					),
					'data' => array(
						'db' => 'warning',
					),
				),
	// 			'issued_on' => array(
	// 				'header' => array(
	// 					'value' => $txt['issued_on'],
	// 				),
	// 				'data' => array(
	// 					'function' => create_function('$data', '
	// 						return strftime(\'%Y-%m-%d\', $data[\'topic_started\']);
	// 					'),
	// 				),
	// 			),
				'check' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
					),
					'data' => array(
						'function' => create_function('$data', '
							return \'<input type="checkbox" name="remove[]" value="\' . $data[\'id_member\'] . \'"  class="input_check" />\';
						'),
						'class' => 'centertext',
					),
				),
			),
			'form' => array(
				'href' => $boardurl . '/ban_script.php?' . $context['session_var'] . '=' . $context['session_id'],
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="add_to_ban" value="' . $txt['ban_add'] . '" class="button_submit" />',
					'class' => 'righttext',
				),
			),
		);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'to_be_banned';

		// Create the request list.
		createList($listOptions);
	}
	elseif (isset($_POST['remove']))
	{
		// Process the IDs from the list
		$id_to_bans = array();
		$context['to_bans'] = array();
		foreach ($_POST['remove'] as $id_member)
			$id_to_bans[] = (int) $id_member;

		$context['id_to_bans'] = array_unique($id_to_bans);

		$request = $smcFunc['db_query']('', '
			SELECT member_name
			FROM {db_prefix}members
			WHERE id_member IN ({array_int:id_members})',
			array(
				'id_members' => $context['id_to_bans'],
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['to_bans'][] = $row['member_name'];
		// Let's show the forms to do the actual ban
		$context['ban_forms'] = true;
		$context['ban_names'] = bans_getBanNames();
	}
	else
	{
		$context['to_bans'] = array();
		if (!empty($_POST['to_bans']))
		{
			$to_bans = explode(',', $_POST['to_bans']);
			foreach ($to_bans as $IDs)
				$context['to_bans'][] = $IDs;
		}
		// First time visit, everything clear!
		$context['ban_forms'] = true;
		$context['ban_names'] = bans_getBanNames();
	}
}

function bans_getBanNames()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_ban_group, name
		FROM {db_prefix}ban_groups
		ORDER BY id_ban_group DESC',
		array()
	);

	$banNames = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$banNames[$row['id_ban_group']] = $row['name'];

	return $banNames;
}

function list_getToBeBans($start, $items_per_page, $sort)
{
	global $smcFunc, $context, $user_info;

	if (empty($context['id_topic_to_start']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT MIN(id_msg)
			FROM {db_prefix}log_mark_read
			WHERE id_member = {int:current_member}
				AND id_board = {int:current_board}',
			array(
				'current_board' => $context['id_board_to_check'],
				'current_member' => $user_info['id'],
			)
		);
		list ($lastRead) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if (empty($lastRead))
			$lastRead = 0;
	}
	else
		$lastRead = $context['id_topic_to_start'];

	$request = $smcFunc['db_query']('', '
		SELECT mem.id_member, mem.member_name, mem.warning
		FROM {db_prefix}topics as t
			LEFT JOIN {db_prefix}messages AS msg ON (t.id_first_msg = msg.id_msg)
			JOIN {db_prefix}members AS mem ON (msg.subject = mem.real_name)
		WHERE t.id_first_msg > {int:last_read}
			AND t.id_board = {int:id_board}
		ORDER BY t.id_topic DESC
		LIMIT {int:start}, {int:limit}',
		array(
			'last_read' => $lastRead,
			'id_board' => $context['id_board_to_check'],
			'start' => $start,
			'limit' => $items_per_page,
	));

	$suggested = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$suggested[] = $row;

	$smcFunc['db_free_result']($request);

	return $suggested;
}

function list_getNumToBeBans()
{
	global $smcFunc, $context, $user_info;

	if (empty($context['id_topic_to_start']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT MIN(id_msg)
			FROM {db_prefix}log_mark_read
			WHERE id_member = {int:current_member}
				AND id_board = {int:current_board}',
			array(
				'current_board' => $context['id_board_to_check'],
				'current_member' => $user_info['id'],
			)
		);
		list ($lastRead) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if (empty($lastRead))
			$lastRead = 0;
	}
	else
		$lastRead = $context['id_topic_to_start'];

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}topics as t
			LEFT JOIN {db_prefix}messages AS msg ON (t.id_first_msg = msg.id_msg)
			LEFT JOIN {db_prefix}members AS mem ON (msg.subject = mem.real_name)
		WHERE t.id_first_msg > {int:last_read}
			AND t.id_board = {int:id_board}
			AND msg.subject = mem.real_name
		ORDER BY t.id_topic DESC',
		array(
			'last_read' => $lastRead,
			'id_board' => $context['id_board_to_check'],
	));
	list ($numPacks) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $numPacks;
}

function template_ban_script()
{
	global $context, $txt, $boardurl;

	if (!empty($context['errors']))
	{
		echo '
		<div class="errorbox">
			', implode('<br />', $context['errors']), '
		</div>';
	}

	if (!empty($context['ban_forms']))
	{
		// Ban forms go here.
		echo '
			<form action="', $boardurl, '/ban_script.php" method="post">';
		if (!isset($_POST['add_to_ban']) && empty($context['errors']))
			echo  '
				<dl class="settings">
					<dt><label for="id_board">', $txt['board_id'], '</label></dt>
					<dd><input type="text" size="15" maxlength="20" id="id_board" name="id_board" class="input_text"/></dd>
					<dt><label for="id_topic">', $txt['topic_id'], '<br />', $txt['topic_id_desc'], '</label></dt>
					<dd><input type="text" size="15" maxlength="20" id="id_topic" name="id_topic" class="input_text"/></dd>
				</dl>
				<div class="righttext" style="padding-right:40%">
					<input type="submit" name="check_board" value="' . $txt['check_board'] . '" class="button_submit" />
				</div>
				<br /><br /><br />';

		echo '
				<dl class="settings">
					<dt><label style="vertical-align:top" for="to_bans">' . $txt['to_bans'] . '</label></dt>
					<dd><textarea id="to_bans" name="to_bans" rows="5" cols="60">', !empty($context['to_bans']) ? implode(',', $context['to_bans']) : '', '</textarea></dd>
					<dt><label for="ban_name">' . $txt['ban_name'] . '</label></dt>
					<dd>
						<select onchange="return showHide(\'ban_name_new\', this.selectedIndex, \'ban_name\');" id="ban_name" name="ban_name">';

		if (!empty($context['ban_names']))
			foreach ($context['ban_names'] as $id => $name)
				echo '
							<option value="' . $name . '"' . (!empty($_POST['ban_name']) && $name == $_POST['ban_name'] ? ' selected="selected"' : '') . '>' . $name . '</option>';
		echo '
							<option value=""' . (empty($context['ban_names']) ? ' selected="selected"' : '') . '/>
						</select>
						<input id="ban_name_new"' . (empty($context['ban_names']) && !empty($_POST['ban_name']) ? ' value="' . $_POST['ban_name'] . '" ' : '') . ' type="text" size="15" maxlength="20" name="ban_name_new" class="input_text"/>
					<script type="text/javascript"><!-- // --><![CDATA[' . (!empty($context['ban_names']) ? '
						document.getElementById(\'ban_name_new\').style.display = \'none\'; ' : '') . '
						
						function showHide(elem, select, orig)
						{
							var a = document.getElementById(elem);
							var b = document.getElementById(orig);

							if (b.options[select].value == \'\')
								a.style.display = \'\';
							else
								a.style.display = \'none\';
						}
						function enable_disable(elem, state)
						{
							document.getElementById(elem).disabled = !state;
						}
					// ]]></script>';

		echo '
					</dd>
					<dt><label for="ban_reason">' . $txt['ban_reason'] . '</label></dt>
					<dd><input type="text" size="15" maxlength="20" id="ban_reason" name="ban_reason" value="' . (!empty($_POST['ban_reason']) ? $_POST['ban_reason'] : '') . '" class="input_text"/></dd>
					<dt><label for="ban_time">' . $txt['ban_time'] . '</label></dt>
					<dd><input size="5" title="' . $txt['ban_time_title'] . '" type="text" id="ban_time" name="ban_time" class="input_text"/></dd>
					<dt><label for="ban_type">' . $txt['ban_by'] . '</label></dt>
					<dd>
						<input type="hidden" name="ban_type" value="ban_emails" />
						<select disabled="disabled" id="ban_type" name="ban_type">
							<option value="ban_names">' . $txt['admin_ban_usernames'] . '</option>
							<option selected="selected" value="ban_emails">' . $txt['admin_ban_emails'] . '</option>
							<option value="ban_ips">' . $txt['admin_ban_ips'] . '</option>
							<option value="ban_names_emails">' . $txt['admin_ban_usernames_and_emails'] . '</option>
							<option value="ban_names_ips">' . $txt['admin_ban_usernames_and_ips'] . '</option>
							<option value="ban_emails_ips">' . $txt['admin_ban_emails_and_ips'] . '</option>
							<option value="ban_names_emails_ips">' . $txt['admin_ban_usernames_emails_and_ips'] . '</option>
						</select>
						<input onclick="return enable_disable(\'ban_type\', this.checked);" id="enable_select" type="checkbox" /><label for="enable_select">' . $txt['enable_select'] . '</label></dd>
				</dl>
				<div class="righttext" style="padding-right:40%">
					<input type="submit" name="save" value="' . $txt['ban_add'] . '" class="button_submit" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</div>
			</form>';
	}
}




function banScript()
{
	global $modSettings, $smcFunc, $txt, $sourcedir, $user_info, $context;

	$context['errors'] = array();

	if (checkSession('post', '', false) != '')
		$context['errors'][] = $txt['session_timeout'];

	// Were are we supposed to put all these bans??
	if (!empty($_POST['ban_name']))
		$ban_name = $smcFunc['htmlspecialchars']($_POST['ban_name'], ENT_QUOTES);
	elseif (!empty($_POST['ban_name_new']))
		$ban_name = $smcFunc['htmlspecialchars']($_POST['ban_name_new'], ENT_QUOTES);
	else
		$ban_name = '';
	
	// If the ban is new we need a reason, otherwise it's already there.
	if (empty($_POST['ban_name']) && !empty($_POST['ban_name_new']))
		$ban_reason = !empty($_POST['ban_reason']) ? $smcFunc['htmlspecialchars']($_POST['ban_reason'], ENT_QUOTES) : '';
	else
		$ban_reason = 'placeholder';

	$remove = false;
	switch ($_POST['ban_type'])
	{
		case 'ban_names':
			$mactions = array('ban_names');
			break;
		case 'ban_emails':
			$mactions = array('ban_mails');
			break;
		case 'ban_ips':
			$mactions = array('ban_ips', 'ban_ips2');
			break;
		case 'ban_names_emails':
			$mactions = array('ban_names', 'ban_mails');
			break;
		case 'ban_names_ips':
			$mactions = array('ban_names', 'ban_ips', 'ban_ips2');
			break;
		case 'ban_emails_ips':
			$mactions = array('ban_mails', 'ban_ips', 'ban_ips2');
			break;
		case 'ban_names_emails_ips':
			$mactions = array('ban_names', 'ban_mails', 'ban_ips', 'ban_ips2');
			break;
		default:
			$mactions = null;
			break;
	}

	if (empty($ban_name))
		$context['errors'][] = $txt['reportedBan_error_name'];
	if (empty($ban_reason))
		$context['errors'][] = $txt['reportedBan_error_reason'];
	if (empty($_POST['to_bans']))
		$context['errors'][] = $txt['no_member_selected'];

	if (!empty($context['errors']))
		return;

	$context['to_bans'] = explode(',', $_POST['to_bans']);
	$id_members = array_unique($context['to_bans']);

	$names = array();
	foreach ($id_members as $key => $value)
	{
		$names[] = htmlspecialchars(trim($value));
		unset($id_members[$key]);
	}

	if (!empty($names))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}members
			WHERE real_name IN ({array_string:members_names})',
			array(
				'members_names' => $names,
		));
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$id_members[] = $row['id_member'];
	}
	$id_members = array_unique($id_members);

	// Clean the input.
	foreach ($id_members as $key => $value)
	{
		$id_members[$key] = (int) $value;
		// Don't ban yourself, idiot.
		if ($value == $user_info['id'])
			unset($id_members[$key]);
	}

	require_once($sourcedir . '/ManageBans.php');

	$id_ban = $smcFunc['db_query']('', '
		SELECT id_ban_group
		FROM {db_prefix}ban_groups
		WHERE name = {string:ban_name}
		LIMIT 1',
		array(
			'ban_name' => $ban_name,
	));
	if ($smcFunc['db_num_rows']($id_ban) != 0)
		list($ban_group_id) = $smcFunc['db_fetch_row']($id_ban);
	else
		$ban_group_id = null;

	$smcFunc['db_free_result']($id_ban);

	$members = array();
	$_REQUEST['bg'] = $ban_group_id;

	// Set up an array of bans
	foreach ($id_members as $key => $value)
		if ($value != $user_info['id'])
			// Don't ban yourself, idiot.
			$members[] = (int) $value;

	if (empty($members))
		return;

	if (!empty($mactions))
		foreach ($mactions as $maction)
		{
			switch ($maction)
			{
				case 'ban_names':
					$what = 'member_name';
					$post_ban = 'user';
					$_POST['ban_suggestion'][] = 'user';
					$_POST['bantype'] = 'user_ban';
					break;
				case 'ban_mails':
					$what = 'email_address';
					$post_ban = 'email';
					$_POST['ban_suggestion'][] = 'email';
					$_POST['bantype'] = 'email_ban';
					break;
				case 'ban_ips':
					$what = 'member_ip';
					$post_ban = !empty($ban_group_id) ? 'ip' : 'main_ip';
					$_POST['ban_suggestion'][] = 'main_ip';
					$_POST['bantype'] = 'ip_ban';
					break;
				case 'ban_ips2':
					$what = 'member_ip2';
					$post_ban = !empty($ban_group_id) ? 'ip' : 'main_ip';
					$_POST['ban_suggestion'][] = 'main_ip';
					$_POST['bantype'] = 'ip_ban';
					break;
				default:
					return false;
			}
			$request = $smcFunc['db_query']('', '
				SELECT id_member, member_name, ' . $what . '
				FROM {db_prefix}members
				WHERE id_member IN ({array_int:id_members})
					AND id_group != {int:admin_group}
					AND FIND_IN_SET({int:admin_group}, additional_groups) = 0',
				array(
					'id_members' => $members,
					'admin_group' => 1,
			));
			$context['members_data'] = array();
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$context['members_data'][] = $row;

			$_POST['expiration'] = empty($_POST['ban_time']) ? 'never' : 'expire_time';
			$_POST['expire_date'] = !empty($_POST['ban_time']) ? $_POST['ban_time'] : '';
			$_POST['old_expire'] = 0;
			$_POST['full_ban'] = empty($_POST['ban_time']);
			$_POST['reason'] = $ban_reason;
			$_POST['ban_name'] = $ban_name;
			$_POST['notes'] = '';

			foreach ($context['members_data'] as $key => $row) //while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				if ($maction == 'ban_ips' || $maction == 'ban_ips2')
				{
					if (!bans_checkExistingTriggerIP($row[$what]))
						continue;

					$_POST['ip'] = $row[$what];
				}
				elseif ($maction == 'ban_mails')
				{
					if (bans_checkExistingTriggerMail($row[$what]))
						continue;
				}
				elseif ($maction == 'ban_names')
				{
					if (bans_checkExistingTriggerName($row[$what]))
						continue;
				}
				$_POST['add_new_trigger'] = !empty($ban_group_id) ? 1 : null;
				$_POST['add_ban'] = empty($ban_group_id) ? 1 : null;
				$_POST[$post_ban] = $row[$what];
				$_REQUEST['u'] = $row['id_member'];

				bans_BanEdit();
				if (empty($ban_group_id))
				{
					$id_ban = $smcFunc['db_query']('', '
						SELECT id_ban_group
						FROM {db_prefix}ban_groups
						WHERE name = {string:ban_name}
						LIMIT 1',
						array(
							'ban_name' => $ban_name
						)
					);
					if ($smcFunc['db_num_rows']($id_ban) != 0)
						list($ban_group_id) = $smcFunc['db_fetch_row']($id_ban);
					else
						$ban_group_id = null;
					$smcFunc['db_free_result']($id_ban);
				}
			}
			$smcFunc['db_free_result']($request);
			$remove = true;
		}

	// Register the last modified date.
	updateSettings(array('banLastUpdated' => time()));

	// Update the member table to represent the new ban situation.
	updateBanMembers();
	// Overrides ban_edit template
	$context['sub_template'] = 'ban_script';
	unset($_POST);
}

function bans_checkExistingTriggerIP($fullip = '')
{
	global $smcFunc, $user_info;

	if (empty($fullip))
		return false;

	$ip_array = ip2range($fullip);

	if (count($ip_array) == 4 || count($ip_array) == 8)
		$values = array(
			'ip_low1' => $ip_array[0]['low'],
			'ip_high1' => $ip_array[0]['high'],
			'ip_low2' => $ip_array[1]['low'],
			'ip_high2' => $ip_array[1]['high'],
			'ip_low3' => $ip_array[2]['low'],
			'ip_high3' => $ip_array[2]['high'],
			'ip_low4' => $ip_array[3]['low'],
			'ip_high4' => $ip_array[3]['high'],
		);
	else
		return false;

	// Again...don't ban yourself!!
	if (!empty($fullip) && ($user_info['ip'] == $fullip || $user_info['ip2'] == $fullip))
		return false;

	$request = $smcFunc['db_query']('', '
		SELECT bg.id_ban_group, bg.name
		FROM {db_prefix}ban_groups AS bg
		INNER JOIN {db_prefix}ban_items AS bi ON
			(bi.id_ban_group = bg.id_ban_group)
			AND ip_low1 = {int:ip_low1} AND ip_high1 = {int:ip_high1}
			AND ip_low2 = {int:ip_low2} AND ip_high2 = {int:ip_high2}
			AND ip_low3 = {int:ip_low3} AND ip_high3 = {int:ip_high3}
			AND ip_low4 = {int:ip_low4} AND ip_high4 = {int:ip_high4}
		LIMIT 1',
		$values
	);
	if ($smcFunc['db_num_rows']($request) != 0)
		$ret = false;
	else
		$ret = true;
	$smcFunc['db_free_result']($request);

	return $ret;
}
function bans_checkExistingTriggerMail($address = '')
{
	global $smcFunc, $user_info, $context;
	static $bannedEmails;

	if (empty($address))
		return false;

	// Again...don't ban yourself!!
	if (!empty($address) && $user_info['email'] == $address)
		return false;

	if (empty($bannedEmails))
	{
		$addresses = array();
		foreach ($context['members_data'] as $key => $row)
			$addresses[] = $row['email_address'];

		$request = $smcFunc['db_query']('', '
			SELECT bg.id_ban_group, bg.name, email_address
			FROM {db_prefix}ban_groups AS bg
			INNER JOIN {db_prefix}ban_items AS bi ON
				(bi.id_ban_group = bg.id_ban_group)
				AND email_address IN ({array_string:address})',
			array(
				'address' => $addresses,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$bannedEmails[] = $row['email_address'];
		$smcFunc['db_free_result']($request);
	}

	return empty($bannedEmails) ? false : in_array($address, $bannedEmails);
}
function bans_checkExistingTriggerName($member_id = '')
{
	global $smcFunc, $user_info, $context;
	static $bannedIDs;

	if (empty($member_id))
		return false;

	// Again...don't ban yourself!!
	if (!empty($member_id) && ($user_info['id'] == $member_id))
		return false;

	if (empty($bannedIDs))
	{
		$names = array();
		foreach ($context['members_data'] as $key => $row)
			$names[] = $row['id_member'];

		$request = $smcFunc['db_query']('', '
			SELECT bg.id_ban_group, bg.name, id_member
			FROM {db_prefix}ban_groups AS bg
			INNER JOIN {db_prefix}ban_items AS bi ON
				(bi.id_ban_group = bg.id_ban_group)
				AND id_member IN ({array_int:member_id})',
			array(
				'member_id' => $names,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$bannedIDs[] = $row['id_member'];
		$smcFunc['db_free_result']($request);
	}

	return empty($bannedIDs) ? false : in_array($member_id, $bannedIDs);
}

function bans_BanEdit()
{
	global $txt, $modSettings, $context, $ban_request, $scripturl, $smcFunc;

	$_REQUEST['bg'] = empty($_REQUEST['bg']) ? 0 : (int) $_REQUEST['bg'];

	// Adding or editing a ban trigger?
	if (!empty($_POST['add_new_trigger']) || !empty($_POST['edit_trigger']))
	{
		checkSession();

		$newBan = !empty($_POST['add_new_trigger']);
		$values = array(
			'id_ban_group' => $_REQUEST['bg'],
			'hostname' => '',
			'email_address' => '',
			'id_member' => 0,
			'ip_low1' => 0,
			'ip_high1' => 0,
			'ip_low2' => 0,
			'ip_high2' => 0,
			'ip_low3' => 0,
			'ip_high3' => 0,
			'ip_low4' => 0,
			'ip_high4' => 0,
		);

		// Preset all values that are required.
		if ($newBan)
		{
			$insertKeys = array(
				'id_ban_group' => 'int',
				'hostname' => 'string',
				'email_address' => 'string',
				'id_member' => 'int',
				'ip_low1' => 'int',
				'ip_high1' => 'int',
				'ip_low2' => 'int',
				'ip_high2' => 'int',
				'ip_low3' => 'int',
				'ip_high3' => 'int',
				'ip_low4' => 'int',
				'ip_high4' => 'int',
			);
		}
		else
			$updateString = '
				hostname = {string:hostname}, email_address = {string:email_address}, id_member = {int:id_member},
				ip_low1 = {int:ip_low1}, ip_high1 = {int:ip_high1},
				ip_low2 = {int:ip_low2}, ip_high2 = {int:ip_high2},
				ip_low3 = {int:ip_low3}, ip_high3 = {int:ip_high3},
				ip_low4 = {int:ip_low4}, ip_high4 = {int:ip_high4}';

		if ($_POST['bantype'] == 'ip_ban')
		{
			$ip = trim($_POST['ip']);
			$ip_parts = ip2range($ip);
			$ip_check = checkExistingTriggerIP($ip_parts, $ip);
			if (!$ip_check)
				fatal_lang_error('invalid_ip', false);
			$values = array_merge($values, $ip_check);

			$modlogInfo['ip_range'] = $_POST['ip'];
		}
		elseif ($_POST['bantype'] == 'hostname_ban')
		{
			if (preg_match('/[^\w.\-*]/', $_POST['hostname']) == 1)
				fatal_lang_error('invalid_hostname', false);

			// Replace the * wildcard by a MySQL compatible wildcard %.
			$_POST['hostname'] = str_replace('*', '%', $_POST['hostname']);

			$values['hostname'] = $_POST['hostname'];

			$modlogInfo['hostname'] = $_POST['hostname'];
		}
		elseif ($_POST['bantype'] == 'email_ban')
		{
			if (preg_match('/[^\w.\-\+*@]/', $_POST['email']) == 1)
				fatal_lang_error('invalid_email', false);
			$_POST['email'] = strtolower(str_replace('*', '%', $_POST['email']));

/*		Check already done...another query gone. :P
			// Check the user is not banning an admin.
			$request = $smcFunc['db_query']('', '
				SELECT id_member
				FROM {db_prefix}members
				WHERE (id_group = {int:admin_group} OR FIND_IN_SET({int:admin_group}, additional_groups) != 0)
					AND email_address LIKE {string:email}
				LIMIT 1',
				array(
					'admin_group' => 1,
					'email' => $_POST['email'],
				)
			);
			if ($smcFunc['db_num_rows']($request) != 0)
				fatal_lang_error('no_ban_admin', 'critical');
			$smcFunc['db_free_result']($request);*/

			$values['email_address'] = $_POST['email'];

			$modlogInfo['email'] = $_POST['email'];
		}
		elseif ($_POST['bantype'] == 'user_ban')
		{
			$_POST['user'] = preg_replace('~&amp;#(\d{4,5}|[2-9]\d{2,4}|1[2-9]\d);~', '&#$1;', $smcFunc['htmlspecialchars']($_POST['user'], ENT_QUOTES));

			$request = $smcFunc['db_query']('', '
				SELECT id_member, (id_group = {int:admin_group} OR FIND_IN_SET({int:admin_group}, additional_groups) != 0) AS isAdmin
				FROM {db_prefix}members
				WHERE member_name = {string:user_name} OR real_name = {string:user_name}
				LIMIT 1',
				array(
					'admin_group' => 1,
					'user_name' => $_POST['user'],
				)
			);
			if ($smcFunc['db_num_rows']($request) == 0)
				fatal_lang_error('invalid_username', false);
			list ($memberid, $isAdmin) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			if ($isAdmin && $isAdmin != 'f')
				fatal_lang_error('no_ban_admin', 'critical');

			$values['id_member'] = $memberid;

			$modlogInfo['member'] = $memberid;
		}
		else
			fatal_lang_error('no_bantype_selected', false);

		if ($newBan)
			$smcFunc['db_insert']('',
				'{db_prefix}ban_items',
				$insertKeys,
				$values,
				array('id_ban')
			);
		else
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}ban_items
				SET ' . $updateString . '
				WHERE id_ban = {int:ban_item}
					AND id_ban_group = {int:id_ban_group}',
				array_merge($values, array(
					'ban_item' => (int) $_REQUEST['bi'],
				))
			);

		// Log the addion of the ban entry into the moderation log.
		logAction('ban', $modlogInfo + array(
			'new' => $newBan,
			'type' => $_POST['bantype'],
		));
	}

	// The user pressed 'Remove selected ban entries'.
	elseif (!empty($_POST['remove_selection']) && !empty($_POST['ban_items']) && is_array($_POST['ban_items']))
	{
		checkSession();

		// Making sure every deleted ban item is an integer.
		foreach ($_POST['ban_items'] as $key => $value)
			$_POST['ban_items'][$key] = (int) $value;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ban_items
			WHERE id_ban IN ({array_int:ban_list})
				AND id_ban_group = {int:ban_group}',
			array(
				'ban_list' => $_POST['ban_items'],
				'ban_group' => $_REQUEST['bg'],
			)
		);

		// It changed, let the settings and the member table know.
		updateSettings(array('banLastUpdated' => time()));
		updateBanMembers();
	}

	// Modify OR add a ban.
	elseif (!empty($_POST['modify_ban']) || !empty($_POST['add_ban']))
	{
		checkSession();

		$addBan = !empty($_POST['add_ban']);
		if (empty($_POST['ban_name']))
			fatal_lang_error('ban_name_empty', false);

		// Let's not allow HTML in ban names, it's more evil than beneficial.
		$_POST['ban_name'] = $smcFunc['htmlspecialchars']($_POST['ban_name'], ENT_QUOTES);

		// Check whether a ban with this name already exists.
		$request = $smcFunc['db_query']('', '
			SELECT id_ban_group
			FROM {db_prefix}ban_groups
			WHERE name = {string:new_ban_name}' . ($addBan ? '' : '
				AND id_ban_group != {int:ban_group}') . '
			LIMIT 1',
			array(
				'ban_group' => $_REQUEST['bg'],
				'new_ban_name' => $_POST['ban_name'],
			)
		);
		if ($smcFunc['db_num_rows']($request) == 1)
			fatal_lang_error('ban_name_exists', false, array($_POST['ban_name']));
		$smcFunc['db_free_result']($request);

		$_POST['reason'] = $smcFunc['htmlspecialchars']($_POST['reason'], ENT_QUOTES);
		$_POST['notes'] = $smcFunc['htmlspecialchars']($_POST['notes'], ENT_QUOTES);
		$_POST['notes'] = str_replace(array("\r", "\n", '  '), array('', '<br />', '&nbsp; '), $_POST['notes']);
		$_POST['expiration'] = $_POST['expiration'] == 'never' ? 'NULL' : ($_POST['expiration'] == 'expired' ? '0' : ($_POST['expire_date'] != $_POST['old_expire'] ? time() + 24 * 60 * 60 * (int) $_POST['expire_date'] : 'expire_time'));
		$_POST['full_ban'] = empty($_POST['full_ban']) ? '0' : '1';
		$_POST['cannot_post'] = !empty($_POST['full_ban']) || empty($_POST['cannot_post']) ? '0' : '1';
		$_POST['cannot_register'] = !empty($_POST['full_ban']) || empty($_POST['cannot_register']) ? '0' : '1';
		$_POST['cannot_login'] = !empty($_POST['full_ban']) || empty($_POST['cannot_login']) ? '0' : '1';

		if ($addBan)
		{
			// Adding some ban triggers?
			if ($addBan && !empty($_POST['ban_suggestion']) && is_array($_POST['ban_suggestion']))
			{
				$ban_triggers = array();
				$ban_logs = array();
				if (in_array('main_ip', $_POST['ban_suggestion']) && !empty($_POST['main_ip']))
				{
					$ip = trim($_POST['main_ip']);
					$ip_parts = ip2range($ip);
					if (!checkExistingTriggerIP($ip_parts, $ip))
						fatal_lang_error('invalid_ip', false);

					$ban_triggers[] = array(
						$ip_parts[0]['low'],
						$ip_parts[0]['high'],
						$ip_parts[1]['low'],
						$ip_parts[1]['high'],
						$ip_parts[2]['low'],
						$ip_parts[2]['high'],
						$ip_parts[3]['low'],
						$ip_parts[3]['high'],
						'',
						'',
						0,
					);

					$ban_logs[] = array(
						'ip_range' => $_POST['main_ip'],
					);
				}
				if (in_array('hostname', $_POST['ban_suggestion']) && !empty($_POST['hostname']))
				{
					if (preg_match('/[^\w.\-*]/', $_POST['hostname']) == 1)
						fatal_lang_error('invalid_hostname', false);

					// Replace the * wildcard by a MySQL wildcard %.
					$_POST['hostname'] = str_replace('*', '%', $_POST['hostname']);

					$ban_triggers[] = array(
						0, 0, 0, 0, 0, 0, 0, 0,
						substr($_POST['hostname'], 0, 255),
						'',
						0,
					);
					$ban_logs[] = array(
						'hostname' => $_POST['hostname'],
					);
				}
				if (in_array('email', $_POST['ban_suggestion']) && !empty($_POST['email']))
				{
					if (preg_match('/[^\w.\-\+*@]/', $_POST['email']) == 1)
						fatal_lang_error('invalid_email', false);
					$_POST['email'] = strtolower(str_replace('*', '%', $_POST['email']));

					$ban_triggers[] = array(
						0, 0, 0, 0, 0, 0, 0, 0,
						'',
						substr($_POST['email'], 0, 255),
						0,
					);
					$ban_logs[] = array(
						'email' => $_POST['email'],
					);
				}
				if (in_array('user', $_POST['ban_suggestion']) && (!empty($_POST['bannedUser']) || !empty($_POST['user'])))
				{
					// We got a username, let's find its ID.
					if (empty($_POST['bannedUser']))
					{
						$_POST['user'] = preg_replace('~&amp;#(\d{4,5}|[2-9]\d{2,4}|1[2-9]\d);~', '&#$1;', $smcFunc['htmlspecialchars']($_POST['user'], ENT_QUOTES));

						$request = $smcFunc['db_query']('', '
							SELECT id_member, (id_group = {int:admin_group} OR FIND_IN_SET({int:admin_group}, additional_groups) != 0) AS isAdmin
							FROM {db_prefix}members
							WHERE member_name = {string:username} OR real_name = {string:username}
							LIMIT 1',
							array(
								'admin_group' => 1,
								'username' => $_POST['user'],
							)
						);
						if ($smcFunc['db_num_rows']($request) == 0)
							fatal_lang_error('invalid_username', false);
						list ($_POST['bannedUser'], $isAdmin) = $smcFunc['db_fetch_row']($request);
						$smcFunc['db_free_result']($request);

						if ($isAdmin && $isAdmin != 'f')
							fatal_lang_error('no_ban_admin', 'critical');
					}

					$ban_triggers[] = array(
						0, 0, 0, 0, 0, 0, 0, 0,
						'',
						'',
						(int) $_POST['bannedUser'],
					);
					$ban_logs[] = array(
						'member' => $_POST['bannedUser'],
					);
				}

				if (!empty($_POST['ban_suggestion']['ips']) && is_array($_POST['ban_suggestion']['ips']))
				{
					$_POST['ban_suggestion']['ips'] = array_unique($_POST['ban_suggestion']['ips']);

					// Don't add the main IP again.
					if (in_array('main_ip', $_POST['ban_suggestion']))
						$_POST['ban_suggestion']['ips'] = array_diff($_POST['ban_suggestion']['ips'], array($_POST['main_ip']));

					foreach ($_POST['ban_suggestion']['ips'] as $ip)
					{
						$ip_parts = ip2range($ip);

						// They should be alright, but just to be sure...
						if (count($ip_parts) != 4)
							fatal_lang_error('invalid_ip', false);

						$ban_triggers[] = array(
							$ip_parts[0]['low'],
							$ip_parts[0]['high'],
							$ip_parts[1]['low'],
							$ip_parts[1]['high'],
							$ip_parts[2]['low'],
							$ip_parts[2]['high'],
							$ip_parts[3]['low'],
							$ip_parts[3]['high'],
							'',
							'',
							0,
						);
						$ban_logs[] = array(
							'ip_range' => $ip,
						);
					}
				}
			}

			// Yes yes, we're ready to add now.
			$smcFunc['db_insert']('',
				'{db_prefix}ban_groups',
				array(
					'name' => 'string-20', 'ban_time' => 'int', 'expire_time' => 'raw', 'cannot_access' => 'int', 'cannot_register' => 'int',
					'cannot_post' => 'int', 'cannot_login' => 'int', 'reason' => 'string-255', 'notes' => 'string-65534',
				),
				array(
					$_POST['ban_name'], time(), $_POST['expiration'], $_POST['full_ban'], $_POST['cannot_register'],
					$_POST['cannot_post'], $_POST['cannot_login'], $_POST['reason'], $_POST['notes'],
				),
				array('id_ban_group')
			);
			$_REQUEST['bg'] = $smcFunc['db_insert_id']('{db_prefix}ban_groups', 'id_ban_group');

			// Now that the ban group is added, add some triggers as well.
			if (!empty($ban_triggers) && !empty($_REQUEST['bg']))
			{
				// Put in the ban group ID.
				foreach ($ban_triggers as $k => $trigger)
					array_unshift($ban_triggers[$k], $_REQUEST['bg']);

				// Log what we are doing!
				foreach ($ban_logs as $log_details)
					logAction('ban', $log_details + array('new' => 1));

				$smcFunc['db_insert']('',
					'{db_prefix}ban_items',
					array(
						'id_ban_group' => 'int', 'ip_low1' => 'int', 'ip_high1' => 'int', 'ip_low2' => 'int', 'ip_high2' => 'int',
						'ip_low3' => 'int', 'ip_high3' => 'int', 'ip_low4' => 'int', 'ip_high4' => 'int', 'hostname' => 'string-255',
						'email_address' => 'string-255', 'id_member' => 'int',
					),
					$ban_triggers,
					array('id_ban')
				);
			}
		}
		else
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}ban_groups
				SET
					name = {string:ban_name},
					reason = {string:reason},
					notes = {string:notes},
					expire_time = {raw:expiration},
					cannot_access = {int:cannot_access},
					cannot_post = {int:cannot_post},
					cannot_register = {int:cannot_register},
					cannot_login = {int:cannot_login}
				WHERE id_ban_group = {int:id_ban_group}',
				array(
					'expiration' => $_POST['expiration'],
					'cannot_access' => $_POST['full_ban'],
					'cannot_post' => $_POST['cannot_post'],
					'cannot_register' => $_POST['cannot_register'],
					'cannot_login' => $_POST['cannot_login'],
					'id_ban_group' => $_REQUEST['bg'],
					'ban_name' => $_POST['ban_name'],
					'reason' => $_POST['reason'],
					'notes' => $_POST['notes'],
				)
			);

/*	This is done later
		// No more caching, we have something new here.
		updateSettings(array('banLastUpdated' => time()));
		updateBanMembers();*/
	}

/*This should be only presentation
	// If we're editing an existing ban, get it from the database.
	if (!empty($_REQUEST['bg']))
	{
		$context['ban_items'] = array();
		$request = $smcFunc['db_query']('', '
			SELECT
				bi.id_ban, bi.hostname, bi.email_address, bi.id_member, bi.hits,
				bi.ip_low1, bi.ip_high1, bi.ip_low2, bi.ip_high2, bi.ip_low3, bi.ip_high3, bi.ip_low4, bi.ip_high4,
				bg.id_ban_group, bg.name, bg.ban_time, bg.expire_time, bg.reason, bg.notes, bg.cannot_access, bg.cannot_register, bg.cannot_login, bg.cannot_post,
				IFNULL(mem.id_member, 0) AS id_member, mem.member_name, mem.real_name
			FROM {db_prefix}ban_groups AS bg
				LEFT JOIN {db_prefix}ban_items AS bi ON (bi.id_ban_group = bg.id_ban_group)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = bi.id_member)
			WHERE bg.id_ban_group = {int:current_ban}',
			array(
				'current_ban' => $_REQUEST['bg'],
			)
		);
		if ($smcFunc['db_num_rows']($request) == 0)
			fatal_lang_error('ban_not_found', false);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (!isset($context['ban']))
			{
				$context['ban'] = array(
					'id' => $row['id_ban_group'],
					'name' => $row['name'],
					'expiration' => array(
						'status' => $row['expire_time'] === null ? 'never' : ($row['expire_time'] < time() ? 'expired' : 'still_active_but_we_re_counting_the_days'),
						'days' => $row['expire_time'] > time() ? floor(($row['expire_time'] - time()) / 86400) : 0
					),
					'reason' => $row['reason'],
					'notes' => $row['notes'],
					'cannot' => array(
						'access' => !empty($row['cannot_access']),
						'post' => !empty($row['cannot_post']),
						'register' => !empty($row['cannot_register']),
						'login' => !empty($row['cannot_login']),
					),
					'is_new' => false,
				);
			}
			if (!empty($row['id_ban']))
			{
				$context['ban_items'][$row['id_ban']] = array(
					'id' => $row['id_ban'],
					'hits' => $row['hits'],
				);
				if (!empty($row['ip_high1']))
				{
					$context['ban_items'][$row['id_ban']]['type'] = 'ip';
					$context['ban_items'][$row['id_ban']]['ip'] = range2ip(array($row['ip_low1'], $row['ip_low2'], $row['ip_low3'], $row['ip_low4']), array($row['ip_high1'], $row['ip_high2'], $row['ip_high3'], $row['ip_high4']));
				}
				elseif (!empty($row['hostname']))
				{
					$context['ban_items'][$row['id_ban']]['type'] = 'hostname';
					$context['ban_items'][$row['id_ban']]['hostname'] = str_replace('%', '*', $row['hostname']);
				}
				elseif (!empty($row['email_address']))
				{
					$context['ban_items'][$row['id_ban']]['type'] = 'email';
					$context['ban_items'][$row['id_ban']]['email'] = str_replace('%', '*', $row['email_address']);
				}
				elseif (!empty($row['id_member']))
				{
					$context['ban_items'][$row['id_ban']]['type'] = 'user';
					$context['ban_items'][$row['id_ban']]['user'] = array(
						'id' => $row['id_member'],
						'name' => $row['real_name'],
						'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
						'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
					);
				}
				// Invalid ban (member probably doesn't exist anymore).
				else
				{
					unset($context['ban_items'][$row['id_ban']]);
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}ban_items
						WHERE id_ban = {int:current_ban}',
						array(
							'current_ban' => $row['id_ban'],
						)
					);
				}
			}
		}
		$smcFunc['db_free_result']($request);
	}
	// Not an existing one, then it's probably a new one.
	else
	{
		$context['ban'] = array(
			'id' => 0,
			'name' => '',
			'expiration' => array(
				'status' => 'never',
				'days' => 0
			),
			'reason' => '',
			'notes' => '',
			'ban_days' => 0,
			'cannot' => array(
				'access' => true,
				'post' => false,
				'register' => false,
				'login' => false,
			),
			'is_new' => true,
		);
		$context['ban_suggestions'] = array(
			'main_ip' => '',
			'hostname' => '',
			'email' => '',
			'member' => array(
				'id' => 0,
			),
		);

		// Overwrite some of the default form values if a user ID was given.
		if (!empty($_REQUEST['u']))
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member, real_name, member_ip, email_address
				FROM {db_prefix}members
				WHERE id_member = {int:current_user}
				LIMIT 1',
				array(
					'current_user' => (int) $_REQUEST['u'],
				)
			);
			if ($smcFunc['db_num_rows']($request) > 0)
				list ($context['ban_suggestions']['member']['id'], $context['ban_suggestions']['member']['name'], $context['ban_suggestions']['main_ip'], $context['ban_suggestions']['email']) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			if (!empty($context['ban_suggestions']['member']['id']))
			{
				$context['ban_suggestions']['href'] = $scripturl . '?action=profile;u=' . $context['ban_suggestions']['member']['id'];
				$context['ban_suggestions']['member']['link'] = '<a href="' . $context['ban_suggestions']['href'] . '">' . $context['ban_suggestions']['member']['name'] . '</a>';

				// Default the ban name to the name of the banned member.
				$context['ban']['name'] = $context['ban_suggestions']['member']['name'];

				// Would be nice if we could also ban the hostname.
				if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $context['ban_suggestions']['main_ip']) == 1 && empty($modSettings['disableHostnameLookup']))
					$context['ban_suggestions']['hostname'] = host_from_ip($context['ban_suggestions']['main_ip']);

				// Find some additional IP's used by this member.
				$context['ban_suggestions']['message_ips'] = array();
				$request = $smcFunc['db_query']('ban_suggest_message_ips', '
					SELECT DISTINCT poster_ip
					FROM {db_prefix}messages
					WHERE id_member = {int:current_user}
						AND poster_ip RLIKE {string:poster_ip_regex}
					ORDER BY poster_ip',
					array(
						'current_user' => (int) $_REQUEST['u'],
						'poster_ip_regex' => '^[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}$',
					)
				);
				while ($row = $smcFunc['db_fetch_assoc']($request))
					$context['ban_suggestions']['message_ips'][] = $row['poster_ip'];
				$smcFunc['db_free_result']($request);

				$context['ban_suggestions']['error_ips'] = array();
				$request = $smcFunc['db_query']('ban_suggest_error_ips', '
					SELECT DISTINCT ip
					FROM {db_prefix}log_errors
					WHERE id_member = {int:current_user}
						AND ip RLIKE {string:poster_ip_regex}
					ORDER BY ip',
					array(
						'current_user' => (int) $_REQUEST['u'],
						'poster_ip_regex' => '^[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}$',
					)
				);
				while ($row = $smcFunc['db_fetch_assoc']($request))
					$context['ban_suggestions']['error_ips'][] = $row['ip'];
				$smcFunc['db_free_result']($request);

				// Borrowing a few language strings from profile.
				loadLanguage('Profile');
			}
		}
	}

	// Template needs this to show errors using javascript
	loadLanguage('Errors');

	// If we're in wireless mode remove the admin template layer and use a special template.
	if (WIRELESS && WIRELESS_PROTOCOL != 'wap')
	{
		$context['sub_template'] = WIRELESS_PROTOCOL . '_ban_edit';
		foreach ($context['template_layers'] as $k => $v)
			if (strpos($v, 'generic_menu') === 0)
				unset($context['template_layers'][$k]);
	}
	else
		$context['sub_template'] = 'ban_edit';*/
}
