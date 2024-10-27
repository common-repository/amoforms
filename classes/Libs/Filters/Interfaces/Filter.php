<?php
namespace Amoforms\Libs\Filters\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Filter
 * @since 1.0.0
 * @package Amoforms\Libs\Filters\Interfaces
 */
interface Filter
{
	/**
	 * Register plugin
	 * @since 1.0.0
	 * @return Filter
	 */
	public function register();
}
