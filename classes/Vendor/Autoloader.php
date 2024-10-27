<?php
namespace Amoforms\Vendor;

use Amoforms\Traits\Singleton;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Autoloader
 * @method static $this instance
 * @package Amoforms\Vendor
 */
class Autoloader
{
	use Singleton;

	public function register()
	{
		spl_autoload_register(function ($class) {
			$path = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
			if (file_exists($path)) {
				/** @noinspection PhpIncludeInspection */
				require_once $path;
			}
		});
	}
}
