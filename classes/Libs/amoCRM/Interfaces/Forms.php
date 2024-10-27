<?php
namespace Amoforms\Libs\amoCRM\Interfaces;

use Amoforms\Exceptions\Runtime;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Forms
 * @package Amoforms\Libs\amoCRM\Interfaces
 */
Interface Forms {
	/**
	 * @param string $subdomain
	 * @param string $login
	 * @param string $api_key
	 * @param int $primary_authorization
	 * @param string $top_level_domain
	 *
	 * @return array [int $form_id, string $form_uid]
	 */
	public function register_form($subdomain, $login, $api_key, $primary_authorization, $top_level_domain);

	/**
	 * @param int $form_id
	 * @param string $form_uid
	 * @param string $subdomain
	 * @param string $login
	 * @param string $api_key
	 * @param int $primary_authorization
	 * @param string $top_level_domain
	 *
	 * @return bool
	 */
	public function update_form($form_id, $form_uid, $subdomain, $login, $api_key, $primary_authorization, $top_level_domain);

	/**
	 * @param int    $form_id
	 * @param string $form_uid
	 * @param string $email_to
	 * @param string $subject
	 * @param array  $fields
	 * @param array  $origin
	 * @param bool   $recently_registered
	 *
	 * @throws Runtime
	 *
	 * @return array [int $request_id, string $request_uid]
	 */
	public function register_result($form_id, $form_uid, $email_to, $subject, $fields, $origin, $recently_registered);

	/**
	 * @param bool $status - on / off
	 * @param string $subdomain
	 * @param string $login
	 * @param string $api_key
	 * @param string $top_level_domain
	 *
	 * @throws Runtime
	 *
	 * @return array
	 */
	public function update_ga_settings($status, $subdomain, $login, $api_key, $top_level_domain);
}
