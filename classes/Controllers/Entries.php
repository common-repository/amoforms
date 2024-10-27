<?php
namespace Amoforms\Controllers;

use Amoforms\Exceptions\Runtime;
use Amoforms\Exceptions\Validate;
use Amoforms\Libs\Locale\Date;
use Amoforms\Models\Entries\Entries_List;
use Amoforms\Models\Entries\Interfaces\Entry;
use Amoforms\Models\Entries\Manager;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Entries
 * @since 2.8.0
 * @package Amoforms\Controllers
 */
class Entries extends Base
{
	public function get_capability() {
		return 'manage_options';
	}

	public function index_action() {
		$this->list_action();
	}

	/**
	 * List of entries
	 * @throws \Amoforms\Views\Exceptions\Runtime
	 */
	public function list_action()
	{
		$forms = \Amoforms\Models\Forms\Manager::instance()->get_forms_collection();
		$date_instance = Date::instance();
		$entries = [];
		$filter = [];
		if (!empty($_REQUEST['filter']['form_id'])) {
			$filter['form_id'] = (int)$_REQUEST['filter']['form_id'];
		}
		$entries_list = new Entries_List();
		$entries_list->set_filter($filter);
		$entries_collection = $entries_list->get_collection();
		$page = [
			'current' => $entries_list->get_page(),
			'total'   => $entries_list->get_pages_count(),
		];

		$columns = [
			'id'          => 'Entry',
			'form_name'   => 'Form',
			'submit_date' => 'Submitted Date',
			'user_name'   => 'User name',
			'user_email'  => 'User email',
		];

		/** @var Entry $entry */
		foreach ($entries_collection as $entry) {
			$new_entry = $entry->to_array();
			$new_entry['submit_date'] = $date_instance->format_gmt($entry->get_submit_date(), Date::FORMAT_SITE);
			$new_entry['form_name'] = 'Not found';
			if ($form = $forms->get_by_id($entry->get_form_id())) {
				$new_entry['form_name'] = $form->get('name');
			}
			$entries[] = $new_entry;
		}

		$this->_view
			->set('columns', $columns)
			->set('entries', $entries)
			->set('forms', $forms)
			->set('form_id', (!empty($filter['form_id'])) ? $filter['form_id'] : 0)
			->set('page', $page)
			->set('path', 'entries/pages/list')
			->render('entries/page');
	}

	/**
	 * Entry detail page
	 * @throws Validate
	 */
	public function detail_action()
	{
		if (empty($_GET['id'])) {
			throw new Validate('Empty entry id');
		}

		$id = (int)$_GET['id'];
		if (!$entry = Manager::instance()->get_by_id($id)) {
			throw new Runtime("Entry not found by id: {$id}");
		}

		$info = [
			'Entry ID'       => $entry->id(),
			'Form ID'        => $entry->get_form_id(),
			'Submitted date' => Date::instance()->format_gmt($entry->get_submit_date(), Date::FORMAT_SITE),
			'User ID'        => $entry->get('user_id'),
			'User Name'      => $entry->get('user_name'),
			'User Email'     => $entry->get('user_email'),
			'User IP'        => $entry->get('user_ip'),
		];

		$this->_view
			->set('entry', $entry->to_array())
			->set('info', $info)
			->set('path', 'entries/pages/detail')
			->render('entries/page');
	}
}
