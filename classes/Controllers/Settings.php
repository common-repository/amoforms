<?php
namespace Amoforms\Controllers;

use Amoforms\Libs\amoCRM\Stats;
use Amoforms\Libs\Analytics\Analytics;
use Amoforms\Libs\Errors\MainErrorHandler;
use Amoforms\Libs\FileSystem\Collections\Backgrounds;
use Amoforms\Libs\FileSystem\Models\Background;
use Amoforms\Libs\FileSystem\Models\Interfaces\ImageWithThumb;
use Amoforms\Libs\Http\Response;
use Amoforms\Libs\amoCRM\Account;
use Amoforms\Libs\Notices\Notices;
use Amoforms\Models\Fields;
use Amoforms\Models\Styles;
use Amoforms\Models\Fields\Types\Base_Field;
use Amoforms\Models\Forms;
use Amoforms\Models\Forms\Form;
use Amoforms\Models\amoCRM\amoUser;
use Amoforms\Exceptions\Validate;
use Amoforms\Exceptions\Runtime;
use Amoforms\Helpers;
use Amoforms\Helpers\Strings;
use Amoforms\Helpers\Arrays;
use Amoforms\Router;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Settings
 * @since 1.0.0
 * @package Amoforms\Controllers
 */
class Settings extends Base
{
	const ACCOUNT_ACTION_UPDATE = 'update';
	const ACCOUNT_ACTION_REGISTER = 'register';

	protected $_account_actions = [
		self::ACCOUNT_ACTION_UPDATE   => self::ACCOUNT_ACTION_UPDATE,
		self::ACCOUNT_ACTION_REGISTER => self::ACCOUNT_ACTION_REGISTER,
	];

	/**
	 * Key for validate "nonce" field
	 * @since 1.0.0
	 * @var string
	 */
	protected $_nonce_field = 'amoforms_settings';

	public function __construct()
	{
		parent::__construct();
		$stats = Stats::instance();
		if (!$stats->get_event_date(Stats::EVENT_FIRST_VIEW)) {
			$stats->set_event_date(Stats::EVENT_FIRST_VIEW, time());
			$stats->send_event(Stats::EVENT_FIRST_VIEW);
		}
	}

	public function get_capability() {
		return 'manage_options';
	}

	public function index_action() {
		$this->form_action();
	}

	/**
	 * First setup page
	 * @since 2.16.6
	 */
	public function first_setup_action()
	{
		$user = amoUser::instance();
		if ($user->is_full()) {
			throw new Runtime('Account is already set up');
		}

		$this->_view
			->set('hide_top_nav', TRUE)
			->set('path', 'settings/pages/fields')
			->set('account_actions', $this->_account_actions)
			->render('settings/page');
	}

