<?php
namespace Amoforms\Helpers;

use Amoforms\Exceptions\Validate;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Arrays
 * @since 2.16.12
 * @package Amoforms\Helpers
 */
class Arrays
{
	/**
	 * Safety comparison of arrays.
	 * Uses strict string comparison of values.
	 * If the item of $one is an array, it will be checked strictly presence in another array $two.
	 *
	 * @since 2.16.12
	 *
	 * @param array $one
	 * @param array $two
	 *
	 * @return array - array containing all the entries from $one that are not present in $two
	 */
	public static function diff(array $one, array $two)
	{
		$result = [];

		foreach ($one as $key => $value) {
			if (is_array($value)) {
				if (!in_array($value, $two, TRUE)) {
					$result[$key] = $value;
				}
			} else {
				$exists = FALSE;
				$str_value = (string)$value;
				foreach ($two as $second_value) {
					if (!is_array($second_value) && $str_value === (string)$second_value) {
						$exists = TRUE;
						break;
					}
				}
				if (!$exists) {
					$result[$key] = $value;
				}
			}
		}

		return $result;
	}

	/**
	 * Safety comparison of associative arrays.
	 * Uses strict string comparison of values if $to_string is TRUE.
	 * If the item of $original is an array, it will be checked strictly presence in another array $another.
	 *
	 * @since 2.16.12
	 *
	 * @param array $original
	 * @param array $another
	 * @param bool $to_string
	 *
	 * @return array - array containing all the entries from $original that are not present in $another or different from $another.
	 */
	public static function diff_assoc(array $original, array $another, $to_string = TRUE)
	{
		$result = [];

		foreach ($original as $key => $value) {
			if (!array_key_exists($key, $another)) {
				$result[$key] = $value;
				continue;
			}

			if (is_array($value)) {
				if ($value !== $another[$key]) {
					$result[$key] = $value;
				}
			} else {
				if ($to_string) {
					if (is_array($another[$key]) || (string)$value !== (string)$another[$key]) {
						$result[$key] = $value;
					}
				} else {
					if ($value !== $another[$key]) {
						$result[$key] = $value;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Trim string values in array
	 *
	 * @since 2.15.0
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function trim_values(array $array)
	{
		foreach ($array as $key => $item) {
			if (is_string($item)) {
				$array[$key] = trim($item);
			} elseif (is_array($item)) {
				$array[$key] = self::trim_values($item);
			}
		}

		return $array;
	}

	/**
	 * Array validator for empty values
	 *
	 * @since 1.0.0
	 *
	 * @param array $array - array for checking
	 * @param array $keys - array of keys that should be checked: [id, settings => [email => [name, subject, to]]]
	 * @param string $prefix - internal parameter for building path of array keys
	 *
	 * @throws Validate
	 */
	public static function validate_for_empty(array $array, array $keys, $prefix = '')
	{
		foreach ($keys as $key => $value) {
			if (is_array($value)) {
				$path = $prefix . "[$key]";
				if (empty($array[$key])) {
					throw new Validate("Empty $path");
				}
				self::validate_for_empty($array[$key], $value, $path);
			} else {
				if (is_string($key) && is_string($value)) {
					$path = $prefix . "[$key][$value]";
					if (empty($array[$key][$value])) {
						throw new Validate("Empty $path");
					}
				} else {
					$key = (string)$value;
					$path = $prefix . "[$key]";
					if (empty($array[$key])) {
						throw new Validate("Empty $path");
					}
				}
			}
		}
	}
}
