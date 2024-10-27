<?php
namespace Amoforms;

use Amoforms\Libs\amoCRM\Stats;
use Amoforms\Models\amoCRM\amoUser;
use Amoforms\Models\Forms\Interfaces\Form;
use Amoforms\Models\Forms;
use Amoforms\Traits\Singleton;
use Amoforms\Exceptions\Validate;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Router
 * @since 1.0.0
 * @method static Router instance
 * @package Amoforms
 */
class Router
{
	use Singleton;

	/**
	 * URL to http://site.domain/wp-admin/admin.php
	 * @var string
	 */
	protected $_admin_url;

	/** @var string */
	protected $_page = '';

	/** @var string */
	protected $_controller = '';

	/** @var string */
	protected $_action = '';

	/** @var string|NULL */
	protected $_param;

	/** @var bool */
	protected $_is_ajax = FALSE;

	/** @var bool */
	protected $_is_json_request = FALSE;

	/** @var array|null */
	protected $_json_body;

	/** @var string */
	protected $_ajax_prefix = 'amoforms_';

	protected $_menu_title = 'amoForms';
	protected $_menu_slug_prefix = 'amoforms';
	protected $_main_menu_slug;
	protected $_sub_menu_items = [];
	protected $_has_blocked_form = FALSE;
	protected $_settings_capability = 'manage_options';
	protected $_is_registered;
	protected $_need_first_setup;
	protected $_page_id;
	protected $_form_id;

	/**
	 * Items of top menu
	 * @var array
	 */
	protected $_top_menu = [
		[
			'name'       => 'Edit Fields',
			'controller' => 'settings',
			'action'     => 'form',
		],
		[
			'name'       => 'Form Settings',
			'controller' => 'settings',
			'action'     => 'edit_form',
		],
		[
			'name'       => 'Email Settings',
			'controller' => 'settings',
			'action'     => 'edit_email',
		],
		[
			'name'       => 'Form Preview',
			'controller' => 'settings',
			'action'     => 'form_preview',
		],
		/* Not used
		[
			'name'       => 'Account',
			'controller' => 'settings',
			'action'     => 'edit_account',
		],
		*/
	];

	/**
	 * Ajax endpoints.
	 * The resulting values ​​will be as: $this->_ajax_prefix . 'add_field'
	 * Example: amoforms_add_field
	 * Endpoint will be: /wp-admin/admin-ajax.php?action=amoforms_add_field
	 * @var array
	 */
	protected $_ajax_actions = [
		'private' => [
			// First setup
			'Get started'               => 'get_started',
			'Send "Get started" event'  => 'send_get_started_event',

			// Fields
			'Add field'                 => 'add_field',
			'Duplicate field'           => 'duplicate_field',
			'Delete field'              => 'delete_field',
			'Edit field'                => 'edit_field',
			'Update fields'             => 'update_fields',

			//Styles
			'Edit style'                => 'edit_style',
			'Reset Field style'         => 'reset_field_style',
			'Reset Form style'          => 'reset_form_style',

			// Form
			'Update submit button'      => 'update_submit_button',
			'Update design settings'    => 'update_design_settings',
			'Upload background'         => 'upload_form_background',
			'Duplicate form'            => 'duplicate_form',
			'Delete form'               => 'delete_form',

			// Images
			'Delete form background'    => 'delete_form_background',

			// Form settings
			'Check amo form settings'   => 'update_form',

			// Email settings
			'Check amo user is free'    => 'check_email',
			'Check login for register'  => 'can_register',
			'Check amo email settings'  => 'update_email',
			'Send registration event'   => 'send_registration_event',

			// Account settings
			'Check amo acc credentials' => 'has_connection',
			'Update first setup'        => 'update_first_setup_settings',

			// Preview page
			'Update custom code'        => 'update_custom_code_settings',

			//Notices
			'Dismiss notice'			=> 'dismiss_notice',
			'Suspend notice'			=> 'suspend_notice',
			// Errors
			'Send error'                => 'send_error',
		],
		'protected' => [
			'Submit amo form'           => 'submit',
		],
		'public' => [

		],
	];

