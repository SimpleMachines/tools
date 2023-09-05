<?php

/**
 * Fix Packages (fixp)
 *
 * @package fixp
 * @author emanuele
 * @copyright 2022 emanuele, Simple Machines
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 1.0
 */

// Let's use the default theme
$ssi_theme = 1;
$forum_version = 'Fix Packages 0.1';
// If SSI.php is in the same place as this file, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (file_exists('./SSI.php'))
	require_once('./SSI.php');
// Hmm... no SSI.php?
else
	die('<b>Error:</b> Cannot find SSI - please verify you put this in the same place as SMF\'s index.php.');

/**
 * Change false to true if you want to avoid the admin check.
 */
$context['override_security'] = false;

/**
 * 
 * Do you want to add a new language?
 * Copy the following function,
 * change 'english' to the language you want
 * and tranlsate it. ;D
 * 
 */
class FixXP_Langauge
{
	private $txt;
	
	public function __construct()
	{
		global $user_info;

		// Load the english language always.
		$this->english();

		// Load the users if we have it.
		$flang = !empty($user_info['language']) ? $user_info['language'] : '';
		if (method_exists($this, $flang) && $flang != 'english')
			$this->$flang();
	}
	
	public function lang(string $s): string
	{
		global $txt;

		return isset($this->txt[$s]) ? $this->txt[$s] : (isset($txt[$s]) ? $txt[$s] : '');
	}

	// These are the language strings.
	public function english(): void
	{
		$this->txt['fixp'] = 'Fix Pack';
		$this->txt['log_packages_title_installed'] = 'Installed packages';
		$this->txt['log_packages_title_removed'] = 'Uninstalled packages';
		$this->txt['pack_button_remove'] = 'Mark uninstalled selected';
		$this->txt['pack_button_install'] = 'Mark installed selected';
		$this->txt['remove_hooks'] = 'Remove all hooks';
		$this->txt['uninstall'] = 'Show uninstalled';
		$this->txt['install'] = 'Show installed';
		$this->txt['mod_installed'] = 'Install date';
		$this->txt['mod_removed'] = 'Uninstall date';
	}
}

$context['fixp_lang'] = new FixXP_Langauge();

// Run it.
new FixXP($context['override_security']);

// and then let's throw out the template! :P
obExit(null, null, true);

class FixXP
{
	private $lang;
	private bool $isInstalling;

	public function __construct(bool $overrideSecure = false)
	{
		global $context;

		// Sorry, only logged in admins...unless you want so.
		if(empty($context['override_security']))
			isAllowedTo('admin_forum');

		$this->setupSystem();

		if (!empty($_POST['remove']) && is_array($_POST['remove']))
			$this->removeCustomizations();
		if (isset($_POST['remove_hooks']))
			$this->removeHooks();

		// Use the standard templates for showing this.
		$listOptions = $this->buildListOptions();

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'log_packages';

		// Create the request list.
		createList($listOptions);
	}

	public function setupSystem(): void
	{
		global $sourcedir, $modSettings, $user_info;

		loadLanguage('Admin');
		loadLanguage('Packages');
		loadTemplate('Admin');
		$this->lang = !empty($context['fixp_lang']) ? $context['fixp_lang'] : new FixXP_Langauge();

		$this->isInstalling = isset($_GET['uninstall']) ? 0 : 1;

		require_once($sourcedir . '/Subs-List.php');

		$context['sub_template'] = 'admin';
		$context['page_title'] = $this->lang('log_packages_title_' . (!empty($this->isInstalling) ? 'installed' : 'removed'));

		// A nice menu button
		cache_put_data('menu_buttons-' . implode('_', $user_info['groups']) . '-' . $user_info['language'], null);
		add_integration_function('integrate_menu_buttons', 'FixXP::menuButton', false);
		setupThemeContext(true);
	}

