<?php
namespace Amoforms\Libs\GeoIP;

use Amoforms\Traits\Singleton;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class IpInfo
 * @since 2.15.8
 * @package Amoforms\Libs\GeoIP
 * @method static IpInfo instance
 */
class IpInfo
{
	use Singleton;

	/**
	 * @param string $ip
	 * @return bool
	 */
	private function is_valid_id($ip)
	{
		return $ip && is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
	}

	/**
	 * @param string $ip
	 * @return array|bool - Info array or FALSE
	 */
	public function get_info($ip)
	{
		if ($this->is_valid_id($ip)) {
			$wp_response = wp_remote_get("http://ipinfo.io/{$ip}/geo", [
				'sslverify'   => FALSE,
				'timeout'     => 2,
				'redirection' => 0,
			]);

			if (!is_wp_error($wp_response)
				&& !empty($wp_response['response']['code'])
				&& $wp_response['response']['code'] == 200
				&& !empty($wp_response['body'])
				&& ($country_data = json_decode($wp_response['body'], TRUE))
				&& is_array($country_data)
				&& !empty($country_data['country'])
			) {
				return $country_data;
			}
		}

		return FALSE;
	}
}
