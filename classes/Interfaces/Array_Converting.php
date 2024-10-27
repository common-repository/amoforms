<?php
namespace Amoforms\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Array_Converting
 * @since 1.0.0
 * @package Amoforms\Interfaces
 */
interface Array_Converting
{
	/**
	 * Get array representation of object
	 * @since 1.0.0
	 * @return array
	 */
	public function to_array();
}
