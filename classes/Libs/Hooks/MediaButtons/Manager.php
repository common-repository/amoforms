<?php
namespace Amoforms\Libs\Hooks\MediaButtons;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Traits\Singleton;
use Amoforms\Libs\Hooks\Interfaces\Hook;

/**
 * Class Manager
 * @since 2.11.8
 * @method static $this instance
 * @package Amoforms\Libs\Hooks\MediaButtons
 */
class Manager implements \Amoforms\Libs\Hooks\Interfaces\Manager
{
	use Singleton;

	/**
	 * @var Hook[]
	 */
	protected $_buttons = [];

	protected function __construct()
	{
		$this->_buttons = [
			AddForm::instance(),
		];
	}

	public function register()
	{
		foreach ($this->_buttons as $button) {
			$button->register();
		}

		return $this;
	}
}
