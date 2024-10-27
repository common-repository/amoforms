<?php
namespace Amoforms\Controllers;

use Amoforms\Exceptions\Argument;
use Amoforms\Exceptions\Runtime;
use Amoforms\Exceptions\Validate;
use Amoforms\Helpers;
use Amoforms\Helpers\Strings;
use Amoforms\Libs\Analytics\Analytics;
use Amoforms\Models\amoCRM\amoUser;
use Amoforms\Models\Entries\Entry;
use Amoforms\Models\Forms;
use Amoforms\Libs\Sender;
use Amoforms\Libs\Captcha\Captcha;
use Amoforms\Libs\Http\Response;
use Amoforms\Libs\amoCRM;
use Amoforms\Router;
use \Amoforms\Models\Fields\Types\Base_Field;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Form
 * @since 1.0.0
 * @package Amoforms\Controllers
 */
class Form extends Base
{
	public function get_capability() {
		return TRUE;
	}

	public function index_action() {
		throw new Runtime('Undefined action');
	}

	/**
	 * Show form by id
	 * @since 2.15.0
	 *
	 * @param int $id Form id
	 *
	 * @return string Form content
	 */
	public function render_form_by_id($id)
	{
		$content = '';

		try {
			$id = (int)$id;
			if (!$form = Forms\Manager::instance()->get_form_by_id($id)) {
				throw new Argument('Form not found by id: ' . $id);
			}
			if ($this->can_show_form($form)) {
				amoCRM\Stats::instance()->form_shown_event();

				$content = $this->_view
					->set('form', $form)
					->get_content('form/form');
			}
		} catch (\Exception $e) {
			Helpers::handle_exception($e);
		}

		return $content;
	}

	/**
	 * Can show form or not
	 *
	 * @since 2.15.0
	 * @param Forms\Form $form
	 *
	 * @return bool
	 */
	protected function can_show_form(Forms\Form $form) {
		$is_preview = (int)get_option(AMOFORMS_OPTION_PREVIEW_PAGE_ID) === (int)Router::instance()->get_page_id();
		return ($form->get('status') === Forms\Form::FORM_STATUS_PUBLIC && $form->get('email')['to']) || $is_preview;
	}

