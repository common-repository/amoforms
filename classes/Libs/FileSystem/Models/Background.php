<?php
namespace Amoforms\Libs\FileSystem\Models;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

class Background extends Image implements Interfaces\ImageWithThumb {
	/** @var Interfaces\Thumb */
	protected $_thumb;

	public function set_thumb(Interfaces\Thumb $thumb) {
		$this->_thumb = $thumb;

		return $this;
	}

	public function get_thumb() {
		return $this->_thumb;
	}
}
