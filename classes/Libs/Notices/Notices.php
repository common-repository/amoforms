<?php

namespace Amoforms\Libs\Notices;

use Amoforms\Traits\Singleton;
use Amoforms\Exceptions\Validate;

/**
 * Class Notices
 * @since 2.19.0
 * @method static Notices instance
 * @package Amoforms\Libs\Notices
 */
class Notices
{
	use Singleton;

	private $_statuses = ['show', 'hide'];
	private $_notices = ['promo', 'plugin', 'review'];
	const AMOFORMS_PREFIX = 'amoforms_notice_';
	const AMOFORMS_SHOW_TIMEOUT = 3;

	/**
	 * @since 2.19.0
	 * @param $notice_name
	 * @return bool
	 * @throws Validate
	 */
	public function get_notice_status($notice_name) {
		$showtime = TRUE;
		if(!in_array($notice_name, $this->_notices)){
			throw new Validate('Try to get undefined option');
		}
		$data = get_option(self::AMOFORMS_PREFIX.$notice_name, array());
		if (!in_array($data, $this->_statuses)){
			$showtime = ((int)$data <= time()) || empty($data);
		}
		return ($data != 'hide') && $showtime;
	}

	/**
	 * @since 2.19.0
	 * @param $notice_name
	 * @param $status
	 * @return bool
	 * @throws Validate
	 */
	public function set_notice_status($notice_name, $status){
		if(!in_array($notice_name, $this->_notices)){
			throw new Validate('Try to set undefined option');
		}
		if($status == 'time'){
			$status = strtotime("+" . self::AMOFORMS_SHOW_TIMEOUT . " day");
		}
		return (bool)update_option(self::AMOFORMS_PREFIX.$notice_name, $status);
	}

}