	/**
	 * Accepting form submit
	 * @since 1.0.0
	 * @throws Validate
	 * @throws Runtime
	 */
	public function submit_action()
	{
		$response = new Response\Ajax();
		$captcha = Captcha::instance();
		$has_captcha = FALSE;

		try {
			if (empty($_REQUEST['form']['id'])) {
				throw new Validate('Empty form id');
			}

			$form = Forms\Manager::instance()->get_form_by_id((int)$_REQUEST['form']['id']);
			if (!$form) {
				throw new Runtime('Form not found');
			}

			$amo_user = AmoUser::instance()->has_connection();
			if(!$amo_user){
				throw new Runtime('amoCRM authorization error. Please check your integration settings!');
			}

			$has_captcha = $form->has_captcha();
			$settings = $form->get_settings();

			if (empty($settings['email']['to'])) {
				throw new Runtime('Empty email settings');
			}

			if (empty($_REQUEST['fields']) || !is_array($_REQUEST['fields'])) {
				throw new Validate('Empty fields');
			}

			if ($has_captcha) {
				if (empty($_POST['g-recaptcha-response'])) {
					throw new Validate('Empty captcha response');
				}
				if (!$captcha->verify_response($_POST['g-recaptcha-response'])) {
					throw new Validate('Incorrect captcha response');
				}
			}

			$values = [];
			foreach ($form->get_fields() as $field) {
				$field_id = $field['id'];
				if ($field['type'] === Base_Field::TYPE_FILE && !empty($_FILES['fields']['name'][$field_id])) {
					$uploaded_files = [];
					foreach ($_FILES['fields']['name'][$field_id] as $file_index => $file_name) {
						$path_info = pathinfo($file_name);
						$hash_name = md5($file_name) . (!empty($path_info['extension']) ? '.' . $path_info['extension'] : '');
						$saved_file = wp_upload_bits($hash_name, NULL, file_get_contents($_FILES['fields']['tmp_name'][$field_id][$file_index]), date('Y/m'));
						if (!empty($saved_file['error']) || empty($saved_file['url'])) {
							throw new Validate('Invalid file');
						}
						$uploaded_files[$file_index] = [
							'name' => $_FILES['fields']['name'][$field_id][$file_index],
							'type' => $_FILES['fields']['type'][$field_id][$file_index],
							'url'  => $saved_file['url'],
							'size' => $_FILES['fields']['size'][$field_id][$file_index],
						];
					}
					if (!empty($uploaded_files)) {
						$values[$field_id] = [
							'id'        => $field_id,
							'name'      => 'Files',
							'type'      => Base_Field::TYPE_FILE,
							'value'     => $uploaded_files,
							'api_value' => $uploaded_files,
						];
					}
				}


				if (isset($_REQUEST['fields'][$field_id]) && $_REQUEST['fields'][$field_id] !== '') {
					$request_field = $_REQUEST['fields'][$field_id];
					$field_values = [];
					$api_value = NULL;

					if (is_array($request_field)) {
						foreach ($request_field as $enum) {
							$enum_id = array_search($enum, $field['enums']);
							if ($enum_id !== FALSE) {
								$field_values[$enum_id] = Strings::escape($enum);
								$api_value[] = $enum_id;
							}
						}
						$value = implode(', ', $field_values);
					} else {

						switch ($field['type']) {
							case Base_Field::TYPE_RADIO:
							case Base_Field::TYPE_SELECT:
								if (($enum_id = array_search($request_field, $field['enums'])) !== FALSE) {
									$api_value = $enum_id;
								}
								break;
							case Base_Field::TYPE_CHECKBOX:
								$api_value = $request_field === Base_Field::CHECKBOX_TRUE_VALUE ?
									Base_Field::CHECKBOX_API_TRUE_VALUE : Base_Field::CHECKBOX_API_FALSE_VALUE;
								break;
							case Base_Field::TYPE_ANTISPAM:
								if($request_field != $field['spam']['answer']){
									$response->set('antispam', FALSE);
									throw new Validate('Wrong anti-spam answer');
								}
								continue;
								break;
							default:
								$api_value = $request_field;
						}

						$value = Strings::escape($request_field);
					}

					$values[$field_id] = [
						'id'        => $field['id'],
						'name'      => $field['name'],
						'type'      => $field['type'],
						'value'     => $value,
						'api_value' => $api_value,
					];

					if (!empty($field['enums'])) {
						$values[$field_id] += [
							'enums' => $field['enums'],
						];
					}
				}
			}

			if (empty($values)) {
				throw new Validate('Please fill in form fields!');
			}

			$origin = [
				'ip'        => $_SERVER['REMOTE_ADDR'],
				'analytics' => [
					'enabled' => Analytics::API_VALUE_DISABLED,
				],
			];

			$analytics = Analytics::instance();

			if ($analytics->is_enabled()) {
				$origin['analytics'] = [
					'enabled' => Analytics::API_VALUE_ENABLED,
					'data'    => $analytics->extract_analytics_data_from_post($_POST),
				];
			}

			try {
				$entry = new Entry();
				$entry
					->set_form_id($form->id())
					->set_fields($values)
					->set_user_ip($_SERVER['REMOTE_ADDR']);

				if (is_user_logged_in()) {
					$user = wp_get_current_user();
					$entry
						->set_user_id($user->ID)
						->set_user_name($user->display_name)
						->set_user_email($user->user_email);
				}
				$entry->save();

			} catch (\Exception $e) {
				Helpers::handle_exception($e);
			}

			switch ($settings['confirmation']['type']) {
				case 'text':
					$response
						->set('type', 'html')
						->set('value', Strings::un_escape($settings['confirmation']['value']));
					break;
				case 'wp_page':
				case 'redirect':
					$response
						->set('type', 'redirect')
						->set('value', $settings['confirmation']['value']);
					break;
			}

			// Remove FALSE if you need fast but unsafe sending of request
			if (FALSE && $response->can_finish_request()) {
				// We will not wait for the server response, hoping that it will be successful.
				$response
					->set_result(TRUE)
					->set('fast', TRUE)
					->send(FALSE)
					->try_finish_request();
			}

			amoCRM\Stats::instance()->form_submit_event();

			//TODO: Send message to admin if error occurred
			$this->send_data_to_forms($form, $values, $origin);
			$response
				->set('fast', FALSE)
				->set_result(TRUE)
				->send();

		} catch (\Exception $e) {
			if ($has_captcha) {
				$response->set('stoken', $captcha->get_captcha_token());
			}
			$response->set_message($e->getMessage())->send(FALSE);
			Helpers::handle_exception($e);
		}
	}

	/**
	 * Send data to forms.amoCRM.com if can
	 * @since 2.9.0
	 *
	 * @param Forms\Form $form
	 * @param array $values
	 * @param array $origin
	 *
	 * @throws Runtime
	 */
	private function send_data_to_forms(Forms\Form $form, array $values, array $origin) {
		$settings = $form->get_settings();
		$amo_settings = $form->get('amo');
		if (empty($amo_settings['id'])) {
			return;
		}
		if ($amo_settings['blocked'] === Forms\Form::FORM_BLOCKED_Y) {
			return;
		}

		$amo_user = amoUser::instance();

		if (!$amo_user->check_and_update_connection_status()) {
			$subject = 'amoCRM Account Authorization Blocked';

			if ($amo_user->need_block()) {
				$subject = 'Wordpress Form Blocked';
				$forms_collection = Forms\Manager::instance()->get_forms_collection();
				/** @var Forms\Form $form */
				foreach ($forms_collection as $form) {
					$form->set_amo(['blocked' => Forms\Form::FORM_BLOCKED_Y])->save();
				}
			}

			$sender = new Sender\Mail();
			$to = amoUser::instance()->get_data('login');
			$admin_url = Router::instance()->get_settings_page_url('edit_email', $form->id());
			$content = $this->_view->set('admin_url', $admin_url)->get_content('mail/blocked');
			$sender->set_html(TRUE)->send($to, $subject, $content);
		}

		amoCRM\Forms::instance()->register_result(
			$amo_settings['id'],
			$amo_settings['uid'],
			$settings['email']['to'],
			Strings::un_escape($settings['email']['subject']),
			$values,
			$origin,
			$amo_user->is_recently_registered()
		);
	}
}
