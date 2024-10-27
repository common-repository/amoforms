<?php
namespace Amoforms\Libs\Db;

use Amoforms\Traits\Singleton;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Manager
 * @since 2.8.0
 * @method static $this instance
 * @package Amoforms\Libs\Db
 */
class Db_Manager
{
	use Singleton;

	/** @var \wpdb $_db */
	protected $_db;

	protected function __construct()
	{
		/** @var \wpdb $wpdb */
		global $wpdb;
		$this->_db = $wpdb;
	}

	/**
	 * @return \wpdb
	 */
	public function get_db() {
		return $this->_db;
	}
}
