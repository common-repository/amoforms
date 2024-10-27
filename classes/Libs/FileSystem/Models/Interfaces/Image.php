<?php
namespace Amoforms\Libs\FileSystem\Models\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

interface Image extends File {
	/**
	 * @param string $base_path
	 *
	 * @return $this
	 */
	public function set_url($base_path);

	/**
	 * @return Thumb
	 */
	public function make_thumb();
}
