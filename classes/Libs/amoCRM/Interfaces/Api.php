<?php
namespace Amoforms\Libs\amoCRM\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Api
 * @package Amoforms\Libs\amoCRM\Interfaces
 */
interface Api {
	/**
	 * @param string $email
	 * @param string $api_key
	 *
	 * @return bool
	 */
	public function is_auth($email, $api_key);

	/**
	 * @since 2.18.7
	 * @param string $login
	 * @param string $api_key
	 * @return array|bool
	 * @throws \Amoforms\Exceptions\Runtime
	 */
	public function get_account_info($login, $api_key);

	/**
	 * @param string $subdomain
	 * @param string $top_level_domain
	 *
	 * @return $this
	 */
	public function set_base_url($subdomain, $top_level_domain = AMOFORMS_DOMAIN_DEFAULT);

	/**
	 * @since 2.17.9
	 *
	 * @param string $subdomain
	 *
	 * @throws \Amoforms\Exceptions\Runtime
	 *
	 * @return string
	 */
	public function get_top_level_domain($subdomain);
}
