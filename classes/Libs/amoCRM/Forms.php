<?php
namespace Amoforms\Libs\amoCRM;

use Amoforms\Exceptions\Runtime;
use Amoforms\Libs\Analytics\Analytics;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Forms
 * @package Amoforms\Libs\amoCRM
 * @method static Interfaces\Forms instance()
 */
class Forms extends Base implements Interfaces\Forms {

	protected $_base_url = AMOFORMS_FORMS_BASE_URL;

	/**
	 * @inheritDoc
	 */
	public function register_form($subdomain, $login, $api_key, $primary_authorization, $top_level_domain) {
		$response = $this->post('wordpress/register_form/', [
			'subdomain' => $subdomain,
			'login' => $login,
			'api_key' => $api_key,
			'primary_authorization' => $primary_authorization,
			'top_level_domain' => $top_level_domain,
		]);

		if (!isset($response['form_id'], $response['uid'])) {
			throw new Runtime('Form add failed');
		}

		return [$response['form_id'], $response['uid']];
	}

	/**
	 * @inheritDoc
	 */
	public function update_form($form_id, $form_uid, $subdomain, $login, $api_key, $primary_authorization, $top_level_domain) {
		$result = $this->post('wordpress/update_form/', [
			'form_id'   => $form_id,
			'uid' 	    => $form_uid,
			'subdomain' => $subdomain,
			'login'     => $login,
			'api_key'   => $api_key,
			'primary_authorization' => $primary_authorization,
			'top_level_domain' => $top_level_domain,
		]);

		return isset($result['status']) && $result['status'] === 'success';
	}

	/**
	 * @inheritDoc
	 */
	public function register_result($form_id, $form_uid, $email_to, $subject, $fields, $origin, $recently_registered) {
		$response = $this->post('wordpress/register_request/', [
			'form_id' => $form_id,
			'uid' => $form_uid,

			'site_url' => site_url(),
			'email_to' => $email_to,
			'subject' => $subject,
			'fields' => $fields,
			'recently_registered' => ($recently_registered ? 'y' : 'n'),
			'origin' => $origin,
		]);

		if (!isset($response['request_id'], $response['uid'])) {
			throw new Runtime('Result add failed');
		}

		return [$response['request_id'], $response['uid']];
	}

	/**
	 * @inheritdoc
	 */
	public function update_ga_settings($status, $subdomain, $login, $api_key, $top_level_domain)
	{
		$result = $this->post('wordpress/update_ga_settings/', [
			'enabled'   => $status ? Analytics::API_VALUE_ENABLED : Analytics::API_VALUE_DISABLED,
			'subdomain' => $subdomain,
			'login'     => $login,
			'api_key'   => $api_key,
			'top_level_domain' => $top_level_domain,
		]);

		return isset($result['status']) && $result['status'] === 'success';
	}
}
