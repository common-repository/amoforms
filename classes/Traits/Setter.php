<?php
namespace Amoforms\Traits;

/**
 * Class Setter
 * @since 2.18.7
 * @package Amoforms\Traits
 */
trait Setter
{
	/**
	 * @since 2.18.7
	 * @param string $key
	 * @param mixed $value
	 * @return $this
	 */
	protected function _set($key, $value)
	{
		/** @noinspection PhpUndefinedFieldInspection */
		$this->_data[$key] = $value;

		return $this;
	}
}