	/**
	 * @since 1.0.0
	 */
	protected function __construct()
	{
		$this->_admin_url = admin_url() . 'admin.php';

		$this->_is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) === 'XMLHTTPREQUEST');
		if (!$this->_is_ajax) {
			// fix for some cases when HTTP_X_REQUESTED_WITH is not provided
			$this->_is_ajax = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin-ajax.php') !== FALSE;
		}
		$this->_page = !empty($_GET['page']) ? (string)$_GET['page'] : '';
		$this->_page_id = !empty($_GET['page_id']) ? (int)$_GET['page_id'] : '';
		$this->_form_id = !empty($this->_page_id) && !empty($_GET['amoforms_form_id']) ? (int)$_GET['amoforms_form_id'] : '';
		$parts = explode('-', $this->_page);
		if (!empty($parts[1])) {
			$this->_controller = $parts[1];
		} elseif (isset($_GET['controller'])) {
			$this->_controller = $_GET['controller'];
		} else {
			$this->_controller = 'Settings';
		}

		$this->_action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : (!empty($parts[2]) ? $parts[2] : 'index');

		if ($this->_is_ajax && strpos($this->_action, $this->_ajax_prefix) === 0) {
			$this->_action = str_replace($this->_ajax_prefix, '', $this->_action);
		}

		if (!empty($parts[3])) {
			$this->_param = (string)$parts[3];
		}
		if (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'json') !== FALSE) {
			$this->_is_json_request = TRUE;
		}

		$this->is_registered();
		$this->check_setup();
		$this->init_admin_menu();
		$this->init_ajax_actions();
	}

	/**
	 * Get full url to /wp-admin/admin.php
	 * @since 2.9.5
	 * @return string
	 */
	public function get_admin_url() {
		return $this->_admin_url;
	}

	/**
	 * Check, whether the request is ajax
	 * @since 2.5.0
	 * @return bool
	 */
	public function is_ajax() {
		return $this->_is_ajax;
	}

	/**
	 * @since 2.19.10
	 * @return bool
	 */
	public function is_json_request() {
		return $this->_is_json_request;
	}

	/**
	 * @since 2.19.10
	 * @return array
	 */
	public function get_json_body()
	{
		if (is_null($this->_json_body)) {
			$this->_json_body = [];

			if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($_POST)) {
				return $this->_json_body;
			}

			if ($data = file_get_contents('php://input')) {
				$data = json_decode($data, TRUE);
				if (is_array($data)) {
					$this->_json_body = $data;
				}
			}
		}

		return $this->_json_body;
	}

	/**
	 * Get current action
	 * @since 2.9.5
	 * @return string
	 */
	public function get_action() {
		return $this->_action;
	}

	/**
	 * Get param from URL like this: /wp-admin/admin.php?page=amoforms-settings-form-4
	 * It will be: 4
	 * @since 2.9.5
	 * @return string|NULL
	 */
	public function get_param() {
		return $this->_param;
	}

	/**
	 * Get current page
	 * @since 2.15.0
	 * @return string
	 */
	public function get_page() {
		return $this->_page;
	}

	/**
	 * Get current page ID
	 * @since 2.20.8
	 * @return string
	 */
	public function get_page_id() {
		return $this->_page_id;
	}

	/**
	 * Get form_id from request
	 * @since 2.20.8
	 * @return string
	 */
	public function get_form_id() {
		return $this->_form_id;
	}

	/**
	 * Get top menu items
	 * @since 2.9.5
	 * @return array
	 */
	public function get_top_menu_items() {
		return $this->_top_menu;
	}

	/**
	 * Get top menu URLs
	 * @param int $form_id
	 * @return array
	 */
	public function get_top_menu_urls($form_id)
	{
		$result = [];
		foreach ($this->get_top_menu_items() as $item) {
			$result[] = [
				'name' => $item['name'],
				'page' => $this->_menu_slug_prefix . '-' . $item['controller'] . '-form-' . $form_id,
				'action' => $item['action'],
			];
		}
		return $result;
	}

	/**
	 * Get admin menu items with their full actions
	 * @since 2.8.0
	 * @return array
	 */
	public function get_sub_menu_items() {
		return $this->_sub_menu_items;
	}

	/**
	 * Get prefix for form page $_GET-argument.
	 * Example url: /wp-admin/admin.php?page=amoforms-settings-form-4
	 * @return string
	 */
	public function get_form_page_prefix() {
		return $this->_menu_slug_prefix . '-settings-form-';
	}

	/**
	 * Init sub menu items
	 * Items in format: ['name' => 'Item name', 'slug' => 'controller-action_name'].
	 * Slug example: amoforms-settings-edit_form
	 * URL of each item will be: /wp-admin/admin.php?page=amoforms-settings-form-35&action=edit_form&form[id]=35
	 * @since 2.9.5
	 * @return array
	 */
	protected function init_admin_sub_menu_items()
	{
		$this->_sub_menu_items = [];
		$this->_has_blocked_form = FALSE;

		/** @var Form $form */
		foreach (Forms\Manager::instance()->get_forms_collection() as $form) {
			$slug = $this->get_form_page_prefix() . $form->id();
			if (!$this->_main_menu_slug) {
				$this->_main_menu_slug = $slug;
			}
			$this->_sub_menu_items[] = [
				'name' => $form->get('name'),
				'slug' => $slug,
			];
			if ($form->is_blocked()) {
				$this->_has_blocked_form = TRUE;
			}
		}

		if (!$this->_has_blocked_form) {
			$this->_sub_menu_items[] = [
				'name' => 'Add form',
				'slug' => $this->_menu_slug_prefix . '-settings-create_form',
			];
		}

		return $this->_sub_menu_items;
	}

	/**
	 * Init admin menu
	 * @since 1.0.0
	 */
	protected function init_admin_menu()
	{
		add_action('admin_menu', [$this, 'init_admin_sub_menu']);
	}

	/**
	 * Init admin sub menu.
	 * Public access for calling from 'add_action'
	 * @since 2.15.10
	 */
	public function init_admin_sub_menu()
	{

		$this->init_admin_sub_menu_items();

		$menu_title = $this->_menu_title;
		if (!$this->is_registered() || $this->_has_blocked_form) {
			$menu_title .= $this->get_badge_template('setup');
		}

		add_menu_page($this->_menu_title, $menu_title, $this->_settings_capability, $this->_main_menu_slug, [$this, 'navigate'], AMOFORMS_PLUGIN_URL . 'images/amo_menu_icon.png', '59.5' . rand(9999, 199999));

		if (!$this->check_setup()) {
			foreach ($this->get_sub_menu_items() as $item) {
				add_submenu_page($this->_main_menu_slug, $item['name'], $item['name'], $this->_settings_capability, $item['slug'], [$this, 'navigate']);
			}
		}
	}

	/**
	 * Get template for badge in menu
	 * @since 2.15.10
	 * @param string $text
	 * @return string
	 */
	protected function get_badge_template($text) {
		return " <span class='update-plugins count-1'><span class='update-count'>" . $text . "</span></span>";
	}

	/**
	 * Init ajax actions
	 * @since 2.0.1
	 */
	protected function init_ajax_actions()
	{
		foreach($this->_ajax_actions as $type => $actions) {
			foreach ($actions as $action) {
				$action = $this->_ajax_prefix . $action;
				switch($type) {
					case 'private':
						add_action("wp_ajax_{$action}", [$this, 'navigate_ajax']);
						break;
					case 'protected':
						add_action("wp_ajax_{$action}", [$this, 'navigate_ajax']);
						add_action("wp_ajax_nopriv_{$action}", [$this, 'navigate_ajax']);
						break;
					case 'public':
						add_action("wp_ajax_nopriv_{$action}", [$this, 'navigate_ajax']);
						break;
				}
			}
		}
	}

	/**
	 * Navigate to controller and action
	 * @since 1.0.0
	 * @throws Exceptions\Validate
	 */
	public function navigate()
	{
		try {
			$controller_name = '\\' . __NAMESPACE__ . '\\Controllers\\' . ucfirst($this->_controller);

			if (!class_exists($controller_name)) {
				throw new Validate('Undefined controller');
			}

			$controller = new $controller_name();

			$account_settings_actions = [
				'edit_email',
				// ajax actions:
				'update_email',
				'can_register',
				'has_connection',
				// old actions:
				'edit_account',
				'update_account',
				'dismiss_notice',
				'suspend_notice'
			];
			if ($this->_controller === 'settings' && !in_array($this->_action, $account_settings_actions, TRUE)) {
				$forms_manager = Forms\Manager::instance();
				/** @var Form $form */
				foreach ($forms_manager->get_forms_collection() as $form) {
					if ($form->is_blocked()) {
						$this->_action = 'edit_email';
						break;
					}
				}
			}

			$action = $this->_action . '_action';

			if (!method_exists($controller, $action)) {
				throw new Validate('Undefined action');
			}

			$controller->$action();

		} catch (\Exception $e) {
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Navigate for ajax requests
	 * @since 2.0.0
	 */
	public function navigate_ajax() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		$this->navigate();
		die;
	}

	/**
	 * Make URL to plugin page like: http://site.domain/wp-admin/admin.php?page=amoforms-settings-form-15
	 * @since 2.9.5
	 * @param string $page
	 * @return string
	 */
	public function get_plugin_page_url($page)
	{
		return $this->get_admin_url() . '?page=' . $page;
	}

	/**
	 * Get form page URL
	 * @since 2.9.5
	 * @param int $id
	 * @return string
	 */
	public function get_form_url($id)
	{
		return $this->get_plugin_page_url($this->get_form_page_prefix() . $id);
	}

	/**
	 * Get URL to some settings page
	 * @since 2.9.5
	 * @param string $action
	 * @param int $form_id
	 * @return string
	 */
	public function get_settings_page_url($action, $form_id)
	{
		return $this->get_form_url($form_id) . "&action={$action}&form[id]={$form_id}";
	}


	/**
	 * Get form from request
	 * @since 2.15.0
	 *
	 * @return Form|bool
	 */
	public function get_form_from_request() {
		$form_id = !empty($_REQUEST['form']['id']) ? $_REQUEST['form']['id'] : NULL;
		if (!$form_id) {
			$page = $this->get_page();
			if ($page && strpos($page, $this->get_form_page_prefix()) === 0) {
				if (($id = $this->get_param()) && is_numeric($id)) {
					$form_id = $id;
				}
			}
		}

		$form = FALSE;
		if ($form_id) {
			$form = Forms\Manager::instance()->get_form_by_id((int)$form_id);
		}

		return $form;
	}

	/**
	 * Redirect to URL
	 * @since 2.9.5
	 * @param string $url
	 * @return void die
	 */
	public function redirect($url)
	{
		if (!headers_sent()) {
			header('Location: ' . $url, TRUE, 302);
		} else {
			echo "<script>window.location.replace('$url')</script>";
		}
		die;
	}


	/**
	 * Check registration status
	 * @since 2.19.0
	 * @return bool
	 */
	protected function is_registered(){
		if(!isset($this->_is_registered)){
			$amo_user = amoUser::instance();
			$this->_is_registered = $amo_user->is_full();
		}
		return $this->_is_registered;
	}

	/**
	 * Check setup status
	 * @since 2.19.0
	 * @return bool
	 */
	protected function check_setup(){
		if(!isset($this->_need_first_setup)){
			$stats = Stats::instance();
			$is_installed = $stats->get_event_date(Stats::EVENT_PLUGIN_INSTALLED);
			$is_viewed = $stats->get_event_date(Stats::EVENT_FIRST_VIEW);
			$this->_need_first_setup = !$this->is_registered() && ($is_installed || $is_viewed);
		}
		return $this->_need_first_setup;
	}

	/**
	 * Get URL to preview page
	 * @param int $form_id
	 * @since 2.20.8
	 * @return string
	 */
	public function get_preview_page_url($form_id)
	{
		$base = get_site_url();
		$page_id = get_option(AMOFORMS_OPTION_PREVIEW_PAGE_ID);
		return $base . "?page_id=".$page_id."&amoforms_form_id=".$form_id;
	}
}
