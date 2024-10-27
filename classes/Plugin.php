<?php
namespace Amoforms;

use Amoforms\Libs\amoCRM\Stats;
use Amoforms\Libs\Plugin\PluginSettings;
use Amoforms\Libs\Shortcodes;
use Amoforms\Libs\Filters;
use Amoforms\Models\amoCRM\amoUser;
use Amoforms\Models\Entries;
use Amoforms\Models\Forms;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Plugin
 * @since 1.0.0
 * @package Amoforms
 */
class Plugin
{
	public function __construct()
	{
		PluginSettings::instance();

		Forms\Manager::instance()
			->create_table()
			->check_and_create_form();

		Router::instance();

		Entries\Manager::instance()->create_table();

		amoUser::instance();

		Shortcodes\Manager::instance()->register_codes();
		Libs\Hooks\Manager::instance()->register();

		Stats::instance()->install_event();
	}
}
