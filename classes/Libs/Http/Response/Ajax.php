<?php
namespace Amoforms\Libs\Http\Response;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Ajax
 * @since 2.0.0
 * @package Amoforms\Libs\Http\Response
 */
class Ajax extends Base implements Interfaces\Ajax
{
	protected $_data = [
		'result' => AMOFORMS_AJAX_ERROR
	];

	protected $_reserved_keys = [
		'result',
		'message',
	];

	public function set_result($result) {
		$this->_data['result'] = $result ? AMOFORMS_AJAX_SUCCESS : AMOFORMS_AJAX_ERROR;
		return $this;
	}

	public function set_message($message) {
		$this->_data['message'] = (string)$message;
		return $this;
	}

	public function set($key, $value)
	{
		if (!in_array($key, $this->_reserved_keys, TRUE)) {
			$this->_data[$key] = $value;
		}
		return $this;
	}

	public function send($die = TRUE) {
		echo json_encode($this->_data);

		if ($die) {
			die;
		}

		return $this;
	}

	public function try_finish_request() {
		if ($this->can_finish_request()) {
			return fastcgi_finish_request();
		}

		return FALSE;
	}

	public function can_finish_request() {
		return function_exists('fastcgi_finish_request');
	}
}
