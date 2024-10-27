<?php
namespace Amoforms\Models\Base_List\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Base_List
 * @since 2.8.0
 * @package Amoforms\Models\Base_List\Interfaces
 */
interface Base_List
{
	/**
	 * Set page number from $_GET
	 * @return $this
	 */
	public function set_page_from_request();

	/**
	 * @param int $number
	 * @return $this
	 */
	public function set_page($number);

	/**
	 * Get current page
	 * @return int
	 */
	public function get_page();

	/**
	 * Get count of pages
	 * @return int
	 */
	public function get_pages_count();

	/**
	 * @param array $filter - array of fields and their filter values: ['id' => 1]
	 * @return $this
	 */
	public function set_filter(array $filter = []);

	/**
	 * Get array of db results
	 * @return array
	 */
	public function get();
}
