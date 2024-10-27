<?php
namespace Amoforms\Libs\Locale\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface I18n
 * @since 1.0.0
 * @package Amoforms\Libs\Locale\Interfaces
 */
interface I18n
{
	/**
	 * Get localized string
	 * @since 1.0.0
	 * @param string $string
	 * @return string
	 */
	public static function get($string);
}
