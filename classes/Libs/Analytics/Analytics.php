<?php
namespace Amoforms\Libs\Analytics;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Exceptions\Runtime;
use Amoforms\Libs\amoCRM\Forms;
use Amoforms\Models\amoCRM\amoUser;
use Amoforms\Traits\Singleton;

/**
 * Class Analytics
 * @since 2.18.0
 * @method static $this instance
 * @package Amoforms\Libs\Analytics
 */
class Analytics implements Interfaces\AnalyticsInterface
{
	use Singleton;

	const DATA_SOURCE = 'wordpress';

	const API_VALUE_ENABLED = 'y';
	const API_VALUE_DISABLED = 'n';

	/** @var amoUser */
	protected $_user;

	/** @var Forms */
	protected $_forms_api;

	/** @var array */
	protected static $_default_settings = [
		'enabled' => FALSE,
	];

	/**
	 * Sourcebuster aliases
	 * @var array
	 */
	protected $_sbjs_aliases = [
		'current'     => [
			'typ' => 'type',
			'src' => 'source',
			'mdm' => 'medium',
			'cmp' => 'campaign',
			'cnt' => 'content',
			'trm' => 'term',
		],
		'current_add' => [
			'fd' => 'fire_date',
			'ep' => 'entrance_point',
			'rf' => 'referer',
		],
		'session'     => [
			'pgs' => 'pages_seen',
			'cpg' => 'current_page',
		],
		'udata'       => [
			'vst' => 'visits',
			'uip' => 'ip',
			'uag' => 'agent',
		],
	];

	/**
	 * @var array
	 */
	protected $_default_analytics_data = [
		'ga'          => [
			'clientId'   => '',
			'trackingId' => '',
		],
		'utm'         => [
			'current' => [
				'type'           => '', // utm
				'source'         => '', // google
				'medium'         => '', // cpc
				'campaign'       => '', // google_cpc
				'content'        => '', // (none)
				'term'           => '', // (none)
				'entrance_point' => '',
				'referer'        => '',
				'fire_date'      => '',
			],
			'session' => [
				'pages_seen'   => '',
				'current_page' => '',
			],
			'udata'   => [
				'visits' => '',
				'ip'     => '',
				'agent'  => '',
			],
		],
		'datetime'    => '',
		'data_source' => self::DATA_SOURCE,
	];

	protected function __construct()
	{
		$this->_user = amoUser::instance();
		$this->_forms_api = Forms::instance();

		$this->_sbjs_aliases['first'] = $this->_sbjs_aliases['current'];
		$this->_sbjs_aliases['first_add'] = $this->_sbjs_aliases['current_add'];

		$this->_default_analytics_data['utm']['first'] = $this->_default_analytics_data['utm']['current'];
		$this->_default_analytics_data['utm']['udata']['ip'] = $_SERVER['REMOTE_ADDR'];

		if (!empty($_SERVER['HTTP_USER_AGENT'])) {
			$this->_default_analytics_data['utm']['udata']['agent'] = $_SERVER['HTTP_USER_AGENT'];
		}
	}

	/**
	 * @since 2.18.0
	 * @return array
	 */
	public static function get_default_settings()
	{
		return self::$_default_settings;
	}

	/**
	 * @since 2.18.0
	 * @return array
	 */
	protected function get_settings()
	{
		return $this->_user->get_param(amoUser::PARAM_GA) ?: [];
	}

	/**
	 * @since 2.18.0
	 * @return bool
	 */
	public function is_enabled()
	{
		$settings = $this->get_settings();

		return !empty($settings['enabled']) && $settings['enabled'] === TRUE;
	}

	/**
	 * Enable or disable Google Analytics
	 * @since 2.18.0
	 * @param bool $on - on / off
	 * @throws Runtime
	 */
	public function toggle($on)
	{
		$on = (bool)$on;

		if (!$this->_user->is_full()) {
			throw new Runtime('User settings is empty');
		}

		$result = $this->_forms_api->update_ga_settings(
			$on,
			$this->_user->get_data('subdomain'),
			$this->_user->get_data('login'),
			$this->_user->get_data('api_key'),
			$this->_user->get_param(amoUser::PARAM_TOP_LEVEL_DOMAIN)
		);
		if (!$result) {
			throw new Runtime('Error updating Google Analytics settings');
		}

		$settings = $this->get_settings();
		$settings['enabled'] = $on;
		$this->_user->set_param(amoUser::PARAM_GA, $settings)->save();
	}

	/**
	 * @since 2.18.0
	 * @param array $post
	 * @return array
	 */
	public function extract_analytics_data_from_post(array $post)
	{
		$result = $this->_default_analytics_data;
		if (empty($post['analytics']) || !is_string($post['analytics'])) {
			return $result;
		}

		$data = json_decode(stripslashes($post['analytics']), TRUE);
		if (!$data || !is_array($data)) {
			return $result;
		}

		if (!empty($data['datetime'])) {
			$result['datetime'] = $data['datetime'];
		}

		foreach ($this->_default_analytics_data['ga'] as $key => $v) {
			if (isset($data['ga'][$key])) {
				$result['ga'][$key] = $data['ga'][$key];
			}
		}

		foreach ($this->_sbjs_aliases as $key => $params) {
			if (isset($data['sbjs'][$key])) {
				foreach ($params as $param_key => $name) {
					if (isset($data['sbjs'][$key][$param_key])) {
						if ($param_key === 'uip') {
							continue; // we already got IP from $_SERVER['REMOTE_ADDR']
						}
						$result_key = $key;
						if ($key === 'current_add' || $key === 'first_add') {
							$result_key = str_replace('_add', '', $key);
						}
						$result['utm'][$result_key][$name] = $data['sbjs'][$key][$param_key];
					}
				}
			}
		}

		return $result;
	}
}
