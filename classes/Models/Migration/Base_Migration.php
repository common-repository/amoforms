<?php
namespace Amoforms\Models\Migration;

use Amoforms\Traits\Singleton;
use Amoforms\Exceptions\Validate;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Base_Migration
 * @since 2.9.0
 * @package Amoforms\Models\Migration
 */
abstract class Base_Migration implements Interfaces\Base_Migration
{
	use Singleton;

	protected $_versions = [];

	protected function __construct()
	{
		$this->_versions = json_decode(AMOFORMS_VERSIONS_JSON, TRUE);
	}

	public function need_migrate($params)
	{
		$this->check_params($params);
		return version_compare($params['version'], AMOFORMS_VERSION, '<');
	}

	public function migrate($params)
	{
		$this->check_params($params);

		if (version_compare($params['version'], '2.21.1', '<')) {
			$params['version'] = '2.21.1';
		}
		foreach ($this->_versions as $index => $version) {
			if (version_compare($params['version'], $version, '=')) {
				$method = 'migrate_from_' . str_replace('.', '_', $params['version']);
				if (method_exists($this, $method)) {
					$params = $this->$method($params);
					if (isset($this->_versions[$index + 1])) {
						$params['version'] = $this->_versions[$index + 1];
					}
				}
			}
		}
		return $params;
	}

	/**
	 * Check params
	 * @since 2.9.0
	 * @param array $params
	 * @throws Validate
	 */
	protected function check_params($params)
	{
		if (!is_array($params)) {
			throw new Validate('Params is not array');
		}
		if (empty($params['version'])) {
			throw new Validate('Empty params version');
		}
	}
}
