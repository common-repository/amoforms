<?php
namespace Amoforms\Models\Forms\Interfaces;

use Amoforms\Exceptions\Runtime;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

interface Manager
{
	/**
	 * Get forms database object
	 * @since 1.0.0
	 * @return \wpdb
	 */
	public function get_db();

	/**
	 * Get forms table
	 * @since 1.0.0
	 * @return string
	 */
	public function get_table();

	/**
	 * Creating table for storing forms
	 * @since 1.0.0
	 * @return self
	 */
	public function create_table();

	/**
	 * Get form by id
	 * @since 1.0.0
	 * @param int $id
	 * @return Form|bool
	 */
	public function get_form_by_id($id);

	/**
	 * Get collection of all forms instances from db
	 * @since 1.0.0
	 * @return Collection
	 * @throws \Amoforms\Exceptions\Argument
	 */
	public function get_forms_collection();

	/**
	 * Delete form by id
	 * @since 2.9.5
	 * @param int $id
	 * @return bool
	 */
	public function delete_form($id);

	/**
	 * Delete all forms from DB
	 * @since 2.5.0
	 * @return int|false
	 */
	public function delete_all_forms();

	/**
	 * Get first Form instance.
	 * If forms not exists, then new Form instance will be returned.
	 * @since 1.0.0
	 * @return Form
	 * @throws \Amoforms\Exceptions\Argument
	 */
	public function get_first_form();

	/**
	 * Get first form instance
	 * @since 2.0.0
	 * @return Form|bool
	 */
	public function get_first_form_from_db();

	/**
	 * Create default form instance
	 * @since 2.0.0
	 * @return Form
	 */
	public function create_default_form();

	/**
	 * Check form existing and create it if form not exists
	 * @since 2.0.0
	 * @throws Runtime
	 */
	public function check_and_create_form();
}
