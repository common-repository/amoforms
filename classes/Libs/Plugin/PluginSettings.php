<?php
namespace Amoforms\Libs\Plugin;

use Amoforms\Traits\Getter;
use Amoforms\Traits\Setter;
use Amoforms\Traits\Singleton;

/**
 * Class PluginSettings
 * @since 2.18.7
 * @method static PluginSettings instance
 * @package Amoforms\Libs\Plugin
 */
class PluginSettings
{
	use Singleton;
	use Getter;
	use Setter;

	const KEY_PLUGIN_UID = 'plugin_uid';

	/**
	 * @var array
	 */
	protected $_data = [];

	protected function __construct()
	{
		$this->_data = $this->get_db_settings();
		$this->check_plugin_uid();
	}

	/**
	 * @since 2.18.7
	 * @param string|null $key1
	 * @param string|null $key2
	 * @return mixed|null
	 */
	public function get($key1 = NULL, $key2 = NULL)
	{
		return $this->_get($key1, $key2);
	}

	/**
	 * @since 2.18.7
	 * @return array
	 */
	protected function get_db_settings()
	{
		$settings = get_option(AMOFORMS_OPTION_SETTINGS);
		$settings = is_string($settings) ? json_decode($settings, TRUE) : $settings;

		return is_array($settings) ? $settings : [];
	}

	/**
	 * @since 2.18.7
	 * @return bool
	 */
	protected function save()
	{
		return (bool)update_option(AMOFORMS_OPTION_SETTINGS, json_encode($this->_get()));
	}

	/**
	 * Checks plugin uid for existence and creates it if need.
	 * @since 2.18.7
	 */
	protected function check_plugin_uid()
	{
		if (!$this->_get(self::KEY_PLUGIN_UID)) {
			$this->_set(self::KEY_PLUGIN_UID, $this->generate_plugin_uid())->save();
		}
	}

	/**
	 * @since 2.18.7
	 * @return string
	 */
	protected function generate_plugin_uid()
	{
		return md5(microtime(TRUE) . '|' . site_url());
	}
}
