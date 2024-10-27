<?php
namespace Amoforms\Libs\Locale;

use Amoforms\Exceptions\Runtime;
use Amoforms\Traits\Singleton;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Date
 * @since 2.8.0
 * @method static $this instance
 * @package Amoforms\Libs\Locale
 */
class Date implements Interfaces\Date
{
	use Singleton;

	const DATE = 'date';
	const TIME = 'time';
	const FULL = 'full';

	const FORMAT_SITE = 'site';
	const FORMAT_DB   = 'db';

	protected $_formats = [
		self::FORMAT_SITE => [],
		self::FORMAT_DB   => [
			self::DATE => 'Y-m-d',
			self::TIME => 'H:i:s',
		],
	];

	protected function __construct()
	{
		$this->_formats[self::FORMAT_SITE] = [
			self::DATE => get_option('date_format'),
			self::TIME => get_option('time_format'),
		];

		foreach ($this->_formats as &$format) {
			$format[self::FULL] = $format[self::DATE] . ' ' . $format[self::TIME];
		}
		unset($format);
	}

	public function get_format($name, $type = NULL)
	{
		if (!isset($this->_formats[$name])) {
			throw new Runtime("Date format '{$name}' not found.");
		}
		if (!$type) {
			$type = self::FULL;
		}
		if (!isset($this->_formats[$name][$type])) {
			throw new Runtime("Undefined format type '{$type}'.");
		}
		return $this->_formats[$name][$type];
	}

	public function format($timestamp, $format_name, $type = NULL)
	{
		return date($this->get_format($format_name, $type), $timestamp);
	}

	public function format_gmt($gmt_timestamp, $format_name, $type = NULL)
	{
		$date = $this->format($gmt_timestamp, self::FORMAT_DB, $type);
		return get_date_from_gmt($date, $this->get_format($format_name));
	}
}
