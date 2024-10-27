<?php
namespace Amoforms\Libs\Sender\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Mail
 * @since 1.0.0
 * @package Amoforms\Libs\Sender\Interfaces
 */
interface Mail
{
	/**
	 * Send message
	 * @since 1.0.0
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @return bool - send result
	 */
	public function send($to, $subject, $message);

	/**
	 * Set flag for adding html header to email
	 * @since 1.0.0
	 * @param bool $value
	 * @return $this
	 */
	public function set_html($value);

	/**
	 * Set "From" field
	 * @since 1.0.0
	 * @param string $from
	 * @return $this
	 */
	public function set_from($from);

	/**
	 * Get value of "From" field
	 * @since 1.0.0
	 * @param string $default - default value
	 * @return string
	 */
	public function get_from($default = '');
}
