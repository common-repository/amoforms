<?php
namespace Amoforms\Models\Entries;

use Amoforms\Models\Table\Base_Table;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Table
 * @since 2.8.0
 * @method static $this instance
 * @package Amoforms\Models\Entries
 */
class Table extends Base_Table implements Interfaces\Table
{
	protected $_base_name = 'amoforms_entries';
	protected $_fields = [
		'id'          => ['desc' => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT'],
		'form_id'     => ['desc' => 'INT(11) UNSIGNED NOT NULL'],
		'submit_date' => ['desc' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP'],
		'fields'      => ['desc' => 'TEXT COLLATE utf8_unicode_ci NOT NULL'],
		'user_ip'     => ['desc' => 'VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL'],
		'user_id'     => ['desc' => 'INT(11) UNSIGNED DEFAULT NULL'],
		'user_name'   => ['desc' => 'VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL'],
		'user_email'  => ['desc' => 'VARCHAR(150) COLLATE utf8_unicode_ci DEFAULT NULL'],
		'version'     => ['desc' => "VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''"],
	];
	protected $_keys = [
		'form_id' => 'form_id',
	];
}
