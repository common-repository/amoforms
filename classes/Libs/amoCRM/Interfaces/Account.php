<?php
namespace Amoforms\Libs\amoCRM\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Account
 * @package Amoforms\Libs\amoCRM\Interfaces
 */
interface Account {
	/**
	 * Check if user already registered in amoCRM
	 *
	 * @param string $email
	 * @return bool
	 * @throws \Exception
	 */
	public function can_register($email);

	/**
	 * @param string $email
	 * @param array $reg_data - additional registration data: [phone_num]
	 * @return array contains $api_key, $account_subdomain
	 */
	public function register_user($email, array $reg_data = []);
}
