<?php
namespace Amoforms\Libs\Locale\Interfaces;

use Amoforms\Exceptions\Runtime;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

interface Date
{
	/**
	 * Get date/time format
	 * @param string $name - format name
	 * @param string $type - format type: date/time/full
	 * @return string
	 * @throws Runtime
	 */
	public function get_format($name, $type = NULL);

	/**
	 * Format date
	 * @param int    $timestamp
	 * @param string $format_name
	 * @param string $type
	 * @return bool|string
	 */
	public function format($timestamp, $format_name, $type = NULL);

	/**
	 * Format GTM timestamp to local time
	 * @param int    $gmt_timestamp
	 * @param string $format_name
	 * @param string $type
	 * @return string
	 * @throws Runtime
	 */
	public function format_gmt($gmt_timestamp, $format_name, $type = NULL);
}
