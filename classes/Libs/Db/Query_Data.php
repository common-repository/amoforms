<?php
namespace Amoforms\Libs\Db;

use Amoforms\Exceptions\Validate;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

class Query_Data implements Interfaces\Query_Data
{
	const TYPE_STRING  = '%s';
	const TYPE_INTEGER = '%d';
	const TYPE_FLOAT   = '%f';

	protected $_formats = [
		self::TYPE_INTEGER => self::TYPE_INTEGER,
		self::TYPE_FLOAT   => self::TYPE_FLOAT,
		self::TYPE_STRING  => self::TYPE_STRING,
	];

	protected $_data = [];

	public function __construct(array $fields = NULL)
	{
		if ($fields) {
			$this->set_array($fields);
		}
	}

	public function set($key, $value, $format)
	{
		if (!isset($this->_formats[$format])) {
			throw new Validate('Undefined field format: ' . $format);
		}
		$this->_data[(string)$key] = ['value' => $value, 'format' => $format];

		return $this;
	}

	public function set_array(array $fields)
	{
		foreach ($fields as $key => $field) {
			if (!is_array($field)) {
				throw new Validate('Field must be array');
			}
			if (!isset($field[0])) {
				throw new Validate('Empty field format');
			}
			if (!array_key_exists(1, $field)) {
				throw new Validate('Empty field value');
			}
			list($format, $value) = $field;
			$this->set($key, $value, $format);
		}

		return $this;
	}

	public function get_data() {
		return $this->get_array('value');
	}

	public function get_formats() {
		return $this->get_array('format');
	}

	/**
	 * Get array of values/formats by key
	 * @param string $key
	 * @return array
	 */
	protected function get_array($key)
	{
		$result = [];
		foreach ($this->_data as $name => $params) {
			$result[$name] = $params[$key];
		}
		return $result;
	}
}
