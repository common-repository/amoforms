<?php
namespace Amoforms\Libs\amoCRM;

use Amoforms\Exceptions\Argument;
use Amoforms\Exceptions\Runtime;
use Amoforms\Helpers;
use Amoforms\Libs\Plugin\PluginSettings;
use Amoforms\Models\amoCRM\amoUser;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Stats
 * @since 2.9.6
 * @method static Stats instance
 * @package Amoforms\Libs\amoCRM
 */
class Stats extends Base implements Interfaces\Stats
{
	const REQUEST_TIMEOUT = 5;

	const EVENT_PLUGIN_INSTALLED = 'plugin_installed';
	const EVENT_FIRST_VIEW       = 'first_view';
	const EVENT_GET_STARTED      = 'get_started';
	const EVENT_REGISTRATION     = 'registration';
	const EVENT_FORM_SHOWN       = 'form_shown';
	const EVENT_FIRST_SUBMIT     = 'first_submit';

	const API_METHOD_EVENT_SET = 'wp_plugin_service/event/set/';

	/**
	 * @var \Amoforms\Models\amoCRM\Interfaces\amoUser
	 */
	protected $_user;


	/**
	 * @var PluginSettings
	 */
	protected $_plugin_settings;

	protected $_events_options = [
		self::EVENT_PLUGIN_INSTALLED => AMOFORMS_OPTION_INSTALL_DATE,
		self::EVENT_FIRST_VIEW       => AMOFORMS_OPTION_FIRST_VIEW_DATE,
		self::EVENT_GET_STARTED      => AMOFORMS_OPTION_GET_STARTED_DATE,
		self::EVENT_REGISTRATION     => AMOFORMS_OPTION_REGISTRATION_DATE,
		self::EVENT_FORM_SHOWN       => AMOFORMS_OPTION_FIRST_FORM_SHOW_DATE,
		self::EVENT_FIRST_SUBMIT     => AMOFORMS_OPTION_FIRST_FORM_SUBMIT_DATE,
	];

	protected function __construct()
	{
		$this->_user = amoUser::instance();
		$this->_plugin_settings = PluginSettings::instance();
	}

	/**
	 * @since 2.18.7
	 * @param string $event
	 * @param int|null $date
	 * @return bool
	 * @throws Argument
	 */
	public function set_event_date($event, $date = NULL)
	{
		$date = !is_null($date) ? (int)$date : time();

		return (bool)update_option($this->get_event_option($event), $date);
	}

	/**
	 * @since 2.18.7
	 * @param string $event
	 * @return int|bool
	 * @throws Argument
	 */
	public function get_event_date($event)
	{
		return $this->get_option_date($this->get_event_option($event));
	}

	/**
	 * @since 2.18.7
	 * @param string $event
	 * @return string
	 * @throws Argument
	 */
	protected function get_event_option($event)
	{
		if (!isset($this->_events_options[$event])) {
			throw new Argument('Undefined event: ' . $event);
		}

		return $this->_events_options[$event];
	}

	public function enable_reporting()
	{
		return update_option(AMOFORMS_OPTION_STATS_REPORTING, time());
	}

	public function install_event()
	{
		return $this->handle_date_event(self::EVENT_PLUGIN_INSTALLED);
	}

	public function form_shown_event()
	{
		return $this->handle_date_event(self::EVENT_FORM_SHOWN);
	}

	/**
	 * @inheritdoc
	 * @since 2.16.4
	 */
	public function form_submit_event()
	{
		return $this->handle_date_event(self::EVENT_FIRST_SUBMIT);
	}

	/**
	 * @since 2.18.7
	 * @param string $event_name
	 * @return bool
	 */
	public function send_event($event_name)
	{
		return $this->handle_date_event($event_name, TRUE);
	}

