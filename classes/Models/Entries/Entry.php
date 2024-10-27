<?php
namespace Amoforms\Models\Entries;

use Amoforms\Exceptions\Runtime;
use Amoforms\Helpers;
use Amoforms\Helpers\Strings;
use Amoforms\Libs\Db\Db_Manager;
use Amoforms\Libs\Db\Query_Data;
use Amoforms\Libs\Locale\Date;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Entry
 * @since 2.8.0
 * @package Amoforms\Models\Entries
 */
class Entry implements Interfaces\Entry
{
	/** @var \wpdb */
	protected $_db;

	/** @var Interfaces\Table */
	protected $_table;

	protected $_data = [
		'id'          => NULL,
		'form_id'     => NULL,
		'submit_date' => NULL,
		'fields'      => [],
		'user_ip'     => NULL,
		'user_id'     => NULL,
		'user_name'   => NULL,
		'user_email'  => NULL,
	];

	protected $_field_keys = [
		'name',
		'type',
		'value'
	];

	/**
	 * @param array|NULL $db_params - array of params from DB
	 */
	public function __construct(array $db_params = NULL)
	{
		$this->_db = Db_Manager::instance()->get_db();
		$this->_table = Table::instance();

		if (!is_null($db_params)) {
			$this->set_db_params($db_params);
		}
	}

	/**
	 * Get entry data as array
	 * @return array
	 */
	public function to_array() {
		return $this->_data;
	}

	/**
	 * @return int|NULL
	 */
	public function id() {
		return $this->_data['id'];
	}

	/**
	 * @return int|NULL
	 */
	public function get_form_id() {
		return $this->_data['form_id'];
	}

	/**
	 * @return int|NULL
	 */
	public function get_user_id() {
		return $this->_data['user_id'];
	}

	/**
	 * @return int|NULL
	 */
	public function get_submit_date() {
		return $this->_data['submit_date'];
	}

	/**
	 * Get entry property
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		return isset($this->_data[$key]) ? $this->_data[$key] : FALSE;
	}

	/**
	 * Save entry to db only if it not saved.
	 * @return int - entry id
	 * @throws Runtime
	 */
	public function save()
	{
		if ($this->id()) {
			throw new Runtime('Entry already saved');
		}
		if (!$this->get_form_id()) {
			throw new Runtime('Empty form id');
		}

		$this->set_submit_date('now');

		$data = new Query_Data([
			'form_id'     => ['%d', $this->get('form_id')],
			'submit_date' => ['%s', Date::instance()->format($this->get('submit_date'), Date::FORMAT_DB)],
			'fields'      => ['%s', json_encode(Helpers::strip_slashes($this->get('fields')), JSON_UNESCAPED_UNICODE)],
			'user_ip'     => ['%s', $this->get('user_ip')],
			'version'     => ['%s', AMOFORMS_VERSION],
		]);

		if ($this->get_user_id()) {
			$data->set_array([
				'user_id'    => ['%d', $this->get('user_id')],
				'user_name'  => ['%s', $this->get('user_name')],
				'user_email' => ['%s', $this->get('user_email')],
			]);
		}

		$this->_db->insert($this->_table->get_name(), $data->get_data(), $data->get_formats());
		if (!$id = $this->_db->insert_id) {
			throw new Runtime("Can't get id of inserted entry");
		}
		$this->set_id($id);

		return $this;
	}

	/**
	 * Set params form DB
	 * @param array $params
	 */
	protected function set_db_params(array $params)
	{
		foreach ($this->_data as $key => $default_value) {
			$method = "set_$key";
			if (isset($params[$key]) && method_exists($this, $method)) {
				if (is_array($default_value)) {
					$params[$key] = json_decode($params[$key], TRUE);
				}
				$this->$method($params[$key]);
			}
		}
	}

	/**
	 * @param int $id
	 * @return $this
	 */
	protected function set_id($id) {
		$this->_data['id'] = abs((int)$id);
		return $this;
	}

	/**
	 * @param int $id
	 * @return $this
	 */
	public function set_form_id($id) {
		$this->_data['form_id'] = abs((int)$id);
		return $this;
	}

	/**
	 * @param string $date
	 * @return $this
	 */
	protected function set_submit_date($date) {
		$this->_data['submit_date'] = strtotime($date);
		return $this;
	}

	/**
	 * Set fields values.
	 * Values must be already escaped!
	 * @param array $fields - array of fields: [[name => '', type => '', value => ''], ...].
	 * @return $this
	 */
	public function set_fields(array $fields)
	{
		foreach ($fields as $field) {
			$new_field = [];
			foreach ($this->_field_keys as $key) {
				if (!isset($field[$key])) {
					continue 2;
				}
				$new_field[$key] = $field[$key];
			}
			$this->_data['fields'][] = $new_field;
		}
		return $this;
	}

	/**
	 * @param string $ip
	 * @return $this
	 */
	public function set_user_ip($ip)
	{
		$this->_data['user_ip'] = Strings::escape($ip);
		return $this;
	}

	/**
	 * @param int $id
	 * @return $this
	 */
	public function set_user_id($id)
	{
		$this->_data['user_id'] = abs((int)$id);
		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function set_user_name($name)
	{
		$this->_data['user_name'] = Strings::escape($name);
		return $this;
	}

	/**
	 * @param string $email
	 * @return $this
	 */
	public function set_user_email($email)
	{
		$this->_data['user_email'] = Strings::escape($email);
		return $this;
	}
}
