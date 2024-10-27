<?php
namespace Amoforms\Libs\Shortcodes;

use Amoforms\Controllers;
use Amoforms\Libs\Shortcodes\Exceptions\Manager_Runtime;
use Amoforms\Traits\Singleton;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Manager
 * @since 1.0.0
 * @method static $this instance
 * @package Amoforms\Libs\Shortcodes
 */
class Manager implements Interfaces\Manager
{
	use Singleton;

	/** @var \Amoforms\Controllers\Form $_form_controller */
	protected $_form_controller;

	/**
	 * @since 1.0.0
	 * @throws Manager_Runtime
	 */
	protected function __construct()
	{
		if (!function_exists('add_shortcode')) {
			throw new Manager_Runtime('Function "add_shortcode" is not exists');
		}
	}

	/**
	 * Register shortcode handler
	 * @since 1.0.0
	 */
	public function register_codes()
	{
		add_shortcode('amoforms', [$this, 'parse']);
	}

	/**
	 * Parse shortcode and show form
	 * @since 1.0.0
	 * @param array $attributes - array of shortcode attributes like 'id' in [amoforms id="123"]
	 //* @param string $content - content between two tags like [amoforms id="123"]some content[/amoforms]
	 * @return string
	 */
	public function parse($attributes/*, $content = NULL*/)
	{
		$result = '';

		if (!empty($attributes['id'])) {
			$result = $this->get_form_controller()->render_form_by_id((int)$attributes['id']);
		}

		return $result;
	}

	/**
	 * Get Form Controller
	 * @since 1.0.0
	 * @return Controllers\Form
	 */
	protected function get_form_controller()
	{
		if (!$this->_form_controller) {
			$this->_form_controller = new Controllers\Form();
		}
		return $this->_form_controller;
	}
}
