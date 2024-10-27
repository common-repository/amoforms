<?php
namespace Amoforms\Models\Forms;

use Amoforms\Traits\Singleton;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Manager
 * @since 1.0.0
 * @method static Manager instance
 * @package Amoforms\Models\Forms
 */
class Manager implements Interfaces\Manager
{
	use Singleton;

	/** @var \wpdb $_db */
	protected $_db;
	protected $_table = 'amoforms_forms';

	protected function __construct()
	{
		/** @var \wpdb $wpdb */
		global $wpdb;
		$this->_db = $wpdb;
		$this->_table = $this->_db->prefix . $this->_table;
	}

	public function get_db() {
		return $this->_db;
	}

	public function get_table() {
		return $this->_table;
	}

	public function create_table()
	{
		$this->_db->query("CREATE TABLE IF NOT EXISTS `{$this->_table}` (
		  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `settings` TEXT COLLATE utf8_unicode_ci NOT NULL,
		  `fields` TEXT COLLATE utf8_unicode_ci NOT NULL,
		  `version` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  `styles` MEDIUMTEXT COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`id`)
		) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

		return $this;
	}

	public function extend_table()
	{
		$this->_db->query("ALTER TABLE `{$this->_table}` ADD
		  `styles` MEDIUMTEXT COLLATE utf8_unicode_ci NOT NULL");
		return $this;
	}

	public function resize_table()
	{
		$this->_db->query("ALTER TABLE `{$this->_table}` MODIFY
		  `styles` MEDIUMTEXT");
		return $this;
	}

	public function get_form_by_id($id)
	{
		$id = (int)$id;
		$db_params = $this->_db->get_row("SELECT * FROM {$this->_table} WHERE id = {$id}", ARRAY_A);
		if (!$db_params) {
			return FALSE;
		}
		$form = new Form($db_params);

		return $form;
	}

	/**
	 * Get all forms params from db
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_all_forms_params()
	{
		return $this->_db->get_results("SELECT * FROM {$this->_table}", ARRAY_A) ?: [];
	}

	public function get_forms_collection()
	{
		$collection = new Collection();

		foreach ($this->get_all_forms_params() as $db_params) {
			$collection->add(new Form($db_params));
		}

		return $collection;
	}

	public function delete_form($id)
	{
		return (bool)$this->_db->query("DELETE FROM {$this->_table} WHERE id=" . (int)$id);
	}

	public function delete_all_forms()
	{
		return $this->_db->query("DELETE FROM {$this->_table}");
	}

	/**
	 * @return self|Interfaces\Form
	 */
	public function get_first_form()
	{
		return $this->get_first_form_from_db() ?: $this->create_default_form();
	}

	public function get_first_form_from_db()
	{
		$all_forms = $this->get_forms_collection();
		if ($all_forms->count()) {
			return $all_forms->remove_first();
		}
		return FALSE;
	}

	/**
	 * @return self|Interfaces\Form
	 */
	public function create_default_form()
	{
		return ((new Form())->set_default_fields()->set_default_styles());
	}

	public function check_and_create_form()
	{
		if (!$this->get_first_form_from_db()) {
			$this->create_default_form()->save();
		}
	}
}
