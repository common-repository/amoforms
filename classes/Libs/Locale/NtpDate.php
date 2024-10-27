<?php
namespace Amoforms\Libs\Locale;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Traits\Singleton;
use Bt51\NTP\Socket;
use Bt51\NTP\Client;

/**
 * Class NtpDate
 * @since 2.18.5
 * @method static NtpDate instance
 * @package Amoforms\Libs\Locale
 */
class NtpDate
{
	use Singleton;

	const HOST = 'pool.ntp.org';
	const PORT = 123;
	const TIMEOUT = 5;

	/** @var Client $_client */
	protected $_client;

	protected function __construct()
	{
		$this->_client = new Client(new Socket(self::HOST, self::PORT, self::TIMEOUT));
	}

	/**
	 * @since 2.18.5
	 * @return int
	 */
	public function get_timestamp()
	{
		return $this->_client->getTime()->getTimestamp();
	}
}
