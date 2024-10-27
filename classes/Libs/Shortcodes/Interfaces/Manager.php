<?php
namespace Amoforms\Libs\Shortcodes\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Manager
 * @since 1.0.0
 * @package Amoforms\Libs\Shortcodes\Interfaces
 */
interface Manager
{
	/**
	 * Register shortcode handler
	 * @since 1.0.0
	 */
	public function register_codes();

	/**
	 * Parse shortcode and show form
	 * @since 1.0.0
	 * @param array $attributes - array of shortcode attributes like 'id' in [amoforms id="123"]
	 * @return string
	 */
	public function parse($attributes);
}
