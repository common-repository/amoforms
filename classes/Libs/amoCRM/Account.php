<?php
namespace Amoforms\Libs\amoCRM;

use Amoforms\Exceptions\Runtime;
use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Account
 * @package Amoforms\Libs\amoCRM
 * @method static Interfaces\Account instance()
 */
class Account extends Base implements Interfaces\Account {

	const TRIAL_DAYS = 14;

	/** @var array */
	protected $_cache = [];

	/**
	 * @inheritDoc
	 */
	public function can_register($email) {
		$response = $this->post('account/check_login.php', ['LOGIN' => $email]);

		if (!isset($response['mail'], $response['status'])) {
			throw new Runtime('Mail or status in response not defined');
		}

		$result = ($response['mail'] == $email && $response['status'] === 'free');

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function register_user($email, array $reg_data = []) {
		$account = [
			'first_name'    => !empty($reg_data['first_name']) ? $reg_data['first_name'] : $email,
			'name'          => !empty($reg_data['first_name']) ? $reg_data['first_name'] : $email,
			'email_address' => $email,
		];

		if (!empty($reg_data['phone_num'])) {
			$account['phone_num'] = $reg_data['phone_num'];
		}

		$data = [
			'account'            => $account,
			'ACTION'             => 'REGISTER_NEW_ACCOUNT_LANDING',
			'coupon_code'        => 'wordpress',
			'need_json_response' => TRUE,
			'site_url'           => site_url(),
		];

		$prefix = (I18n::get_lang() === 'es' ? 'es/' : '');
		$response = $this->post($prefix . 'account/api_account_registration.php?type=json', $data);

		if (isset($response['response'])) {
			$response = $response['response'];
		}

		if (!isset($response['user_api_key'], $response['accounts'][0]['subdomain'], $response['accounts'][0]['id'])) {
			throw new Runtime('Account create failed');
		}

		return [$response['user_api_key'], $response['accounts'][0]['subdomain'], (int)$response['accounts'][0]['id']];
	}
}
