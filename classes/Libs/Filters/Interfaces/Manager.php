<?php
namespace Amoforms\Libs\Filters\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Manager
 * @since 1.0.0
 * @package Amoforms\Libs\Filters\Interfaces
 */
interface Manager
{
	/**
	 * Register all plugins
	 * @since 1.0.0
	 */
	public function register();
}
