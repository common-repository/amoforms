<?php
namespace Amoforms\Models\amoCRM;

use Amoforms\Exceptions\Argument;
use Amoforms\Exceptions\Runtime;
use Amoforms\Helpers\Arrays;
use Amoforms\Libs\amoCRM as amoLibs;
use Amoforms\Libs\Analytics\Analytics;
use Amoforms\Libs\Locale\Date;
use Amoforms\Models\Forms\Manager;
use Amoforms\Traits\Singleton;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class amoUser for interact with amoCRM
 * @link https://www.amocrm.com
 * @link https://developers.amocrm.com
 * @package Amoforms\Models\amoCRM
 * @method static Interfaces\amoUser instance
 */
class amoUser implements Interfaces\amoUser {
	use Singleton;

	const REGISTERED_BY_SELF = 1;
	const REGISTERED_BY_WP = 2;

	const USER_INFO_NOT_CHANGED = 1;
	const USER_INFO_CHANGED = 2;

	const PRIMARY_AUTHORIZATION = 2;
	const SECONDARY_AUTHORIZATION = 1;

	const MAX_CONNECTION_TRY_COUNTS = 12;

	const PARAM_REGISTRATION_DATE = 'registration_date';
	const PARAM_TOP_LEVEL_DOMAIN = 'top_level_domain';
	const PARAM_GA = 'ga';
	const PARAM_ACCOUNT_ID = 'account_id';

	/** @var \wpdb */
	private $_db;

	/** @var amoLibs\Interfaces\Account */
	private $_account;

	/** @var amoLibs\Interfaces\Api */
	private $_api;

	/** @var string */
	private $_table = 'amoforms_amo_user';

	/** @var array */
	private $_fields = [
		'id',
		'login',
		'api_key',
		'subdomain',
		'try_counts',
		'last_try',
		'registered_by',
		'changed_info',
		'params',
	];

	/** @var array */
	private $_int_fields = [
		'id',
		'try_counts',
		'registered_by',
		'changed_info',
	];

	/** @var array */
	private $_json_fields = [
		'params',
	];

	/** @var array */
	private $_data = [];

	/** @var array */
	private $_db_data = [];

	/** @var array */
	private $_allowed_params = [
		self::PARAM_REGISTRATION_DATE => TRUE,
		self::PARAM_TOP_LEVEL_DOMAIN  => TRUE,
		self::PARAM_GA                => TRUE,
		self::PARAM_ACCOUNT_ID        => TRUE,
	];

	/** @var array */
	private $_default_params = [
		self::PARAM_REGISTRATION_DATE => NULL,
		self::PARAM_TOP_LEVEL_DOMAIN  => NULL,
		self::PARAM_GA                => [],
		self::PARAM_ACCOUNT_ID        => NULL,
	];

	protected function __construct() {
		$this->_db = Manager::instance()->get_db();
		$this->_table = $this->_db->prefix . $this->_table;

		$this->_account = amoLibs\Account::instance();
		$this->_api = amoLibs\Api::instance();
		$this->_default_params[self::PARAM_GA] = Analytics::get_default_settings();
		$this->install();
	}

	/**
	 * Install amoUser table
	 * @since 2.9.0
	 */
	private function install() {
		$table = Tables\amoUser::instance();
		if (!$table->create()) {
			throw new Runtime("Error creating table " . $table->get_name());
		}

		$table->check_fields();
		$data = $this->get_data();
		$this->set('params', array_merge($this->_default_params, isset($data['params']) ? $data['params'] : []));

		if (!$this->get_param(self::PARAM_TOP_LEVEL_DOMAIN)) {
			if ($this->get_data('subdomain') && $this->get_data('login') && $this->get_data('api_key')) {
				$this->set_top_level_domain()->_save();
			}
		}
		if (!$this->get_param(self::PARAM_ACCOUNT_ID)) {
			if ($this->get_data('subdomain') && $this->get_data('login') && $this->get_data('api_key')) {
				$this->update_account_id()->_save();
			}
		}
	}

