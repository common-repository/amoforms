<?php
namespace Amoforms\Controllers;

use Amoforms\Access;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Base
 * @since 1.0.0
 * @package Amoforms\Controllers
 */
abstract class Base
{
	/** @var \Amoforms\Views\Base */
	protected $_view;

	public function __construct()
	{
		$this->check_access();
		$class = str_replace('\\Controllers\\', '\\Views\\', get_called_class());
		if (!class_exists($class)) {
			throw new Exceptions\Controller("Class \"{$class}\" not found");
		}
		$this->_view = new $class;
	}

	/**
	 * Default action
	 * @since 2.8.0
	 */
	abstract public function index_action();

	/**
	 * Get controller capability
	 * @return string|TRUE - capability name or TRUE for public access
	 */
	abstract public function get_capability();

	/**
	 * Check access to controller
	 */
	protected function check_access()
	{
		$access = Access::instance();
		if (!$access->check($this->get_capability())) {
			$access->die_error();
		}
	}
}
