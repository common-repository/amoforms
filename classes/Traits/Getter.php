<?php
namespace Amoforms\Traits;

/**
 * Class Getter
 * @since 2.18.7
 * @package Amoforms\Traits
 */
trait Getter
{
	/**
	 * @since 2.18.7
	 * @param string|null $key1
	 * @param string|null $key2
	 * @return mixed|null
	 */
	protected function _get($key1 = NULL, $key2 = NULL)
	{
		$result = isset($this->_data) ? $this->_data : NULL;

		if (!is_null($key1)) {
			$result = isset($result[$key1]) ? $result[$key1] : NULL;
		}

		if (!is_null($key2)) {
			$result = is_array($result) && isset($result[$key2]) ? $result[$key2] : NULL;
		}

		return $result;
	}
}