	/**
	 * Get current row data
	 *
	 * @param string $key
	 * @return array|int|string|NULL
	 */
	public function get_data($key = NULL) {
		if (!$this->_data) {
			$fields = implode(', ', $this->_fields);
			if ($row = $this->_db->get_row("SELECT {$fields} FROM {$this->_table}", ARRAY_A)) {
				foreach ($row as $field => $value) {
					if ($field === 'last_try') {
						$value = strtotime($value);
					} elseif (in_array($field, $this->_json_fields, TRUE)) {
						if (!$value) {
							$value = [];
						} else {
							$value = json_decode($value, TRUE);
							$value = is_array($value) ? $value : [];
						}
					}

					if (in_array($field, ['last_try', 'try_counts'], TRUE)) {
						$value = (int)$value;
						if (($value) < 0) {
							$value = 0;
						}
					}

					if (in_array($field, ['registered_by', 'changed_info'], TRUE)) {
						$value = (int)$value;
					}

					$this->set($field, $value);
				}
			}

			$this->_db_data = $this->_data;
		}

		$result = $this->_data ? $this->_data : [];
		if (!is_null($key)) {
			$result = isset($result[$key]) ? $result[$key] : NULL;
		}

		return $result;
	}

	/**
	 * Get param value
	 *
	 * @since 2.16.12
	 *
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function get_param($key)
	{
		$params = $this->get_data('params');

		$result = is_array($params) && isset($params[$key]) ? $params[$key] : NULL;

		return $result;
	}

	/**
	 * @return bool
	 */
	public function is_full() {
		$result = TRUE;
		foreach (['login', 'api_key', 'subdomain'] as $field) {
			if (!$this->get_data($field)) {
				$result = FALSE;
				break;
			}
		}

		return $result;
	}

	/**
	 * Validate current data to save
	 *
	 * @return string|bool string when has error, FALSE otherwise
	 */
	public function validate() {
		$result = FALSE;

		if (!$this->get_data('login') || !filter_var($this->get_data('login'), FILTER_VALIDATE_EMAIL)) {
			$result = 'Invalid login';
		}

		if (empty($this->get_data('api_key'))) {
			$result = 'Invalid api key';
		}

		if (isset($this->_data['subdomain'])) {
			$this->set('subdomain', strtolower($this->get_data('subdomain')));
			if ($subdomain = preg_replace("/[^a-z0-9]/", '', $this->get_data('subdomain'))) {
				if ($subdomain !== $this->get_data('subdomain')) {
					unset($subdomain);
				}
			}
		}

		if (!isset($subdomain)) {
			$result = 'Invalid subdomain';
		}

		return $result;
	}

	/**
	 * Save current settings for amo user
	 *
	 * @return $this
	 * @throws Runtime
	 */
	public function save() {
		$new_data = $this->get_data();

		if ($this->get_data('registered_by') !== amoUser::REGISTERED_BY_WP) {
			$this->set('registered_by', amoUser::REGISTERED_BY_SELF);
		}

		if (!$this->get_data('changed_info')) {
			$this->set('changed_info', self::USER_INFO_NOT_CHANGED);
		}

		if (Arrays::diff_assoc($new_data, $this->_db_data)) {
			$is_changed_info = FALSE;
			foreach (['login', 'api_key', 'subdomain'] as $key) {
				if (isset($this->_db_data[$key]) && isset($new_data[$key]) && $this->_db_data[$key] !== $new_data[$key]) {
					$is_changed_info = TRUE;
					break;
				}
			}

			if ($is_changed_info) {
				$this->set('changed_info', self::USER_INFO_CHANGED);
			}

			$this->_save();
		}

		return $this;
	}

	protected function _save() {
		$format = $where = $where_format = $data = [];
		foreach ($this->_fields as $field) {
			$value = $this->get_data($field);
			if ($field === 'id') {
				continue;
			}

			if (in_array($field, $this->_json_fields, TRUE)) {
				$value = json_encode(is_array($value) ? $value : [], JSON_UNESCAPED_UNICODE);
			}

			$data[$field] = $value;
			if (in_array($field, $this->_int_fields, TRUE)) {
				$format[] = '%d';
			} else {
				$format[] = '%s';
			}
		}

		if (!$data) {
			return;
		}

		if ($this->get_data('id')) {
			$where['id'] = $this->get_data('id');
			$where_format[] = '%d';
			$this->_db->update($this->_table, $data, $where, $format, $where_format);
		} else {
			$this->_db->insert($this->_table, $data, $format);
			if (!($this->_data['id'] = $this->_db->insert_id)) {
				throw new Runtime('Data not inserted correctly!');
			}
		}

		$this->_db_data = $this->_data;
	}

