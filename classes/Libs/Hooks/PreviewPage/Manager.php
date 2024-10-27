<?php
namespace Amoforms\Libs\Hooks\PreviewPage;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Traits\Singleton;
use Amoforms\Libs\Hooks\Interfaces\Hook;

/**
 * Class Manager
 * @since 2.20.8
 * @method static $this instance
 * @package Amoforms\Libs\Hooks\PreviewPage
 */
class Manager implements \Amoforms\Libs\Hooks\Interfaces\Manager
{
	use Singleton;

	/**
	 * @var Hook[]
	 */
	protected $_forms = [];

	protected function __construct()
	{
		$this->_forms = [
			PutForm::instance(),
			ManagePage::instance(),
		];
	}

	public function register()
	{
		foreach ($this->_forms as $form) {
			$form->register();
		}

		return $this;
	}
}

