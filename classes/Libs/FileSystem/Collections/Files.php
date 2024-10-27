<?php
namespace Amoforms\Libs\FileSystem\Collections;

use Amoforms\Libs\FileSystem\Models\Interfaces as Models;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

class Files extends \SplObjectStorage implements Interfaces\Files {
	protected $_current_dir;
	protected $_file_class = '\Amoforms\Libs\FileSystem\Models\File';

	function __construct($path = '') {
		if (!class_exists($this->_file_class)) {
			throw new \RuntimeException('Wrong file class');
		}

		if (!empty($path)) {
			$this->_current_dir = $path;
		}

		if (!file_exists($this->_current_dir)) {
			throw new \RuntimeException('Path ' . $path . ' not exists');
		}

		$this->read_dir();
	}

	private function read_dir() {
		$files = scandir($this->_current_dir);
		foreach ($files as $key => $file_name) {
			$path = $this->_current_dir . $file_name;

			if (in_array($file_name, ['.', '..'], TRUE)) {
				unset($files[$key]);
				continue;
			}

			if (!is_file($path)) {
				unset($files[$key]);
				continue;
			}

			/** @var Models\File $file */
			try {
				$this->attach(new $this->_file_class($path));
			} catch (\Exception $ex) {

			}
		}
	}

	protected function pre_attach($object) {
		if (!($object instanceof Models\File)) {
			throw new \InvalidArgumentException('Object must be instance of Models\File');
		}

		if (!(is_a($object, $this->_file_class))) {
			throw new \InvalidArgumentException('Object must be object of ' . $this->_file_class);
		}

		if (($object->get('dirname') . '/') !== $this->_current_dir) {
			$object = $object->copy_to($this->_current_dir);
		}

		return $object;
	}

	public function attach($object, $data = NULL) {
		/** @var Models\File $object */
		$object = $this->pre_attach($object);

		parent::attach($object, $data);
		$object->attach($this);
	}

	public function update(Models\File $file, $action, $params = []) {
		if ($action == 'delete') {
			$this->detach($file);
		}
	}

	public function find_by($key, $value) {
		$result = NULL;

		foreach ($this as $file) {
			if ($file->get($key) === $value) {
				$result = $file;
				break;
			}
		}

		return $result;
	}

	public function get_path() {
		return $this->_current_dir;
	}
}
