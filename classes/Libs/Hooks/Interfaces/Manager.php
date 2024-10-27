<?php
namespace Amoforms\Libs\Hooks\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Manager
 * @since 2.11.8
 * @package Amoforms\Libs\Hooks\Interfaces
 */
interface Manager
{
	/**
	 * Register all hooks
	 * @since 2.11.8
	 * @return self
	 */
	public function register();
}
