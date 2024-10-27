<?php
namespace Amoforms\Models\Styles;

use Amoforms\Traits\Singleton;
use Amoforms\Exceptions\Validate;
use Amoforms\Models\Styles\Types\Interfaces\Base_Style;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Manager
 * @since 3.0.0
 * @method static Manager instance
 * @package Amoforms\Models\Styles\Types
 */
class Manager implements Interfaces\Manager
{
	use Singleton;

	/**
	 * Make style instance by type
	 * @since 3.0.0
	 * @param string $type
	 * @param array  $db_params
	 * @return Base_Style
	 * @throws Validate
	 */
	public function make_style($type, array $db_params = NULL)
	{
		$type = (string)$type;
		$class = __NAMESPACE__ . '\Types\\' . ucfirst($type);
		if (!class_exists($class)) {
			throw new Validate("Class for style type '{$type}' not exists");
		}
		return new $class($db_params);
	}
}
