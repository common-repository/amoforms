<?php
namespace Amoforms\Libs\amoCRM\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Stats
 * @since 2.9.6
 * @package Amoforms\Libs\amoCRM\Interfaces
 */
interface Stats
{
	/**
	 * Enable reporting option
	 * @since 2.11.0
	 * @return bool
	 */
	public function enable_reporting();

	/**
	 * Get date of start reporting
	 * @since 2.11.0
	 * @return int|bool
	 */
	public function get_reporting_start_date();

	/**
	 * Send installation event
	 * @since 2.9.6
	 * @return bool
	 */
	public function install_event();

	/**
	 * Send form show event
	 * @since 2.9.6
	 * @return bool
	 */
	public function form_shown_event();

	/**
	 * Send first form submit event
	 * @since 2.16.4
	 * @return bool
	 */
	public function form_submit_event();
}
