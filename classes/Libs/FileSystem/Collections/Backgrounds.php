<?php
namespace Amoforms\Libs\FileSystem\Collections;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Libs\FileSystem\Models\Interfaces as Models;

/**
 * Class Backgrounds
 * @package Amoforms\Libs\FileSystem\Collections
 * @method Models\ImageWithThumb find_by($key, $value)
 */
class Backgrounds extends Images {
	/** @var Images */
	protected $_thumbs;
	protected $_file_class = '\Amoforms\Libs\FileSystem\Models\Background';

	protected $_path = 'bg';

	function __construct() {
		$this->detect_thumbs();
		parent::__construct($this->_path);
	}

	protected function detect_thumbs() {
		$this->_thumbs = new Thumbs($this->_path);
	}

	protected function pre_attach($object) {
		$object = parent::pre_attach($object);

		if (!($object instanceof Models\ImageWithThumb)) {
			throw new \InvalidArgumentException('Object must be instance of Models\ImageWithThumb');
		}

		/** @var Models\Thumb $thumb */
		if (!($thumb = $this->_thumbs->find_by('filename', $object->get('filename')))) {
			$thumb_blank = $object->copy_to($this->_thumbs->get_path());
			$thumb = $thumb_blank->make_thumb();
			$this->_thumbs->attach($thumb);
		}

		$object->set_thumb($thumb);

		return $object;
	}
}
