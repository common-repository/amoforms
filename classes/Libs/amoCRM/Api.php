<?php
namespace Amoforms\Libs\amoCRM;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Api
 * @package Amoforms\Libs\amoCRM
 * @method static Interfaces\Api instance()
 */
class Api extends Base implements Interfaces\Api {
	/**
	 * @inheritDoc
	 */
	public function is_auth($email, $api_key) {
		$data = [
			'USER_LOGIN' => $email,
			'USER_HASH'  => $api_key,
		];
		$response = $this->post('private/api/auth.php?type=json', $data);

		return isset($response['auth']) && $response['auth'] === TRUE;
	}

	/**
	 * @since 2.18.7
	 * @param string $login
	 * @param string $api_key
	 * @return array|bool
	 * @throws \Amoforms\Exceptions\Runtime
	 */
	public function get_account_info($login, $api_key)
	{
		$data = [
			'USER_LOGIN' => $login,
			'USER_HASH'  => $api_key,
		];
		$response = $this->get('private/api/v2/json/accounts/current', $data);

		return !empty($response['account']['id']) ? $response['account'] : FALSE;
	}

	/**
	 * @param string $subdomain
	 * @param string $top_level_domain
	 *
	 * @return $this
	 */
	public function set_base_url($subdomain, $top_level_domain = AMOFORMS_DOMAIN_DEFAULT) {
		$top_level_domain = in_array($top_level_domain, [AMOFORMS_DOMAIN_RU, AMOFORMS_DOMAIN_COM], TRUE) ? $top_level_domain : AMOFORMS_DOMAIN_DEFAULT;
		$this->_base_url = sprintf(AMOFORMS_API_BASE_URL_PATTERN, $subdomain, $top_level_domain);

		return $this;
	}

	/**
	 * @since 2.17.9
	 *
	 * @inheritDoc
	 */
	public function get_top_level_domain($subdomain) {
		$base_url = $this->_base_url;
		$this->_base_url = AMOFORMS_PROMO_BASE_URL;
		$response = $this->post('api/accounts/domains/', ['domains' => [$subdomain]], ['headers' => ['accept' => 'application/json', 'Accept-Encoding' => 'UTF-8']]);

		$top_level_domain = AMOFORMS_DOMAIN_DEFAULT;
		if (isset($response[0]['account_domain'])) {
			$top_level_domain = array_reverse(explode('.', $response[0]['account_domain']))[0];
		}
		$this->_base_url = $base_url;

		return $top_level_domain;
	}
}
