<?php
namespace Amoforms\Libs\FileSystem\Collections;

use Amoforms\Libs\FileSystem\Models\Interfaces as Models;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Images
 * @package Amoforms\Libs\FileSystem\Collections
 * @method Models\Image find_by($key, $value)
 */
class Images extends Files implements Interfaces\Images {
	protected $_current_dir_url;
	protected $_file_class = '\Amoforms\Libs\FileSystem\Models\Image';

	function __construct($directory = '') {
		$this->_current_dir_url = AMOFORMS_IMAGES_URL . '/';
		$path = AMOFORMS_IMAGES_DIR . '/';

		if (!empty($directory)) {
			$this->_current_dir_url .= $directory . '/';
			$path .= $directory . '/';
		}

		parent::__construct($path);
	}

	protected function pre_attach($object) {
		$object = parent::pre_attach($object);

		if (!($object instanceof Models\Image)) {
			throw new \InvalidArgumentException('Object must be instance of Models\File');
		}

		$object->set_url($this->_current_dir_url);

		return $object;
	}
}
