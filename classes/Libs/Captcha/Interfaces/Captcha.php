<?php
namespace Amoforms\Libs\Captcha\Interfaces;

use Amoforms\Exceptions\Runtime;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Captcha
 * @since 2.11.0
 * @package Amoforms\Libs\Captcha\Interfaces
 */
interface Captcha
{
	/**
	 * Detect whether can use captcha
	 * @since 2.11.0
	 * @return bool
	 */
	public function can_use_captcha();

	/**
	 * Encrypts a secret token for the Google recaptcha
	 * @since 2.11.0
	 * @return string
	 */
	public function get_captcha_token();

	/**
	 * Verify captcha response
	 * @since 2.11.0
	 * @param string $response - user's response to captcha
	 * @return bool
	 * @throws Runtime
	 */
	public function verify_response($response);
}
