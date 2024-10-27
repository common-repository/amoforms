<?php
namespace Amoforms\Models\amoCRM\Tables;

use Amoforms\Models\Table\Base_Table;

/**
 * Class amoUser
 * @package Amoforms\Models\amoCRM\Tables
 * @method static $this instance
 */
class amoUser extends Base_Table {
	protected $_base_name = 'amoforms_amo_user';
	protected $_fields = [
		'id'            => ['desc' => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT'],
		'login'         => ['desc' => 'VARCHAR(255) NULL'],
		'api_key'       => ['desc' => 'VARCHAR(40) NULL'],
		'subdomain'     => ['desc' => 'VARCHAR(255) NULL'],
		'try_counts'    => ['desc' => 'TINYINT(2) NULL DEFAULT 0'],
		'last_try'      => ['desc' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'],
		'registered_by' => ['desc' => 'TINYINT(1) NOT NULL DEFAULT 1'],
		'changed_info'  => ['desc' => 'TINYINT(1) NOT NULL DEFAULT 1'],
		'params'        => ['desc' => 'TEXT COLLATE utf8_unicode_ci NOT NULL'],
	];

	public function resize_api_key_field()
	{
		$this->_db->query(
			sprintf(
				'ALTER TABLE %s MODIFY COLUMN `api_key` varchar(40) NULL',
				$this->get_name()
			)
		);

		return $this;
	}
}
