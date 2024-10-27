<?php
namespace Amoforms\Libs\Captcha;

use Amoforms\Traits\Singleton;
use Amoforms\Exceptions\Runtime;
use Amoforms\Libs\Locale\NtpDate;
use Amoforms\Models\Forms;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Captcha
 * @since 2.11.0
 * @method static $this instance
 * @package Amoforms\Libs\Captcha
 */
class Captcha implements Interfaces\Captcha
{
	use Singleton;

	const G_CAPTCHA_SITE_KEY = '6LevJwwTAAAAAJPiNXt6658JfSd6MzYq6EHsQeTp';
	const G_CAPTCHA_SECRET_KEY = '6LevJwwTAAAAACCWocWRMuuLEvbl_wsUNhCdGSRu';
	const G_CAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

	public function can_use_captcha()
	{
		return (function_exists('openssl_encrypt') || function_exists('mcrypt_encrypt'));
	}

	public function get_captcha_token($ntp = FALSE)
	{
		if ($ntp) {
			$time = NtpDate::instance()->get_timestamp();
			if (!$time) {
				$time = microtime(TRUE);
			}
		} else {
			$time = microtime(TRUE);
		}
		$token_json = json_encode([
			'session_id' => wp_get_session_token(),
			'ts_ms'      => intval(round($time * 1000)),
		]);

		$token_json = strtr($token_json, ['+' => '-', '/' => '_', '=' => '']);

		return strtr($this->encrypt_token(self::G_CAPTCHA_SECRET_KEY, $token_json), ['+' => '-', '/' => '_', '=' => '']);
	}

	public function verify_response($response)
	{
		$result = file_get_contents(self::G_CAPTCHA_VERIFY_URL . '?' . http_build_query([
				'secret'   => self::G_CAPTCHA_SECRET_KEY,
				'response' => (string)$response,
				'remoteip' => $_SERVER['REMOTE_ADDR'],
			]));

		if (!$result || !($result = json_decode($result, TRUE))) {
			throw new Runtime('Invalid google response');
		}

		return (!empty($result['success']) && $result['success'] === TRUE);
	}

	/**
	 * Padding string for PKCS 5
	 * @since 2.11.0
	 * @param string $text
	 * @param int $block_size
	 * @return string
	 */
	protected function pkcs5_pad($text, $block_size)
	{
		$pad = $block_size - (strlen($text) % $block_size);
		return $text . str_repeat(chr($pad), $pad);
	}

	/**
	 * Encrypt token
	 * @since 2.11.0
	 * @param string $secret_key
	 * @param string $token_json
	 * @return string
	 * @throws Runtime
	 */
	protected function encrypt_token($secret_key, $token_json)
	{
		if (function_exists('openssl_encrypt')) {
			return $this->encrypt_by_openssl($secret_key, $token_json);
		} elseif (function_exists('mcrypt_encrypt')) {
			return $this->encrypt_by_mcrypt($secret_key, $token_json);
		} else {
			throw new Runtime('Encryption function not found');
		}
	}

	/**
	 * Encrypt the value of the secret key and token by using mcrypt_encrypt()
	 * @since 2.11.0
	 * @param string $secret_key
	 * @param string $token_json
	 * @return string
	 */
	protected function encrypt_by_mcrypt($secret_key, $token_json)
	{
		return base64_encode(
			mcrypt_encrypt(
				MCRYPT_RIJNDAEL_128,
				pack('H*', substr(hash('sha1', $secret_key), 0, 32)),
				$this->pkcs5_pad($token_json, 16),
				MCRYPT_MODE_ECB
			)
		);
	}

	/**
	 * Encrypt the value of the secret key and token by using openssl_encrypt()
	 * @since 2.11.0
	 * @param string $secret_key
	 * @param string $token_json
	 * @return string
	 */
	protected function encrypt_by_openssl($secret_key, $token_json)
	{
		return base64_encode(
			openssl_encrypt(
				$token_json,
				'AES-128-ECB', // Encryption method
				substr(hash('sha1', $secret_key, TRUE), 0, 16),
				OPENSSL_RAW_DATA // Give me the raw binary
			)
		);
	}
}
