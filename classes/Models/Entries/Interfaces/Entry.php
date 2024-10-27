<?php
namespace Amoforms\Models\Entries\Interfaces;

use Amoforms\Models;
use Amoforms\Interfaces\Array_Converting;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Entry
 * @since 2.8.0
 * @package Amoforms\Models\Entries\Interfaces
 */
interface Entry extends Models\Interfaces\Base, Array_Converting
{
	public function get_form_id();

	public function get_user_id();

	public function get_submit_date();

	public function get($key);

	public function set_form_id($id);

	public function set_fields(array $fields);

	public function set_user_ip($ip);

	public function set_user_id($id);

	public function set_user_name($name);

	public function set_user_email($email);

	public function save();
}
