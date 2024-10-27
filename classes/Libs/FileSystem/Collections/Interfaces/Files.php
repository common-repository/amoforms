<?php
namespace Amoforms\Libs\FileSystem\Collections\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Libs\FileSystem\Models\Interfaces\File;

interface Files extends \Countable, \Iterator, \Serializable, \ArrayAccess {
	public function update(File $file, $action, $params = []);

	/**
	 * @param string $key
	 * @param string $value
	 *
	 * @return File|null
	 */
	public function find_by($key, $value);

	/**
	 * @return string|null
	 */
	public function get_path();
}
