<?php
namespace Amoforms\Libs\amoCRM;

use Amoforms\Exceptions\Runtime;
use Amoforms\Traits\Singleton;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Base
 * @package Amoforms\Libs\amoCRM
 */
abstract class Base {
	use Singleton;

	/** @var string */
	protected $_base_url = AMOFORMS_PROMO_BASE_URL;

	/** @var NULL|array|\WP_Error */
	protected $_wp_response;

	const HTTP_CODE_CREATED = 201;

	/**
	 * @since 2.18.7
	 * @param string $type - post / get
	 * @param string $path
	 * @param array|string $data
	 * @param array $post_options
	 *
	 * @return array|NULL
	 * @throws Runtime
	 */
	private function request($type, $path, $data, $post_options = [])
	{
		if (!in_array($type, ['post', 'get'], TRUE)) {
			throw new Runtime('Invalid request type');
		}

		$url = $this->_base_url . $path;
		$options = [
			'user-agent' => 'WordPress-API-client/' . AMOFORMS_VERSION,
			'sslverify'  => FALSE, //TODO: change to TRUE
			'timeout' 	 => 60,
			'redirection' => 0,
		];

		if ($data) {
			if ($type === 'post') {
				$options['body'] = $data;
			} else {
				$url .= (strpos($url, '?') === FALSE ? '?' : '&') . http_build_query($data);
			}
		}

		$options = array_merge($options, $post_options);

		if ($type === 'post') {
			$this->_wp_response = wp_remote_post($url, $options);
		} else {
			$this->_wp_response = wp_remote_get($url, $options);
		}

		if (is_wp_error($this->_wp_response)) {
			if($this->_base_url == AMOFORMS_PROMO_BASE_URL_COM) {
				$this->_base_url = AMOFORMS_PROMO_BASE_URL_RU;
				add_option('USE_PROMO_RU', 'Y');
				$this->request($type, $path, $data, $post_options);
			} else{
				$error_message = $this->_wp_response->get_error_message();
				throw new Runtime('Post runtime error: ' . $error_message);
			}
		}
		$response = json_decode($this->_wp_response['body'], TRUE);

		if (isset($response['response'])) {
			$response = $response['response'];
		}

		return $response;
	}

	/**
	 * @since 2.18.7
	 * @param string $path
	 * @param array|string$data
	 * @param array $post_options
	 * @return array|NULL
	 * @throws Runtime
	 */
	protected function post($path, $data, $post_options = [])
	{
		return $this->request('post', $path, $data, $post_options);
	}

	/**
	 * @since 2.18.7
	 * @param string $path
	 * @param array|string $data
	 * @param array $post_options
	 * @return array|NULL
	 * @throws Runtime
	 */
	protected function get($path, $data, $post_options = [])
	{
		return $this->request('get', $path, $data, $post_options);
	}

	/**
	 * Get WordPress response
	 * @since 2.9.6
	 * @return array|NULL|\WP_Error
	 */
	protected function get_wp_response() {
		return $this->_wp_response;
	}
}
