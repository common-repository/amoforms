<?php
namespace Amoforms\Libs\Hooks;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Traits\Singleton;

/**
 * Class Manager
 * @since 2.11.8
 * @method static $this instance
 * @package Amoforms\Libs\Hooks
 */
class Manager implements Interfaces\Manager
{
	use Singleton;

	/** @var Interfaces\Hook[] $hooks */
	protected $_hooks = [];

	protected function __construct()
	{
		$this->_hooks = [
			MediaButtons\Manager::instance(),
			PreviewPage\Manager::instance(),
		];
	}

	public function register()
	{
		foreach ($this->_hooks as $hook) {
			$hook->register();
		}
	}
}
