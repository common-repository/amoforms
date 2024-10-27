<?php
namespace Amoforms\Traits;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Singleton
 * @since 1.0.0
 * @package Amoforms\Traits
 */
trait Singleton
{
	protected static $_instances = [];

	public static function instance()
	{
		$class = get_called_class();

		if (!isset(self::$_instances[$class])) {
			self::$_instances[$class] = new $class();
		}

		return self::$_instances[$class];
	}

	protected function __construct() {}
	protected function __clone() {}
	protected function __sleep() {}
	protected function __wakeup() {}
}
