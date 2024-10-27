<?php
namespace Amoforms\Libs\FileSystem\Models\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');


interface ImageWithThumb extends Image {
	/**
	 * @return Thumb|NULL
	 */
	public function get_thumb();

	/**
	 * @param Thumb $thumb
	 *
	 * @return $this
	 */
	public function set_thumb(Thumb $thumb);
}
