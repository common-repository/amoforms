<?php
namespace Amoforms\Libs\FileSystem\Collections;

use Amoforms\Libs\FileSystem\Models\Interfaces as Models;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Thumbs
 * @package Amoforms\Libs\FileSystem\Collections
 * @method Models\Thumb find_by($key, $value)
 */
class Thumbs extends Images {
	protected $_file_class = '\Amoforms\Libs\FileSystem\Models\Thumb';

	function __construct($directory) {
		parent::__construct($directory . '/thumbs');
	}

	protected function pre_attach($object) {
		$object = parent::pre_attach($object);

		if (!($object instanceof Models\Thumb)) {
			throw new \InvalidArgumentException('Object must be instance of Models\Thumb');
		}

		return $object;
	}
}