	/**
	 * Handle date event
	 * @since 2.16.4
	 * @param string $event
	 * @param bool $send_if_exists
	 * @return bool
	 * @throws Argument
	 */
	protected function handle_date_event($event, $send_if_exists = FALSE)
	{
		$result = FALSE;

		try {
			$date = $this->get_option_date($this->get_event_option($event));

			if ($date && !$send_if_exists) {
				return FALSE;
			}

			$this->send_event_data([
				'site_url'     => site_url(),
				'plugin_event' => $event,
				'account_id'   => $this->_user->get_param(amoUser::PARAM_ACCOUNT_ID),
				'ip'           => $this->get_real_ip(),
				'user_agent'   => $_SERVER['HTTP_USER_AGENT'],
				'version'      => AMOFORMS_VERSION,
				'plugin_uid'   => $this->_plugin_settings->get(PluginSettings::KEY_PLUGIN_UID),
			]);

			if (!$date) {
				$this->set_event_date($event, time());
			}

			$result = TRUE;

		} catch (\Exception $e) {
			if (AMOFORMS_DEV_ENV) {
				Helpers::handle_exception($e);
			}
		}

		return $result;
	}

	/**
	 * Send event
	 * @since 2.9.6
	 * @param array $data
	 * @return array|NULL
	 * @throws Runtime
	 */
	protected function send_event_data($data)
	{
		$this->post(self::API_METHOD_EVENT_SET, $data, [
			'timeout' => self::REQUEST_TIMEOUT,
		]);
		$response = $this->get_wp_response();

		if (empty($response['response']['code'])) {
			throw new Runtime('Event not sent. Empty response code');
		} elseif ((int)$response['response']['code'] !== self::HTTP_CODE_CREATED) {
			throw new Runtime('Event not sent. Invalid response code: ' . $response['response']['code']);
		}

		return $response;
	}

	/**
	 * Get option date
	 * @since 2.16.4
	 * @param string $option
	 * @return int|bool - timestamp or FALSE
	 */
	protected function get_option_date($option)
	{
		$timestamp = get_option($option);
		return $timestamp ? (int)$timestamp : FALSE;
	}

	public function get_reporting_start_date()
	{
		return $this->get_option_date(AMOFORMS_OPTION_STATS_REPORTING);
	}

	/**
	 * Get timestamp of plugin installation date
	 * @since 2.9.6
	 * @return int|FALSE
	 */
	protected function get_installation_date()
	{
		return $this->get_option_date(AMOFORMS_OPTION_INSTALL_DATE);
	}

	/**
	 * Get timestamp of first form show date
	 * @since 2.9.6
	 * @return int|FALSE
	 */
	protected function get_first_form_show_date()
	{
		return $this->get_option_date(AMOFORMS_OPTION_FIRST_FORM_SHOW_DATE);
	}

	/**
	 * Get timestamp of first form submit date
	 * @since 2.16.4
	 * @return int|bool
	 */
	protected function get_first_form_submit_date()
	{
		return $this->get_option_date(AMOFORMS_OPTION_FIRST_FORM_SUBMIT_DATE);
	}

	/**
	 * Get user real IP address
	 * @since 2.18.11
	 * @return string
	 */
	private function get_real_ip()
	{
		$result = '';

		if ((getenv("HTTP_CLIENT_IP") && strtolower(getenv("HTTP_CLIENT_IP")) !== "unknown")){
			$result = getenv("HTTP_CLIENT_IP");
		} elseif ((getenv("HTTP_X_FORWARDED_FOR") && strtolower(getenv("HTTP_X_FORWARDED_FOR")) !==  "unknown")){
			$result = getenv("HTTP_X_FORWARDED_FOR");
		} elseif((getenv("REMOTE_ADDR" && strtolower(getenv("REMOTE_ADDR")) !==  "unknown"))){
			$result = getenv("REMOTE_ADDR");
		} elseif ((!empty($_SERVER['REMOTE_ADDR']) && strtolower($_SERVER['REMOTE_ADDR']) !==  "unknown")){
			$result = $_SERVER['REMOTE_ADDR'];
		}

		return $result;
	}
}
