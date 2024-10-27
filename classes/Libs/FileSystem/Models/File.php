<?php
namespace Amoforms\Libs\FileSystem\Models;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Libs\FileSystem\Collections\Interfaces\Files;

class File implements Interfaces\File {
	protected $_info;
	protected $_observers;

	public function __construct($path) {
		if (!file_exists($path)) {
			throw new \RuntimeException('File not exists');
		}

		if (!is_file($path)) {
			throw new \RuntimeException($path . ' is not a file');
		}

		$this->_info = pathinfo($path);
		$this->_info['path'] = $path;

		$this->_observers = new \SplObjectStorage();
	}

	public function rm() {
		unlink($this->_info['path']);
		$this->notify('delete');
	}

	public function copy_to($new_path) {
		$new_path = rtrim($new_path, '/') . '/' . $this->_info['basename'];
		if ($new_path === $this->_info['path']) {
			throw new \InvalidArgumentException('Copy to the same path!');
		}
		copy($this->_info['path'], $new_path);

		return new static($new_path);
	}

	public function get($key = NULL) {
		$result = $this->_info;
		if (!is_null($key)) {
			$result = isset($result[$key]) ? $result[$key] : NULL;
		}

		return $result;
	}

	public function attach(Files $observer) {
		$this->_observers->attach($observer);
	}

	public function detach(Files $observer) {
		$this->_observers->detach($observer);
	}

	public function notify($action, $params = []) {;
		/** @var Files $observer */
		foreach ($this->_observers as $observer) {
			$observer->update($this, $action, $params);
		}
	}
}