	/**
	 * Set current row value
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return $this
	 */
	public function set($key, $value) {
		if (in_array($key, $this->_fields, TRUE)) {
			$this->_data[$key] = $value;
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 * @since 2.16.12
	 */
	public function set_param($key, $value)
	{
		if (!isset($this->_allowed_params[$key])) {
			throw new Argument("Invalid user param '{$key}'");
		}
		$params = $this->get_data('params') ?: [];
		$params[$key] = $value;
		$this->set('params', $params);

		return $this;
	}

	/**
	 * Set last try date by timestamp
	 *
	 * @since 2.16.9
	 *
	 * @param int $timestamp
	 *
	 * @return self
	 */
	public function set_last_try($timestamp)
	{
		$this->set('last_try', Date::instance()->format((int)$timestamp, Date::FORMAT_DB, Date::FULL));

		return $this;
	}

	/**
	 * Try to register user in amoCRM
	 * @param array $reg_data
	 * @return bool
	 */
	public function try_to_register(array $reg_data = []) {
		$result = FALSE;

		if ($this->get_data('login')) {
			if ($this->_account->can_register($this->get_data('login'))) {
				try {
					list($api_key, $subdomain, $account_id) = $this->_account->register_user($this->get_data('login'), $reg_data);

					$this
						->set('api_key', $api_key)
						->set('subdomain', $subdomain)
						->set('registered_by', self::REGISTERED_BY_WP)
						->set('try_counts', 0)
						->set_last_try(time())
						->set_param(self::PARAM_ACCOUNT_ID, $account_id)
						->set_param(self::PARAM_REGISTRATION_DATE, time());

					$result = TRUE;
				} catch (\Exception $ex) {

				}
			}
		}

		return $result;
	}

	/**
	 * Checks whether the user is recently registered
	 *
	 * @since 2.16.12
	 *
	 * @return bool
	 */
	public function is_recently_registered()
	{
		$reg_date = $this->get_param(self::PARAM_REGISTRATION_DATE);

		return $reg_date && (((time() - $reg_date) / DAY_IN_SECONDS) <= amoLibs\Account::TRIAL_DAYS);
	}

	/**
	 * @return bool
	 */
	public function has_connection() {
		$result = FALSE;

		if ($this->get_data('subdomain') && $this->get_data('login') && $this->get_data('api_key')) {
			try {
				$result = $this->_api
					->set_base_url($this->get_data('subdomain'), $this->get_param(self::PARAM_TOP_LEVEL_DOMAIN))
					->is_auth($this->get_data('login'), $this->get_data('api_key'));
			} catch (\Exception $ex) {
			}
		}

		return $result;
	}

	/**
	 * @return array|bool
	 */
	public function get_account_info()
	{
		$result = FALSE;

		if ($this->get_data('subdomain') && $this->get_data('login') && $this->get_data('api_key')) {
			try {
				$result = $this->_api
					->set_base_url($this->get_data('subdomain'), $this->get_param(self::PARAM_TOP_LEVEL_DOMAIN))
					->get_account_info($this->get_data('login'), $this->get_data('api_key'));
			} catch (\Exception $ex) {
			}
		}

		return $result;
	}

	/**
	 * @since 2.18.7
	 * @return self
	 * @throws Argument
	 */
	public function update_account_id()
	{
		if ($this->get_data('subdomain') && $this->get_data('login') && $this->get_data('api_key')) {
			$account_info = $this->get_account_info();
			if (is_array($account_info) && !empty($account_info['id'])) {
				$this->set_param(self::PARAM_ACCOUNT_ID, (int)$account_info['id']);
			}
		}

		return $this;
	}

	/**
	 * @since 2.17.9
	 *
	 * @return $this
	 */
	public function set_top_level_domain() {
		if ($this->get_data('subdomain') && $this->get_data('login') && $this->get_data('api_key')) {
			$top_level_domain = $this->_api->get_top_level_domain($this->get_data('subdomain'));
			$this->set_param(self::PARAM_TOP_LEVEL_DOMAIN, $top_level_domain);
		}

		return $this;
	}

	/**
	 * Get "primary" authorization type
	 *
	 * @since 2.16.5
	 *
	 * @return int
	 */
	public function get_primary_authorization_type()
	{
		if ($this->get_data('registered_by') === self::REGISTERED_BY_WP && $this->get_data('changed_info') === self::USER_INFO_NOT_CHANGED) {
			return self::PRIMARY_AUTHORIZATION;
		} else {
			return self::SECONDARY_AUTHORIZATION;
		}
	}

	/**
	 * @since 2.9.0
	 *
	 * @return bool need to check connection to amoCRM
	 */
	private function need_check() {
		$last_try = $this->get_data('last_try');
		$result = !$last_try;

		if ($last_try) {
			$hours_from_last_check = (time() - $last_try) / (60 * 60);
			if ($hours_from_last_check > 1) {
				$result = TRUE;
			}
			/* old logic
			$try_counts = (int)$this->get_data('try_counts');
			switch(TRUE) {
				case $try_counts < 1 && $hours_from_last_check > 24:
				case $try_counts === 1 && $hours_from_last_check > 12:
				case $try_counts === 2 && $hours_from_last_check > 8:
				case $try_counts === 3 && $hours_from_last_check > 6:
				case $try_counts === 4 && $hours_from_last_check > 4:
				case $try_counts > 4:
					$result = TRUE;
					break;
			}
			*/
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function need_block() {
		return intval($this->get_data('try_counts')) > self::MAX_CONNECTION_TRY_COUNTS;
	}

	/**
	 * Check connection to amoCRM account and update its status
	 *
	 * @since 2.16.7
	 *
	 * @throws Runtime
	 *
	 * @return bool
	 */
	public function check_and_update_connection_status()
	{
		if ($has_connection = !$this->need_check()) {
			return $has_connection;
		}

		$try_counts = (int)$this->get_data('try_counts');
		$need_save = FALSE;

		if (!$this->get_data('last_try')) {
			$this->set_last_try(time());
			$this->set('try_counts', 1)->save();
			$this->set('try_counts', 0);
			$need_save = TRUE;
		}

		if ($try_counts <= self::MAX_CONNECTION_TRY_COUNTS) {
			$has_connection = $this->has_connection();
			$this->set_last_try(time());
			if (!$has_connection) {
				$this->set('try_counts', ++$try_counts);
			} else {
				$this->set('try_counts', 0);
			}
			$need_save = TRUE;
		}

		if ($need_save) {
			$this->_save();
		}

		return $has_connection;
	}

	/**
	 * Get url to account
	 * @since 2.16.6
	 *
	 * @param bool $with_auth - add auth params to url
	 * @param bool $protocol - add protocol to url
	 *
	 * @return string|bool
	 */
	public function get_account_url($with_auth = FALSE, $protocol = TRUE)
	{
		$result = FALSE;
		if ($subdomain = $this->get_data('subdomain')) {
			$top_level_domain = $this->get_param(self::PARAM_TOP_LEVEL_DOMAIN) ?: AMOFORMS_DOMAIN_DEFAULT;
			$result = sprintf(AMOFORMS_API_BASE_URL_PATTERN, $subdomain, $top_level_domain);
			if ($with_auth) {
				if (($login = $this->get_data('login')) && ($hash = $this->get_data('api_key'))) {
					$result .= '?USER_LOGIN=' . $login . '&USER_HASH=' . $hash;
				} else {
					return FALSE;
				}
			}
		}

		if ($result && !$protocol) {
			$result = str_replace('https://', '', $result);
			$result = str_replace('http://', '', $result);
		}

		return $result;
	}
}