	private function buildListOptions(): array
	{
		global $boardurl, $context;

		return [
			'id' => 'log_packages',
			'title' => $context['page_title'],
			'get_items' => [
				'function' => [$this, 'list_getPacks'],
				'params' => [
					$this->isInstalling
				],
			],
			'get_count' => [
				'function' => [$this, 'list_getNumPacks'],
				'params' => [
					$this->isInstalling
				],
			],
			'columns' => [
				'name' => [
					'header' => [
						'value' => $this->lang('mod_name'),
					],
					'data' => [
						'db' => 'name',
					],
				],
				'version' => [
					'header' => [
						'value' => $this->lang('mod_version'),
					],
					'data' => [
						'db' => 'version',
					],
				],
				'install_date' => [
					'header' => [
						'value' => $this->lang('mod_' . (!empty($this->isInstalling) ? 'installed' : 'removed')),
					],
					'data' => [
						'function' => function($data)
						{
							return timeformat($data['time_' . (!empty($this->isInstalling) ? 'installed' : 'removed')]);
						},
					],
				],
				'check' => [
					'header' => [
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
					],
					'data' => [
						'function' => function($data)
						{
							return '<input type="checkbox" name="remove[]" value="' . $data['id_install'] . '"  class="input_check" />';
						},
						'class' => 'centertext',
					],
				],
			],
			'form' => [
				'href' => $boardurl . '/fix_packages.php?' . $context['session_var'] . '=' . $context['session_id'] . (!empty($this->isInstalling) ? '' : ';uninstall'),
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => '
						<a href="' . $boardurl . '/fix_packages.php' . (!empty($this->isInstalling) ? '?uninstall' : '') . '">[ ' . (!empty($this->isInstalling) ? $this->lang('uninstall') : $this->lang('install')) . ' ]</a>
						<input type="submit" name="remove_packages" value="' . $this->lang('pack_button_' . (!empty($this->isInstalling) ? 'remove' : 'install')) . '" class="button_submit" />
						<input type="submit" name="remove_hooks" value="' . $this->lang('remove_hooks') . '" class="button_submit" />',
					'class' => 'righttext',
				]
			],
		];
	}

	public function list_getPacks(int $start, int $items_per_page, string $sort, bool $isInstalling): array
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT id_install, name, version, time_installed, time_removed
			FROM {db_prefix}log_packages
			WHERE install_state = {int:inst_state}
			ORDER BY id_install',
			[
				'inst_state' => $isInstalling ? 1 : 0,
		]);

		$installed = [];
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$installed[] = $row;
		$smcFunc['db_free_result']($request);

		return $installed;
	}

	public function list_getNumPacks(bool $isInstalling): int
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}log_packages
			WHERE install_state = {int:inst_state}',
			[
				'inst_state' => $isInstalling ? 1 : 0,
		]);
		list ($numPacks) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return !is_numeric($numPacks) ? 0 : $numPacks;
	}

	private function removeCustomizations(): void
	{
		global $sourcedir, $boarddir, $smcFunc, $context;

		checkSession();

		foreach ($_POST['remove'] as $id)
			if (isset($id) && is_numeric($id))
			{
				if (!empty($this->isInstalling))
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}log_packages
						SET
							id_member_removed = {int:id_member},
							member_removed = {string:member_name},
							time_removed = {int:time_removed},
							install_state = 0
						WHERE id_install = {int:inst_package_id}',
						array(
							'id_member' => $context['user']['id'],
							'member_name' => $context['user']['name'],
							'time_removed' => time(),
							'inst_package_id' => $id,
					));
				else
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}log_packages
						SET
							id_member_removed = 0,
							member_removed = 0,
							time_removed = 0,
							install_state = 1
						WHERE id_install = {int:inst_package_id}',
						array(
							'inst_package_id' => $id,
					));
			}

		require_once($sourcedir . '/Subs-Package.php');
		package_put_contents($boarddir . '/Packages/installed.list', time());
	}

	private function removeHooks(): void
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

	// Menu buttons.
	public static function menuButton(array &$buttons): void
	{
		global $boardurl, $txt, $context;

		$context['current_action'] = 'fixp';

		$buttons['fixp'] = [
			'title' => $context['fixp_lang']->lang('fixp'),
			'show' => allowedTo('admin_forum'),
			'href' => $boardurl . '/fix_packages.php',
			'active_button' => true,
			'sub_buttons' => []
		];
	}
	
	private function lang(string $s): string
	{
		return $this->lang->lang($s);
	}
}