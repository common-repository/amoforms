<?php
namespace Amoforms\Libs\FileSystem\Models;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

class Image extends File implements Interfaces\Image {
	protected $_current_dir_url;

	public function set_url($base_path) {
		$this->_info['url'] = $base_path . $this->get('basename');

		return $this;
	}

	public function make_thumb() {
		$image = wp_get_image_editor($this->_info['path']);
		if ($image instanceof \WP_Error) {
			throw new \RuntimeException('Image editor not created: ' . $image->get_error_message());
		}
		/** @var \WP_Image_Editor $image */
		$image->resize(100, 100);

		$file_name = $this->get('dirname') . '/' . $this->get('filename') . '.png';
		$this->rm();

		if (!$image->save($file_name, 'image/png')) {
			throw new \RuntimeException('Thumb not saved');
		}

		return new Thumb($file_name);
	}
}
