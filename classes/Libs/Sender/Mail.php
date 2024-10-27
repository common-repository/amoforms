<?php
namespace Amoforms\Libs\Sender;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Mail
 * @since 1.0.0
 * @package Amoforms\Libs\Sender
 */
class Mail implements Interfaces\Mail
{
	/** @var string */
	protected $_from = '';

	/** @var bool */
	protected $_is_html = FALSE;

	protected $_filter_name = 'wp_mail_from_name';

	public function __construct() {
		add_filter($this->_filter_name, [$this, 'get_from']);
	}

	public function __destruct() {
		remove_filter($this->_filter_name, [$this, 'get_from']);
	}

	public function send($to, $subject, $message) {
		$headers = [
			'MIME-Version: 1.0',
			'Content-type: text/' . ($this->_is_html ? 'html' : 'plain') . '; charset=UTF-8',
		];
		return wp_mail($to, $subject, $message, $headers);
	}

	public function set_html($value) {
		$this->_is_html = (bool)$value;
		return $this;
	}

	public function set_from($from) {
		$this->_from = (string)$from;
		return $this;
	}

	public function get_from($default = '') {
		return $this->_from ?: $default;
	}
}