	/**
	 * Handle click on "Get started" button
	 * @since 2.18.7
	 */
	public function get_started_action()
	{
		$response = new Response\Ajax();

		try {
			$stats = Stats::instance();
			$stats->set_event_date(Stats::EVENT_GET_STARTED, time());

			$first_form = Forms\Manager::instance()->get_first_form();
			$form_url = Router::instance()->get_form_url($first_form->id());

			$response
				->set('form_url', $form_url)
				->set_result(TRUE)
				->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Send "Get started" event
	 * @since 2.18.7
	 */
	public function send_get_started_event_action()
	{
		$response = new Response\Ajax();

		try {
			$stats = Stats::instance();
			if (!$stats->get_event_date(Stats::EVENT_GET_STARTED)) {
				$stats->set_event_date(Stats::EVENT_GET_STARTED, time());
			}
			$stats->send_event(Stats::EVENT_GET_STARTED);
			$response->set_result(TRUE)
				 	 ->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Show fields settings page
	 * @since 1.0.0
	 * @throws \Amoforms\Views\Exceptions\Runtime
	 */
	public function form_action()
	{
		if (!$form_id = Router::instance()->get_param()) {
			throw new Validate('Empty form id');
		}
		if (!$form = Forms\Manager::instance()->get_form_by_id((int)$form_id)) {
			throw new Runtime('Form not found');
		}

		$notice = Notices::instance();
		$review = $notice->get_notice_status('review');

		if($review){
			$this->_view->set('type', 'review');
		}

		$this->_view
			->set('form', $form)
			->set('edit_mode', TRUE)
			->set('nonce_field', $this->_nonce_field) //TODO: use or delete
			->set('path', 'settings/pages/fields')
			->set('backgrounds', new Backgrounds())
			->render('settings/page');
	}

	/**
	 * Updating form fields
	 * @since 1.0.0
	 * @throws Runtime
	 * @throws Validate
	 */
	public function update_fields_action()
	{
		$response = new Response\Ajax();

		try {
			if (empty($_POST['form']['fields']) || !is_array($_POST['form']['fields'])) {
				throw new Validate('Empty fields');
			}
			if (!$form = $this->get_form_by_request()) {
				$form = new Form();
			}
			$form
				->set_fields($_POST['form']['fields'])
				->save();

			$response->set_result(TRUE)
					 ->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}

		$response->send();
	}

	/**
	 * Add field to form
	 * @since 1.0.0
	 * @throws Runtime
	 * @throws Validate
	 * @return $this
	 */
	public function add_field_action()
	{
		$response = new Response\Ajax();

		try {
			Arrays::validate_for_empty($_POST, ['form' => 'id', 'field' => ['type']]);

			if (!isset($_POST['field']['position'])) {
				throw new Validate('Field position is not specified');
			}

			if (!$form = $this->get_form_by_request()) {
				throw new Runtime('Form not found');
			}

			if ($_POST['field']['type'] === Base_Field::TYPE_CAPTCHA && $form->has_captcha()) {
				throw new Validate('Captcha field already exists in form');
			}

			$field = Fields\Manager::instance()->make_field($_POST['field']['type']);
			$style = Styles\Manager::instance()->make_style($_POST['field']['type']);

			$form
				->add_field($field, (int)$_POST['field']['position'])
				->add_style($style, $field->id())
				->save();
			if(is_null($style->id())){
				$style = $form->get_type_style($field->to_array()['type']);
			}
			$response
				->set_result(TRUE)
				->set('field', $field->to_array())
				->set('style', $style->to_array())
				->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Duplicate field in form
	 * @since 2.7.0
	 * @throws Validate
	 * @throws Runtime
	 */
	public function duplicate_field_action()
	{
		$response = new Response\Ajax();

		try {
			if (!isset($_POST['field']['id']) || !is_numeric($_POST['field']['id'])) {
				throw new Validate('Invalid field id');
			}
			if (!$form = $this->get_form_by_request()) {
				throw new Runtime('Form not found');
			}
			if (!$field = $form->duplicate_field((int)$_POST['field']['id'])) {
				throw new Runtime('Duplication error');
			}
			$style = $form->duplicate_field_style((int)$_POST['field']['id'], $field->id(), $field->to_array()['type']);
			$form->save();
			$response
				->set_result(TRUE)
				->set('field', $field->to_array())
				->set('style', $style->to_array())
				->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Edit field
	 * @since 2.0.0
	 * @throws Validate
	 * @throws Runtime
	 */
	public function edit_field_action()
	{
		$response = new Response\Ajax();

		try {
			Arrays::validate_for_empty($_REQUEST, ['form' => 'id']);
			if (!isset($_REQUEST['field']['id'])) {
				throw new Validate('Empty field id');
			}
			if (!$form = $this->get_form_by_request()) {
				throw new Runtime('Form not found');
			}
			if (isset($_REQUEST['field']['extensions']) && !empty($_REQUEST['field']['extensions'])){
				$values = count(explode(',', $_REQUEST['field']['extensions']));
				$valid = preg_match_all("/\.[a-z0-9]+,?/", $_REQUEST['field']['extensions']);
				if($values != $valid){
					throw new Runtime('Invalid file type');
				}
			}
			$form
				->edit_field((int)$_REQUEST['field']['id'], $_REQUEST['field'])
				->save();

			$response->set_result(TRUE)->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Delete field from form
	 * @since 1.0.0
	 * @throws Validate
	 */
	public function delete_field_action()
	{
		$response = new Response\Ajax();

		try {
			if (empty($_REQUEST['form']['id']) || !isset($_REQUEST['field']['id'])) {
				throw new Validate('Invalid params');
			}
			if (!$form = $this->get_form_by_request()) {
				throw new Validate('Form not found');
			}

			$fields_count = count($form->get_fields());
			if ($fields_count <= 1) {
				throw new Validate("You can't delete all fields");
			}
			$form
				->delete_field((int)$_REQUEST['field']['id'])
				->delete_style((int)$_REQUEST['field']['id'])
				->save();

			$response->set_result(TRUE)->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Update submit button settings
	 * @since 2.0.1
	 */
	public function update_submit_button_action()
	{
		$response = new Response\Ajax();
		try {
			Arrays::validate_for_empty($_POST,
					[
						'form' => [
								'id',
								'settings' => [
										'submit' => ['text']
								]
						],
						'style' => [
							'id',
							'type',
							'is_type_style',
							'elements' => ['submit_button']
						]
					]
			);
			if (!$form = $this->get_form_by_request()) {
				throw new Runtime('Form not found');
			}
			$form
				->set_settings(['submit' => $_POST['form']['settings']['submit']])
				->edit_style((int)$_POST['style']['id'], $_POST['style'])
				->save();
			$response->set_result(TRUE)->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Update design settings of form
	 * @since 2.9.0
	 */
	public function update_design_settings_action()
	{
		$response = new Response\Ajax();

		try {
			if (!$form = $this->get_form_by_request()) {
				throw new Runtime('Form not found');
			}
			if (empty($_POST['form']['settings']) || !is_array($_POST['form']['settings'])) {
				throw new Validate('Invalid form settings');
			}
			$form
				->set_settings($_POST['form']['settings'])
				->save();

			$response->set_result(TRUE)->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	public function upload_form_background_action() {
		$response = new Response\Ajax();

		try {
			if (empty($_FILES['background_image'])) {
				throw new Validate('Image not uploaded');
			}

			add_filter('wp_handle_upload_prefilter', function ($file) {
				$filename = explode('.', $file['name']);
				$file['name'] = time() . md5($file['name']) . '.' . end($filename);

				return $file;
			});

			$result = wp_handle_upload($_FILES['background_image'], ['test_form' => FALSE]);
			if (isset($result['error'])) {
				throw new Runtime('Image upload handle failed: ' . $result['error']);
			}

			if (!isset($result['file'])) {
				throw new Runtime('Image upload handle unknown error.');
			}

			/** @var ImageWithThumb $file */
			$file = new Background($result['file']);

			$backgrounds = new Backgrounds();
			$backgrounds->attach($file);
			$background = $backgrounds->find_by('filename', $file->get('filename'));
			$file->rm();

			$response
				->set_result(TRUE)
				->set('image_url', $background->get('url'))
				->set('thumb_url', $background->get_thumb()->get('url'))
				->set('img_basename', $background->get_thumb()->get('basename'));
		} catch (\InvalidArgumentException $ex) {
			$response->set_message('Bad image: ' . $ex->getMessage());
		} catch (\Exception $ex) {
			$response->set_message($ex->getMessage());
		}

		$response->send();
	}

	/**
	 * Delete selected background
	 * @since 2.11.8
	 */
	public function delete_form_background_action()
	{
		$response = new Response\Ajax();

		try {
			if (empty($_REQUEST['img_basename']) || !is_string($_REQUEST['img_basename'])) {
				throw new Validate('Invalid image');
			}

			$backgrounds = new Backgrounds();
			if ($background = $backgrounds->find_by('basename', $_REQUEST['img_basename'])) {
				$background->get_thumb()->rm();
				$background->rm();
			}

			$response->set_result(TRUE)->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Update css settings
	 * @since 2.17.0
	 * @throws Validate
	 * @throws Runtime
	 */
	public function update_custom_code_settings_action()
	{
		$response = new Response\Ajax();

		try {
			$code_types = ['css', 'js'];
			if (empty($_POST['type']) || !in_array($_POST['type'], $code_types, TRUE)) {
				throw new Validate('Invalid code type');
			}
			$type = $_POST['type'];

			if (!isset($_POST[$type]) || !is_string($_POST[$type])) {
				throw new Validate("Invalid {$type} settings");
			}
			if (!$form = $this->get_form_by_request()) {
				throw new Runtime('Form not found');
			}

			$form->set_settings([$type => $_POST[$type]])
				->save();

			$response->set_result(TRUE)->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}


	/**
	 * Create default form and redirect to editor
	 * @since 2.9.5
	 */
	public function create_form_action()
	{
		try {
			$form = Forms\Manager::instance()->create_default_form();
			$form->save();

			$router = Router::instance();
			$this->_view
				->set('text', 'Creating new form...')
				->set('url', $router->get_form_url($form->id()))
				->render('other/redirect');

		} catch (\Exception $e) {
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Edit for settings
	 * @since 1.0.0
	 */
	public function edit_form_action()
	{
		if (!$form = $this->get_form_by_request()) {
			throw new Runtime('Form not found');
		}

		$this->_view
			->set('form', $form)
			->set('ga_enabled', Analytics::instance()->is_enabled())
			->set('nonce_field', $this->_nonce_field) //TODO: use or delete
			->set('path', 'settings/pages/form')
			->render('settings/page');
	}

	/**
	 * Updating form settings
	 * @since 1.0.0
	 * @throws Validate
	 */
	public function update_form_action()
	{
		$response = new Response\Ajax();

		try {
			Arrays::validate_for_empty($_POST,
				[
					'form' => [
						'id',
						'settings' => [
							'name',
							'confirmation' => ['type'],
							'status',
							'view',
						],
					],
					'style' => [
						'id'
					]
				]
			);
			if (!isset($_POST['form']['settings']['confirmation']['value'])) {
				$_POST['form']['settings']['confirmation']['value'] = '';
			}
			if (!$form = $this->get_form_by_request()) {
				$form = new Form();
			}
			$settings = [
					'name'         => $_POST['form']['settings']['name'],
					'status'       => $_POST['form']['settings']['status'],
					'view'       => $_POST['form']['settings']['view'],
					'confirmation' => [
							'type'  => $_POST['form']['settings']['confirmation']['type'],
							'value' => Strings::escape($_POST['form']['settings']['confirmation']['value']),
					],
			];
			if(isset($_POST['form']['settings']['modal']['text'])){
				$settings['modal'] = [
						'text'   => Strings::escape($_POST['form']['settings']['modal']['text'])
				];
			}
			$form
					->set_settings($settings)
					->edit_style((int)$_POST['style']['id'], $_POST['style'])
					->save();

			$ga_status = !empty($_POST['ga']);
			$analytics = Analytics::instance();

			if ($ga_status !== $analytics->is_enabled()) {
				$analytics->toggle($ga_status);
			}

			$response->set_result(TRUE)->send();
		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Duplicate form
	 * @since 2.9.5
	 * @throws Validate
	 */
	public function duplicate_form_action()
	{
		$response = new Response\Ajax();

		try {
			if (!$form = $this->get_form_by_request()) {
				throw new Validate('Form not found');
			}
			$new_form = $form->duplicate();
			$router = Router::instance();
			$response
				->set('form_url', $router->get_form_url($new_form->id()))
				->set_result(TRUE)
				->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Delete form
	 * @since 2.9.5
	 * @throws Validate
	 * @throws Runtime
	 */
	public function delete_form_action()
	{
		$response = new Response\Ajax();

		try {
			if (empty($_REQUEST['form']['id'])) {
				throw new Validate('Empty form id');
			}
			$forms_manager = Forms\Manager::instance();
			$forms = $forms_manager->get_forms_collection();
			if ($forms->count() <= 1) {
				throw new Validate("You have only one form and you can't delete it");
			}
			$form_id = (int)$_REQUEST['form']['id'];
			if (!$forms_manager->delete_form($form_id)) {
				throw new Runtime('Error deleting form');
			}
			$first_form = $forms->delete_by_id($form_id)->first();

			$response
				->set('form_url', Router::instance()->get_form_url($first_form->id()))
				->set_result(TRUE)
				->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Delete all forms from DB
	 */
	/*TODO: delete this unused method
	public function delete_all_forms_action()
	{
		Forms\Manager::instance()->delete_all_forms();
		(new Response\Ajax())->set_result(TRUE)->send();
	}*/

	/**
	 * Show email settings page
	 * @param NULL|Forms\Interfaces\Form $form
	 * @throws Runtime
	 * @throws \Amoforms\Views\Exceptions\Runtime
	 * @since 1.0.0
	 */
	public function edit_email_action(Forms\Interfaces\Form $form = NULL)
	{
		if (!$form && !($form = $this->get_form_by_request())) {
			throw new Runtime('Form not found');
		}

		//TODO: check all forms for block
		$is_blocked = $form->is_blocked(); //TODO: use or remove

		$amo_user = amoUser::instance();
		$registered = (bool)$amo_user->get_data('id');

		$data = $amo_user->get_data() + [
				'login'     => '',
				'api_key'   => '',
				'subdomain' => '',
			];

		$this->_view
			->set('form', $form)
			->set('is_blocked', $is_blocked)
			->set('login', $data['login'])
			->set('subdomain', $data['subdomain'])
			->set('api_key', $data['api_key'])
			->set('show_stats_reporting', !Stats::instance()->get_reporting_start_date() || !$registered)
			->set('registered', $registered)
			->set('account_url', $amo_user->get_account_url(TRUE))
			->set('account_short_url', trim($amo_user->get_account_url(FALSE, FALSE), '/'))
			->set('account_actions', $this->_account_actions)
			->set('path', 'settings/pages/email')
			->render('settings/page');
	}

	/**
	 * Update email settings
	 * @since 1.0.0
	 * @throws Runtime
	 * @throws Validate
	 */
	public function update_email_action()
	{
		$response = new Response\Ajax();
		$post = Arrays::trim_values($_POST);

		try {
			$stats = Stats::instance();

			if (!$stats->get_reporting_start_date()) {
				if (empty($post['stats_reporting'])) {
					throw new Validate('You need to accept Terms of Use Agreement');
				}
				$stats->enable_reporting();
			}

			Arrays::validate_for_empty($post, [
				'account_action',
				'form' => [
					'id',
					'settings' => [
						'email' => ['name', 'subject', 'to'],
					],
				],
			]);

			if (!filter_var($post['form']['settings']['email']['to'], FILTER_VALIDATE_EMAIL)) {
				throw new Validate('Invalid email');
			}

			if (!$form = $this->get_form_by_request()) {
				throw new Runtime('Form not found');
			};

			$form->set_settings([
				'email' => [
					'name'    => $post['form']['settings']['email']['name'],
					'subject' => $post['form']['settings']['email']['subject'],
					'to'      => $post['form']['settings']['email']['to'],
				],
			]);

			$this->update_account_settings($post);
			$form->save();

			/** @var Forms\Form $form */
			foreach (Forms\Manager::instance()->get_forms_collection() as $form) {
				// After successful authorization we unlock all forms
				$form->set_amo(['blocked' => Form::FORM_BLOCKED_N])->save();
			}

			$amo_user = amoUser::instance();

			$response
				->set('account', [
					'login'      => $amo_user->get_data('login'),
					'subdomain'  => $amo_user->get_data('subdomain'),
					'api_key'    => $amo_user->get_data('api_key'),
					'registered' => (bool)$amo_user->get_data('id'),
					'url'        => $amo_user->get_account_url(TRUE),
					'short_url'  => trim($amo_user->get_account_url(FALSE, FALSE), '/'),
				])
				->set('account_action', $post['account_action'])
				->set_result(TRUE)
				->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Update first setup settings
	 * @since 2.16.6
	 * @throws Runtime
	 * @throws Validate
	 */
	public function update_first_setup_settings_action()
	{
		$response = new Response\Ajax();
		$post = Arrays::trim_values($_POST);

		try {
			$stats = Stats::instance();

			if (!$stats->get_reporting_start_date()) {
				if (empty($post['stats_reporting'])) {
					throw new Validate('You need to accept Terms of Use Agreement');
				}
				$stats->enable_reporting();
			}

			Arrays::validate_for_empty($post, [
				'account_action',
				'form' => [
					'settings' => [
						'email' => ['name', 'subject', 'to'],
					],
				],
			]);

			if (!filter_var($post['form']['settings']['email']['to'], FILTER_VALIDATE_EMAIL)) {
				throw new Validate('Invalid email');
			}

			$this->update_account_settings($post);

			$first_form_id = NULL;

			/** @var Forms\Form $form */
			foreach (Forms\Manager::instance()->get_forms_collection() as $form) {
				if (!$first_form_id) {
					$first_form_id = $form->id();
				}

				$email_settings = $form->get('email');
				foreach (['name', 'subject', 'to'] as $key) {
					$email_settings[$key] = $post['form']['settings']['email'][$key];
				}
				$form->set_settings(['email' => $email_settings]);
				// After successful authorization we unlock all forms
				$form->set_amo(['blocked' => Form::FORM_BLOCKED_N])->save();
			}

			if (!$first_form_id) {
				throw new Runtime('Not found id of first form');
			}

			$amo_user = amoUser::instance();
			$form_url = Router::instance()->get_form_url($first_form_id);

			$response
				->set('account', [
					'login'      => $amo_user->get_data('login'),
					'subdomain'  => $amo_user->get_data('subdomain'),
					'api_key'    => $amo_user->get_data('api_key'),
					'registered' => (bool)$amo_user->get_data('id'),
					'url'        => $amo_user->get_account_url(TRUE),
					'short_url'  => trim($amo_user->get_account_url(FALSE, FALSE), '/'),
				])
				->set('account_action', $post['account_action'])
				->set('form_url', $form_url)
				->set_result(TRUE);
			$response->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage());
			$response->send(FALSE);
			throw new Runtime($e->getMessage());
		}

	}

	/**
	 * Update amoCRM account settings
	 * @since 2.16.6
	 * @param array $post - $_POST data
	 * @return bool
	 * @throws Validate
	 */
	private function update_account_settings(array $post)
	{
		$post = Arrays::trim_values($post);

		if (!isset($this->_account_actions[$post['account_action']])) {
			throw new Validate('Undefined account action');
		}

		$stats = Stats::instance();
		$amo_user = amoUser::instance();

		switch ($post['account_action']) {
			case self::ACCOUNT_ACTION_REGISTER:
				$user_data = [];
				if (!empty($post['phone']) && is_string($post['phone']) && ($phone = trim($post['phone']))) {
					$user_data['phone_num'] = $post['phone'];
				}
				if (empty($post['form']['settings']['email']['name']) || !is_string($post['form']['settings']['email']['name'])) {
					throw new Validate('Empty name');
				}
				$login = $post['form']['settings']['email']['to'];
				$user_data['first_name'] = $post['form']['settings']['email']['name'];
				if (!$amo_user->set('login', $login)->try_to_register($user_data)) {
					throw new Validate("Can't register account");
				}
				$amo_user->set_top_level_domain();
				$stats->set_event_date(Stats::EVENT_REGISTRATION, time());
				break;

			case self::ACCOUNT_ACTION_UPDATE:
				foreach (['login', 'subdomain', 'api_key'] as $key) {
					if (empty($post['amo_user'][$key])) {
						throw new Validate("Empty account " . $key);
					}
				}
				$amo_user
					->set('login', $post['amo_user']['login'])
					->set('subdomain', $post['amo_user']['subdomain'])
					->set('api_key', $post['amo_user']['api_key'])
					->set_top_level_domain();

				$account_info = $amo_user->get_account_info();

				if (!$account_info || empty($account_info['id'])) {
					throw new Validate("Can't authorize in amoCRM");
				}
				$amo_user->set_param(amoUser::PARAM_ACCOUNT_ID, (int)$account_info['id']);
				break;
		}

		$amo_user
			->set('try_counts', 0)
			->set_last_try(time())
			->save();

		if (!$amo_user->get_data('id')) {
			throw new Validate('Error in amoCRM authentication');
		}

		return TRUE;
	}

	/**
	 * @since 2.18.7
	 */
	public function send_registration_event_action()
	{
		$response = new Response\Ajax();

		try {
			$stats = Stats::instance();
			if (!$stats->get_event_date(Stats::EVENT_REGISTRATION)) {
				$stats->set_event_date(Stats::EVENT_REGISTRATION, time());
			}
			$stats->send_event(Stats::EVENT_REGISTRATION);
			$response->set_result(TRUE)->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Check email
	 * @since 2.0.0
	 * @deprecated since 2.15.0
	 * TODO: remove this old method
	 * @throws \Exception
	 */
	/*
	public function check_email_action() {
		$amo_account = Account::instance();
		echo json_encode(['response' => ['can_register' => $amo_account->can_register($_POST['email'])]]);
	}*/

	/**
	 * Check email for possibility of registration
	 * @since 2.15.0
	 */
	public function can_register_action() {
		$response = new Response\Ajax();

		try {
			if (empty($_POST['email'])) {
				throw new Validate('Empty email');
			}
			if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				throw new Validate('Invalid email');
			}
			$amo_account = Account::instance();
			$response->set_result($amo_account->can_register($_POST['email']));
			$response->send();
		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Checking connection
	 * @since 2.0.0
	 */
	public function has_connection_action() {
		$response = new Response\Ajax();

		try {
			$fields = [
				'login',
				'api_key',
				'subdomain',
			];

			$data = [];
			foreach ($fields as $field) {
				$data[$field] = isset($_POST[$field]) ? $_POST[$field] : NULL;
			}

			$amo_user = amoUser::instance();
			$has_connection = $amo_user
				->set('login', $data['login'])
				->set('api_key', $data['api_key'])
				->set('subdomain', $data['subdomain'])
				->set_top_level_domain()
				->has_connection();

			$response
				->set_result($has_connection)
				->set('response', ['has_connection' => $has_connection]) // for backward compatibility // TODO: remove with account settings page
				->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Edit account
	 * @since 2.0.0
	 * TODO: delete this method because it is not used
	 * @throws \Amoforms\Views\Exceptions\Runtime
	 */
	public function edit_account_action() {
		$amo_user = amoUser::instance();
		$data = $amo_user->get_data() + [
			'login' => '',
			'api_key' => '',
			'subdomain' => '',
		];

		if (!$form = $this->get_form_by_request()) {
			throw new Runtime('Form not found');
		}
		$amo = $form->get('amo');
		$has_error = isset($amo['blocked']) && $amo['blocked'] === Forms\Form::FORM_BLOCKED_Y;

		$this->_view
			->set('form', $form)
			->set('path', 'settings/pages/account')
			->set('has_error', $has_error)
			->set('login', $data['login'])
			->set('api_key', $data['api_key'])
			->set('subdomain', $data['subdomain'])
			->render('settings/page');
	}

	/**
	 * Update account
	 * @since 2.0.0
	 * TODO: delete this method because it is not used
	 * @throws Validate
	 */
	public function update_account_action() {
		$fields = [
			'login',
			'api_key',
			'subdomain',
		];

		$data = [];
		foreach ($fields as $field) {
			$data[$field] = isset($_POST[$field]) ? $_POST[$field] : NULL;
		}

		$amo_user = amoUser::instance();

		$amo_user->set('login', $data['login'])
			->set('api_key', $data['api_key'])
			->set('subdomain', $data['subdomain']);

		if ($error = $amo_user->validate()) {
			throw new Validate($error);
		}

		if (!$amo_user->has_connection()) {
			throw new Validate('Has no connection');
		}

		if ($amo_user->get_data('try_counts') > 0) {
			$amo_user->set('try_counts', 0);
		}

		$amo_user->save();

		$forms_collection = Forms\Manager::instance()->get_forms_collection();

		/** @var Forms\Form $form */
		foreach ($forms_collection as $form) {
			$form->set_amo(['blocked' => Form::FORM_BLOCKED_N])->save();
		}

		$this->edit_account_action();
	}

	/**
	 * Preview form
	 * @since 1.0.0
	 * @throws \Amoforms\Views\Exceptions\Runtime
	 */
	public function form_preview_action()
	{
		if (!$form = $this->get_form_by_request()) {
			throw new Runtime('Form not found');
		}

		$notice = Notices::instance();
		$review = $notice->get_notice_status('review');

		if($review){
			$this->_view->set('type', 'review');
		}

		$this->_view
			->set('form', $form)
			->set('is_preview', TRUE)
			->set('path', 'settings/pages/preview')
			->render('settings/page');
	}

	/**
	 * Get form by id in request
	 * @since 1.0.0
	 * @return Form|bool
	 */
	protected function get_form_by_request()
	{
		return Router::instance()->get_form_from_request();
	}

	/**
	 * Dismiss notice action
	 * @since 2.19.0
	 */
	public function dismiss_notice_action(){
		try {
			if (empty($_GET['type'])) {
				throw new Validate('Empty dismiss params');
			}
			$type = trim($_GET['type']);
			$notice = Notices::instance();
			$notice->set_notice_status($type, 'hide');

		} catch (\Exception $e) {
			Helpers::handle_exception($e);
		}
	}


	/**
	 * Suspend notice action
	 * @since 2.19.1
	 */
	public function suspend_notice_action()
	{
		try {
			if (empty($_GET['type'])) {
				throw new Validate('Empty suspend params');
			}
			$type = trim($_GET['type']);
			$notice = Notices::instance();
			$notice->set_notice_status($type, 'time');

		} catch (\Exception $e) {
			Helpers::handle_exception($e);
		}
	}
		
	/**
	 * Send error to Sentry
	 * @since 2.19.10
	 */
	public function send_error_action()
	{
		$response = new Response\Ajax();

		try {
			$router = Router::instance();
			$data = $_POST;
			if ($router->is_json_request() && ($json_data = $router->get_json_body())) {
				$data = $json_data;
			}
			if (!$data || !is_array($data)) {
				throw new Validate('Data is not array or empty');
			}

			$handler = MainErrorHandler::instance();
			$handler->handle_js_error($data);
			$response->set_result(TRUE)->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Edit style
	 * @since 3.0.0
	 * @throws Validate
	 * @throws Runtime
	 */
	public function edit_style_action()
	{
		$response = new Response\Ajax();

		try {
			Arrays::validate_for_empty($_REQUEST, ['form' => 'id', 'style' => 'id']);

			if (!$form = $this->get_form_by_request()) {
				throw new Runtime('Form not found');
			}
			$form
					->edit_style((int)$_REQUEST['style']['id'], $_REQUEST['style'])
					->save();

			$response->set_result(TRUE)->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Reset field style
	 * @since 3.1.1
	 * @throws Validate
	 * @throws Runtime
	 */
	public function reset_field_style_action()
	{
		$response = new Response\Ajax();

		try {
			Arrays::validate_for_empty($_REQUEST, ['form' => 'id', 'style' => 'id']);

			if (!$form = $this->get_form_by_request()) {
				throw new Runtime('Form not found');
			}

			$form
				->reset_style((int)$_REQUEST['style']['id'])
				->save();

			$response
					->set('style', $form->get_default_style((int)$_REQUEST['style']['id']))
					->set_result(TRUE)
					->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Reset form style
	 * @since 3.1.1
	 * @throws Validate
	 * @throws Runtime
	 */
	public function reset_form_style_action()
	{
		$response = new Response\Ajax();

		try {
			Arrays::validate_for_empty($_REQUEST, ['form' => 'id']);

			if (!$form = $this->get_form_by_request()) {
				throw new Runtime('Form not found');
			}
			$form
					->reset_form_styles()
					->save();

			$response
					->set('style', $form->get_default_form_style())
					->set_result(TRUE)
					->send();

		} catch (\Exception $e) {
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}
}
