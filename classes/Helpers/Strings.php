<?php
namespace Amoforms\Helpers;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Strings
 * @since 2.16.12
 * @package Amoforms\Helpers
 */
class Strings
{
	/**
	 * Escape HTML-entities in string
	 *
	 * @since 1.0.0
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function escape($text)
	{
		return htmlspecialchars(htmlspecialchars_decode(trim((string)$text), ENT_QUOTES | ENT_HTML401), ENT_QUOTES | ENT_HTML401);
	}

	/**
	 * Un-escape HTML-entities in string
	 *
	 * @since 1.0.2
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function un_escape($text)
	{
		return trim(htmlspecialchars_decode((string)$text, ENT_QUOTES | ENT_HTML401));
	}

	/**
	 * Sanitize CSS
	 *
	 * @since 2.17.0
	 *
	 * @param string $css
	 *
	 * @return string|bool - Sanitized CSS or FALSE on error
	 */
	public static function sanitize_css($css)
	{
		$result = FALSE;
		if (is_string($css)) {
			$result = str_replace('<', '', $css);
		}

		return $result;
	}
}
