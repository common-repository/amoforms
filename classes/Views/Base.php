<?php
namespace Amoforms\Views;

use Amoforms\Libs\UI\Mustache;
use Amoforms\Views\Exceptions\Runtime;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Base
 * @since 1.0.0
 * @package Amoforms\Views
 */
abstract class Base implements Interfaces\Base
{
	protected $_data = [];

	/** @var \Mustache_Engine $_engine */
	protected $_engine;

	public function __construct() {
		$this->_engine = Mustache::instance()->get_engine();
	}

	final public function render($path)
	{
		$path = AMOFORMS_VIEWS_DIR . '/' . $path . '.php';
		if (!file_exists($path)) {
			throw new Runtime('File for view not found in path: ' . $path);
		}
		/** @noinspection PhpIncludeInspection */
		require $path;
	}

	final public function get_content($path)
	{
		ob_start();
		$this->render($path);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	final public function set($key, $value)
	{
		$this->_data[$key] = $value;
		return $this;
	}

	final public function get($key)
	{
		return isset($this->_data[$key]) ? $this->_data[$key] : FALSE;
	}

	final public function engine()
	{
		return $this->_engine;
	}
}
