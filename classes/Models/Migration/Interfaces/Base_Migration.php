<?php
namespace Amoforms\Models\Migration\Interfaces;

use Amoforms\Exceptions\Validate;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Base_Migration
 * @since 2.9.0
 * @package Amoforms\Models\Migration\Interfaces
 */
interface Base_Migration
{
	/**
	 * Checks whether migration is necessary.
	 * @param array $params - row from database with "version" field
	 * @return bool
	 */
	public function need_migrate($params);

	/**
	 * Migrate params to latest version
	 * @since 2.9.0
	 * @param array $params - decoded params from database
	 * @return array
	 * @throws Validate
	 */
	public function migrate($params);
}
