<?php
namespace Amoforms\Libs\Http\Response\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

interface Ajax extends Base
{
	/**
	 * @param bool $result
	 * @return $this
	 */
	public function set_result($result);

	/**
	 * @param string $message
	 * @return $this
	 */
	public function set_message($message);

	/**
	 * Set item to response data
	 * @param string $key
	 * @param mixed  $value
	 * @return $this
	 */
	public function set($key, $value);

	/**
	 * Send response data
	 * @param bool $die Die after echo or not
	 * @return self it not die.
	 */
	public function send($die = TRUE);

	/**
	 * Try to use fastcgi_finish_request
	 * @since 2.14.0
	 * @return bool
	 */
	public function try_finish_request();

	/**
	 * Check for finish request
	 * @since 2.14.0
	 * @return bool
	 */
	public function can_finish_request();
}
