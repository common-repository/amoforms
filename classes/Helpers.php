<?php
namespace Amoforms;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Libs\Errors\MainErrorHandler;

/**
 * Class Helpers
 * @since 1.0.0
 * @package Amoforms
 */
class Helpers
{
	/**
	 * Add or remove ".min" to path based on AMOFORMS_USE_MIN_JS
	 * @since 1.0.0
	 * @param string $path
	 * @return string
	 */
	public static function get_js_path($path)
	{
		$is_min = (strpos($path, '.min.js') !== FALSE);
		if (AMOFORMS_USE_MIN_JS) {
			if (!$is_min) {
				$path = str_replace('.js', '.min.js', $path);
			}
		} else {
			if ($is_min) {
				$path = str_replace('.min.js', '.js', $path);
			}
		}
		return $path;
	}

	/**
	 * Remove slashes before quotes in string or array.
	 * For array this function walk over all string values.
	 * @param string $string
	 * @return string|array
	 */
	public static function strip_slashes($string)
	{
		if (is_array($string)) {
			foreach ($string as $index => $str) {
				$string[$index] = self::strip_slashes($str);
			}
		} elseif (is_string($string)) {
			$string = stripslashes($string);
		}
		return $string;
	}

	/**
	 * Show exceptions only in debug mode
	 * @since 1.0.0
	 * @param \Exception $e
	 */
	public static function handle_exception(\Exception $e) {
		if (!AMOFORMS_DEV_ENV) {
			MainErrorHandler::instance()->capture_exception($e);
		}

		if (defined('WP_DEBUG') && WP_DEBUG) {
			if (Router::instance()->is_ajax()) {
				(new Libs\Http\Response\Ajax())
					->set_message($e->getMessage())
					->set('trace', $e->__toString())
					->send();
			} else {
				echo '<pre>Exception: <b>' . $e->getMessage() . '</b>' . PHP_EOL;
				var_dump($e);
				echo "</pre>";
			}
		}
	}

	/**
	 * Convert hex color (#7fcc8d) to RGB array
	 * @param $hex
	 * @return array
	 */
	public static function hex2rgb($hex)
	{
		$hex = str_replace("#", '', $hex);
		if (strlen($hex) == 3) {
			$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
			$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
			$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
		} else {
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		}
		return [$r, $g, $b];
	}

	/**
	 * Determine whether color is dark
	 * @param string $hex_color - color (#7fcc8d)
	 * @return bool
	 */
	public static function is_dark_color($hex_color)
	{
		if ($hex_color == 'transparent') {
			return false;
		}

		$rgb = self::hex2rgb($hex_color);
		$brightness = (($rgb[0] * 299) + ($rgb[1] * 587) + ($rgb[2] * 114)) / 255000;

		// values range from 0 to 1
		// anything greater than 0.5 should be bright enough for dark text
		return $brightness < 0.5;
	}

	/**
	 * Return CSS-style from styles array
	 * @since 3.0.0
	 * @param array $style_array
	 * @param bool $important
	 * @return string
	 */
	public static function prepare_styles($style_array, $important = TRUE)
	{
		if (!is_array($style_array)) {
			return false;
		}
		$delimiter = $important = $important ? ' !important;' : ';';
		$styles = implode($delimiter, array_map(
				function ($k, $v) {
					return $k . ':' . $v;
				},
				array_keys($style_array),
				$style_array
		)) . $delimiter;

		return $styles;
	}
}
