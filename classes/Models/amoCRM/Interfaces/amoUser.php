<?php
namespace Amoforms\Models\amoCRM\Interfaces;

use Amoforms\Exceptions\Argument;
use Amoforms\Exceptions\Runtime;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class amoUser for interact with amoCRM
 * @link https://www.amocrm.com
 * @link https://developers.amocrm.com
 * @package Amoforms\Models\amoCRM
 */
interface amoUser {

	/**
	 * Get current row data
	 *
	 * @param string $key
	 * @return array|int|string|NULL
	 */
	public function get_data($key = NULL);

	/**
	 * Get param value
	 *
	 * @since 2.17.9
	 *
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function get_param($key);

	/**
	 * @return bool
	 */
	public function is_full();

	/**
	 * Validate current data to save
	 *
	 * @return string|bool string when has error, FALSE otherwise
	 */
	public function validate();

	/**
	 * Save current settings for amo user
	 *
	 * @return \Amoforms\Models\amoCRM\amoUser
	 * @throws Runtime
	 */
	public function save();

	/**
	 * Set current row value
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return $this
	 */
	public function set($key, $value);

	/**
	 * Set value to param
	 *
	 * @since 2.16.12
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @throws Argument
	 *
	 * @return amoUser
	 */
	public function set_param($key, $value);

	/**
	 * Set last try date by timestamp
	 *
	 * @since 2.16.9
	 *
	 * @param int $timestamp
	 *
	 * @return self
	 */
	public function set_last_try($timestamp);

	/**
	 * Try to register user in amoCRM
	 * @param array $reg_data - additional registration data: [phone_num]
	 * @return bool
	 */
	public function try_to_register(array $reg_data = []);

	/**
	 * Checks whether the user is recently registered
	 *
	 * @since 2.16.12
	 *
	 * @return bool
	 */
	public function is_recently_registered();

	/**
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function has_connection();

	/**
	 * @since 2.18.7
	 * @return array|bool
	 */
	public function get_account_info();

	/**
	 * @since 2.18.7
	 * @return self
	 * @throws Argument
	 */
	public function update_account_id();

	/**
	 * @since 2.17.9
	 *
	 * @return $this
	 */
	public function set_top_level_domain();

	/**
	 * Get "primary" authorization type
	 *
	 * @since 2.16.5
	 *
	 * @return int
	 */
	public function get_primary_authorization_type();

	/**
	 * @since 2.9.0
	 *
	 * @return bool need to block form
	 */
	public function need_block();

	/**
	 * Check connection to amoCRM account and update its status
	 *
	 * @since 2.16.7
	 *
	 * @throws Runtime
	 *
	 * @return bool
	 */
	public function check_and_update_connection_status();

	/**
	 * Get url to account
	 * @since 2.16.6
	 *
	 * @param bool $with_auth - add auth params to url
	 * @param bool $protocol - add protocol to url
	 *
	 * @return string|bool
	 */
	public function get_account_url($with_auth = FALSE, $protocol = TRUE);
}
