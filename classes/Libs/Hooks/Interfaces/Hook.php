<?php
namespace Amoforms\Libs\Hooks\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Hook
 * @since 2.11.8
 * @package Amoforms\Libs\Hooks\Interfaces
 */
interface Hook
{
	/**
	 * Register plugin
	 * @since 2.11.8
	 * @return Hook
	 */
	public function register();
}
