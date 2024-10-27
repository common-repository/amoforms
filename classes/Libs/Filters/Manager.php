<?php
namespace Amoforms\Libs\Filters;

use Amoforms\Traits\Singleton;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Manager
 * @since 1.0.0
 * @method static $this instance
 * @package Amoforms\Libs\Filters
 */
class Manager implements Interfaces\Manager
{
	use Singleton;

	protected $_filters = [];

	/**
	 * Register all plugins
	 * @since 1.0.0
	 */
	public function register()
	{
		$this->_filters['TinyMCE'] = new TinyMCE();

		/** @var Interfaces\Filter $filter */
		foreach ($this->_filters as $filter) {
			$filter->register();
		}
	}
}
