<?php
namespace Amoforms\Views\Interfaces;

use Amoforms\Views\Exceptions\Runtime;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Base
 * @since 1.0.0
 * @package Amoforms\Views\Interfaces
 */
interface Base
{
	/**
	 * Render view
	 * @since 1.0.0
	 * @param string $path
	 * @throws Runtime
	 */
	public function render($path);

	/**
	 * Get rendered template content
	 * @since 1.0.0
	 * @param string $path
	 * @return string
	 */
	public function get_content($path);

	/**
	 * Set data to view
	 * @since 1.0.0
	 * @param string $key
	 * @param mixed $value
	 * @return $this
	 */
	public function set($key, $value);

	/**
	 * Get data from view
	 * @since 1.0.0
	 * @param string $key
	 * @return mixed
	 */
	public function get($key);

	/**
	 * Get mustache engine
	 * @since 2.0.0
	 * @return \Mustache_Engine
	 */
	public function engine();
}
