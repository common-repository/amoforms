<?php
namespace Amoforms\Libs\Analytics\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Exceptions\Runtime;

/**
 * Interface AnalyticsInterface
 * @since 2.18.0
 * @package Amoforms\Libs\Analytics\Interfaces
 */
interface AnalyticsInterface
{
	/**
	 * @since 2.18.0
	 * @return array
	 */
	public static function get_default_settings();

	/**
	 * @since 2.18.0
	 * @return bool
	 */
	public function is_enabled();

	/**
	 * Enable or disable Google Analytics
	 * @since 2.18.0
	 * @param bool $on - on / off
	 * @throws Runtime
	 */
	public function toggle($on);
}
