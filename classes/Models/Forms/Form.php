<?php
namespace Amoforms\Models\Forms;

use Amoforms\Exceptions\Argument;
use Amoforms\Exceptions\Runtime;
use Amoforms\Helpers;
use Amoforms\Helpers\Strings;
use Amoforms\Libs\amoCRM\Api;
use Amoforms\Libs\amoCRM\Forms;
use Amoforms\Libs\Db\Query_Data;
use Amoforms\Models\amoCRM\amoUser;
use Amoforms\Models\Fields;
use Amoforms\Models\Styles;
use Amoforms\Models\Fields\Types\Interfaces\Base_Field;
use Amoforms\Models\Styles\Types\Interfaces\Base_Style;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Form
 * @since 1.0.0
 * @package Amoforms\Models\Forms
 */
class Form implements Interfaces\Form
{
	const FORM_NAME_DEFAULT = 'Call Back Request';

	const FORM_TITLE_TYPE_TEXT  = 'text';
	const FORM_TITLE_TYPE_IMAGE = 'image';

	const FORM_TITLE_DEFAULT_TEXT_VALUE = 'Form title text';
	const FORM_TITLE_DEFAULT_IMAGE_VALUE = 'https://www.amocrm.com/design/promo_summer14/images/logo_main.png'; //TODO: save image with widget

	const FORM_TITLE_DEFAULT_TYPE = self::FORM_TITLE_TYPE_TEXT;
	const FORM_TITLE_DEFAULT_VALUE = self::FORM_TITLE_DEFAULT_TEXT_VALUE;

	const FORM_STATUS_PUBLIC = 'public';
	const FORM_STATUS_DRAFT = 'draft';
	const FORM_STATUS_DEFAULT = self::FORM_STATUS_PUBLIC;

	const FORM_THEME_DEFAULT = 1;
	const FORM_DEFAULT_ID = 1;

	const FIELD_NAME_POS_BEFORE = 'before';
	const FIELD_NAME_POS_ABOVE  = 'above';
	const FIELD_NAME_POS_INSIDE = 'inside';
	const FIELD_NAME_POS_DEFAULT = self::FIELD_NAME_POS_BEFORE;

	const CONFIRMATION_TYPE_TEXT = 'text';
	const CONFIRMATION_TYPE_WP_PAGE = 'wp_page';
	const CONFIRMATION_TYPE_REDIRECT = 'redirect';

	const FORM_FONT_FAMILY_DEFAULT = 'PT Sans';
	const FORM_FONT_SIZE_DEFAULT   = 15;

	const FIELD_BORDER_RECTANGULAR = 'rectangular';
	const FIELD_BORDER_ROUNDED = 'rounded';
	const FIELD_BORDER_DEFAULT = self::FIELD_BORDER_RECTANGULAR;

	const DEFAULT_CSS_SETTINGS = '';
	const DEFAULT_JS_SETTINGS = '';

	const SUBMIT_BTN_SIZE_SMALL  = 1;
	const SUBMIT_BTN_SIZE_MEDIUM = 2;
	const SUBMIT_BTN_SIZE_LARGE  = 3;

	const FORM_PADDINGS_Y = 'yes';
	const FORM_PADDINGS_N = 'no';
	const FORM_PADDINGS_DEFAULT = self::FORM_PADDINGS_N;

	const FORM_BLOCKED_Y = 'y';
	const FORM_BLOCKED_N = 'n';

	const FORM_VIEW_MODAL = 'modal';
	const FORM_VIEW_CLASSIC = 'classic';
	const FORM_VIEW_DEFAULT = self::FORM_VIEW_CLASSIC;

	const IN_STYLE_SETTINGS = 'style_settings';

	protected $_form_paddings = [
		self::FORM_PADDINGS_Y,
		self::FORM_PADDINGS_N,
	];

	protected $_title_types = [
		self::FORM_TITLE_TYPE_TEXT,
		self::FORM_TITLE_TYPE_IMAGE,
	];

	protected $_statuses_types = [
		self::FORM_STATUS_PUBLIC,
		self::FORM_STATUS_DRAFT,
	];

	protected $_names_positions = [
		self::FIELD_NAME_POS_BEFORE,
		self::FIELD_NAME_POS_ABOVE,
		self::FIELD_NAME_POS_INSIDE,
	];

	protected $_available_fonts = [
		'PT Sans',
		'Arial',
		'Courier New',
		'Georgia',
		'Lucida Console',
		'Tahoma',
		'Times New Roman',
		'Verdana',
	];

	protected $_confirmation_types = [
    	self::CONFIRMATION_TYPE_TEXT,
		self::CONFIRMATION_TYPE_WP_PAGE,
		self::CONFIRMATION_TYPE_REDIRECT,
	];

	protected $_fields_borders_types = [
		self::FIELD_BORDER_RECTANGULAR,
		self::FIELD_BORDER_ROUNDED,
	];

	/** @var \wpdb $_db */
	protected $_db;

	/** @var string */
	protected $_table;

	/** @var int|null $_id */
	protected $_id;

	/** @var Fields\Collection */
	protected $_fields;

	/** @var Styles\Collection */
	protected $_styles;

	/** @var array */
	protected $_settings = [];

	protected $_form_views = [
		self::FORM_VIEW_CLASSIC,
		self::FORM_VIEW_MODAL,
	];

	/**
	 * @param array $db_params - form params from database
	 */
	public function __construct(array $db_params = NULL)
	{
		$this->set_default_settings();

		$forms_manager = Manager::instance();

		$this->_db = $forms_manager->get_db();
		$this->_table = $forms_manager->get_table();

		$this->_fields = new Fields\Collection();
		$this->_styles = new Styles\Collection();

		if (!is_null($db_params)) {
			$this->set_db_params($db_params);
		}
	}

	/**
	 * Get default form settings
	 * @since 2.9.5
	 * @return array
	 */
	public static function get_default_settings()
	{
		return [
			'name'           => self::FORM_NAME_DEFAULT,
			//TODO: use 'title' or delete
			'title'          => [
				'type'  => self::FORM_TITLE_DEFAULT_TYPE,
				'value' => self::FORM_TITLE_DEFAULT_VALUE,
			],
			'status'         => self::FORM_STATUS_DEFAULT,
			'theme'          => self::FORM_THEME_DEFAULT,
			'names_position' => self::FIELD_NAME_POS_DEFAULT,
			'borders_type'   => self::FIELD_BORDER_DEFAULT,
			'form_paddings'  => self::FORM_PADDINGS_DEFAULT,
			'background'     => self::IN_STYLE_SETTINGS,
			'font'           => self::IN_STYLE_SETTINGS,
			'submit'         => [
				'text'  => 'Submit',
			],
			'modal'          => [
					'text'   => 'Open form',
			],
			'email'          => [
				'name'    => '',
				'subject' => self::FORM_NAME_DEFAULT,
				'to'      => (string)amoUser::instance()->get_data('login'),
			],
			'confirmation'   => [
				'type'  => self::CONFIRMATION_TYPE_TEXT,
				'value' => 'Form successfully submitted!',
			],
			'amo'            => [
				'id'      => '',
				'uid'     => '',
				'blocked' => self::FORM_BLOCKED_N,
			],
			'css'            => self::DEFAULT_CSS_SETTINGS,
			'js'             => self::DEFAULT_JS_SETTINGS,
			'view'           => self::FORM_VIEW_DEFAULT,
		];
	}

	/**
	 * Set default settings.
	 * If the key is not NULL, then only it will be applied.
	 * @since 2.9.5
	 * @param string $key
	 * @return $this
	 */
	public function set_default_settings($key = NULL)
	{
		$defaults = $this->get_default_settings();

		if (is_null($key)) {
			$this->_settings = $defaults;
		} elseif (array_key_exists($key, $defaults)) {
			$this->_settings[$key] = $defaults[$key];
		}

		return $this;
	}

	/**
	 * Set default fields
	 * @since 1.0.0
	 *
	 * @return $this
	 * @throws Argument
	 */
	public function set_default_fields()
	{
		$this->_fields
			->delete_all()
			->add(new Fields\Types\Name())
			->add(new Fields\Types\Email())
			->add(new Fields\Types\Textarea())
		;

		return $this;
	}

	/**
	 * Set default styles
	 * @since 3.0.0
	 *
	 * @return $this
	 * @throws Argument
	 */
	public function set_default_styles()
	{
		$this->_styles
				->delete_all()
				->add(new Styles\Types\Form())
				->add(new Styles\Types\Name())
				->add(new Styles\Types\Email())
				->add(new Styles\Types\Textarea())
				->add(new Styles\Types\Submit())
				->add(new Styles\Types\Modal())
		;

		return $this;
	}

	/**
	 * Set form params form db params
	 * @since 1.0.0
	 *
	 * @param array $db_params - row of params from DB
	 * @return $this
	 * @throws Argument
	 * @throws Runtime
	 */
	protected function set_db_params(array $db_params)
	{
		if (empty($db_params['id'])) {
			throw new Argument('Invalid form id');
		}
		if (empty($db_params['settings']) || empty($db_params['fields'])) {
			throw new Argument('Invalid form db params');
		}

		$db_params['settings'] = json_decode($db_params['settings'], TRUE);
		$db_params['fields'] = json_decode($db_params['fields'], TRUE);

		if(isset($db_params['styles'])){
			$db_params['styles'] = json_decode($db_params['styles'], TRUE);
		}

		if (empty($db_params['settings']) || !is_array($db_params['settings']) || !isset($db_params['fields']) || !is_array($db_params['fields'])) {
			throw new Runtime('Error decoding form params');
		}

		$migration = Migration::instance();
		if ($need_migrate = $migration->need_migrate($db_params)) {
			$db_params = $migration->migrate($db_params);
		}
		$this
			->set_id($db_params['id'])
			->set_settings($db_params['settings'])
			->set_fields($db_params['fields'])
			->set_styles($db_params['styles']);

		if ($need_migrate) {
			$this->save();
		}

		return $this;
	}

	/**
	 * Set form settings
	 * @since 1.0.0
	 *
	 * @param array $settings
	 * @return $this
	 * @throws Runtime
	 */
	public function set_settings(array $settings)
	{
		foreach ($this->_settings as $key => $value) {
			if (isset($settings[$key])) {
				$method = "set_$key";
				if (!method_exists($this, $method)) {
					throw new Runtime("Method '{$method}' not exists in " . __CLASS__);
				}
				$this->$method($settings[$key]);
			}
		}
		return $this;
	}

	/**
	 * Set fields by their params form db/request
	 * @since 1.0.0
	 *
	 * @param array $fields_params
	 * @return $this
	 * @throws \Amoforms\Exceptions\Validate
	 */
	public function set_fields(array $fields_params)
	{
		$this->_fields->fill_by_params($fields_params);
		return $this;
	}

	/**
	 * Fill empty settings
	 * @since 1.1.0
	 */
	protected function fill_settings()
	{
		//TODO: find, why function "wp_get_current_user" not exists sometimes
		if (!function_exists('wp_get_current_user')) {
			return;
		}
		/** @var \WP_User $user */
		$user = wp_get_current_user();

		if (empty($this->_settings['email']['name'])) {
			$this->set_email(['name' => $user->display_name]);
		}
		/* temporary disabled
		if (empty($this->_settings['email']['to'])) {
			$this->set_email(['to' => $user->user_email]);
		}
		*/
	}

	/**
	 * Create/update form on forms.amoCRM.com
	 * @since 2.6.0
	 */
	private function amo_update() {
		$amo_user = amoUser::instance();
		if (!$amo_user->is_full()) {
			return;
		}

		$amo_forms = Forms::instance();
		$amo_api = Api::instance();

		$credentials = $this->get('amo');
		$top_level_domain = $amo_api->get_top_level_domain($amo_user->get_data('subdomain'));
		if (empty($credentials['id'])) {
			list($id, $uid) = $amo_forms->register_form(
				$amo_user->get_data('subdomain'),
				$amo_user->get_data('login'),
				$amo_user->get_data('api_key'),
				$amo_user->get_primary_authorization_type(),
				$top_level_domain
			);
			$this->set_amo(['id' => $id, 'uid' => $uid]);
		} else {
			$amo_forms->update_form(
				$credentials['id'],
				$credentials['uid'],
				$amo_user->get_data('subdomain'),
				$amo_user->get_data('login'),
				$amo_user->get_data('api_key'),
				$amo_user->get_primary_authorization_type(),
				$top_level_domain
			);
		}
	}

	/**
	 * Duplicate form, save it to DB and return new instance.
	 * @since 2.9.5
	 * @return Form - new Form instance
	 * @throws Runtime
	 */
	public function duplicate()
	{
		$form = clone $this;
		$form->_id = NULL;
		$form->set_default_settings('amo')->save();

		return $form;
	}

	/**
	 * Save form to database
	 * @since 1.0.0
	 *
	 * @return $this
	 * @throws Runtime
	 */
	public function save()
	{
		$this->fill_settings();

		//FIXME: move to another place because it slows down ajax while editing fields:
		$this->amo_update();
		$data = new Query_Data([
			'settings' => ['%s', json_encode(Helpers::strip_slashes($this->get_settings()), JSON_UNESCAPED_UNICODE)],
			'fields'   => ['%s', json_encode(Helpers::strip_slashes($this->_fields->get_for_save()), JSON_UNESCAPED_UNICODE)],
			'version'  => ['%s', AMOFORMS_VERSION],
			'styles'   => ['%s', json_encode(Helpers::strip_slashes($this->_styles->get_for_save()), JSON_UNESCAPED_UNICODE)],
		]);

		if ($this->id()) {
			if ($this->_db->update($this->_table, $data->get_data(), ['id' => $this->id()], $data->get_formats()) === FALSE) {
				throw new Runtime('Error saving form to db');
			}
		} else {
			$this->_db->insert($this->_table, $data->get_data(), $data->get_formats());
			$form_id = $this->_db->insert_id;
			if (!$form_id) {
				throw new Runtime("Can't get id of inserted form");
			}
			$this->set_id($form_id);
		}

		return $this;
	}

	/**
	 * Add field to form
	 * @param Base_Field $field
	 * @param int        $position
	 * @return $this
	 * @throws Argument
	 */
	public function add_field(Base_Field $field, $position = NULL)
	{
		if (is_null($position)) {
			$this->_fields->add($field);
		} else {
			$this->_fields->insert($field, $position);
		}
		return $this;
	}

	/**
	 * Duplicate field
	 * @param int $id
	 * @return Base_Field|bool
	 */
	public function duplicate_field($id)
	{
		return $this->_fields->duplicate($id);
	}

	/**
	 * Edit field
	 * @param int $id
	 * @param array $params
	 * @return $this
	 * @throws Runtime
	 */
	public function edit_field($id, array $params)
	{
		/** @var Base_Field $field */
		if (!$field = $this->_fields->get_by_id($id)) {
			throw new Runtime('Field not found');
		}
		$field->set_params($params);
		return $this;
	}

	/**
	 * Delete field form form
	 * @param int $id
	 * @throws Argument
	 * @return $this
	 */
	public function delete_field($id)
	{
		$this->_fields->delete((int)$id);
		return $this;
	}

	/**
	 * Check for existing captcha field in form.
	 * @since 2.11.0
	 * @return bool
	 */
	public function has_captcha()
	{
		foreach ($this->get_fields() as $field) {
			if ($field['type'] === Fields\Types\Base_Field::TYPE_CAPTCHA) {
				return TRUE;
			}
		}

		return FALSE;
	}

	public function is_blocked() {
		return $this->get('amo', 'blocked') === self::FORM_BLOCKED_Y;
	}

	public function get($key, $sub_key = NULL) {
		$result = isset($this->_settings[$key]) ? $this->_settings[$key] : FALSE;

		if (!is_null($sub_key)) {
			$result = is_array($result) && isset($result[$sub_key]) ? $result[$sub_key] : NULL;
		}

		return $result;
	}

	/**
	 * Get all settings
	 * @return array
	 */
	public function get_settings() {
		return $this->_settings;
	}

	/**
	 * Get fields as array
	 * @return array
	 */
	public function get_fields() {
		return $this->_fields->to_array();
	}

	/**
	 * Get styles as array
	 * @return array
	 */
	public function get_styles() {
		return $this->_styles->to_array();
	}

	/**
	 * Get styles max id
	 * @return int
	 */
	public function get_styles_max_id()
	{
		return $this->_styles->get_max_id();
	}

	/**
	 * @return array
	 */
	public function get_title_types() {
		return $this->_title_types;
	}

	/**
	 * @return array
	 */
	public function get_confirmation_types() {
		return $this->_confirmation_types;
	}

	/**
	 * @return array
	 */
	public function get_statuses_types() {
		return $this->_statuses_types;
	}

	/**
	 * @return array
	 */
	public function get_form_views() {
		return $this->_form_views;
	}

	/**
	 * Get id
	 * @since 1.0.0
	 * @return int|null
	 */
	public function id() {
		return $this->_id;
	}

	/**
	 * @param int $id
	 * @return $this
	 */
	protected function set_id($id) {
		$this->_id = abs((int)$id);
		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	protected function set_name($name) {
		$this->_settings['name'] = Strings::escape($name);
		return $this;
	}

	/**
	 * @param array $title
	 * @return $this
	 */
	protected function set_title(array $title) {
		if (!empty($title['type']) && in_array($title['type'], $this->_title_types, TRUE)) {
			$this->_settings['title']['type'] = $title['type'];
		}
		if (!empty($title['value'])) {
			$this->_settings['title']['value'] = Strings::escape($title['value']);
		}
		return $this;
	}

	/**
	 * @param string $status
	 * @return $this
	 */
	protected function set_status($status) {
		if (in_array($status, $this->_statuses_types, TRUE)) {
			$this->_settings['status'] = $status;
		}
		return $this;
	}

	/**
	 * @param int $theme - theme id
	 * @return $this
	 */
	protected function set_theme($theme) {
		$id = (int)$theme['id'];
		$styles = $this->_styles->to_array();
		$color = ($id == 2) ? '#fff' : '#313942';
		$margin = ($id == 2) ? '0px 10px 5px 0px' : '0px 10px 0px 0px';
		foreach($styles as &$style){
			if(isset($style['elements']['field_label'])){
				$style['elements']['field_label']['color'] = $color;
				$style['elements']['field_label']['margin'] = $margin;
			}
		}
		$this->_styles->fill_by_params($styles);

		$this->_settings['theme'] = $id; //TODO: check for valid value
		return $this;
	}

	/**
	 * @param string $form_paddings
	 * @return $this
	 */
	protected function set_form_paddings($form_paddings) {
		if (in_array($form_paddings, $this->_form_paddings, TRUE)) {
			/** @var Base_Style $style */
			if ($style = $this->_styles->get_by_id(self::FORM_DEFAULT_ID)) {
				$params = $style->to_array();
				switch ($form_paddings) {
					case 'yes':
						$params['elements']['form_container']['padding'] = '1px 40px 40px 40px';
						$params['elements']['form_container']['border-width'] = '1px';
						$params['elements']['form_container']['border-color'] = 'rgba(0, 0, 0, 0.13)';
						$params['elements']['form_container']['border-style'] = 'solid';
						break;
					case 'no':
						$params['elements']['form_container']['padding'] = '1px 0px 0px 0px';
						$params['elements']['form_container']['border-width'] = '0px';
						break;
					default:
						break;
				}
				$params['elements']['form_container']['border-radius'] = '0px';
				$style->set_params($params);
			}
			$this->_settings['form_paddings'] = $form_paddings;
		}
		return $this;
	}

	/**
	 * @param string $position
	 * @return $this
	 */
	protected function set_names_position($position) {
		if (in_array($position, $this->_names_positions, TRUE)) {
			$this->_settings['names_position'] = $position;
		}
		return $this;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	protected function set_borders_type($type) {
		if (in_array($type, $this->_fields_borders_types, TRUE)) {
			$styles = $this->_styles->to_array();
			if(!empty($styles)){
				foreach ($styles as &$style){
					if(isset($style['elements']['field_element'])){
						$style['elements']['field_element']['border-radius'] = ($type == 'rounded') ? '8px' : '0px';
					}
				}
				$this->_styles->fill_by_params($styles);
			}
			$this->_settings['borders_type'] = $type;
		}
		return $this;
	}

	/**
	 * @param array $font
	 * @return $this
	 */
	protected function set_font($font) {
		if($font && is_array($font)){
			/** @var Base_Style $style */
			if ($style = $this->_styles->get_by_id(self::FORM_DEFAULT_ID)) {
				$params = $style->to_array();
				if (!empty($font['family']) && in_array($font['family'], $this->_available_fonts, TRUE)) {
					$params['elements']['form_container']['font-family'] = $font['family'];
				}
				if (!empty($font['size'])) {
					$params['elements']['form_container']['font-size'] = abs((int)$font['size']) . 'px';
				}
				$style->set_params($params);
			}
		}
		return $this;
	}

	/**
	 * @param array $background
	 * @return $this
	 */
	protected function set_background($background) {
		if($background && is_array($background)){
			/** @var Base_Style $style */
			if ($style = $this->_styles->get_by_id(self::FORM_DEFAULT_ID)) {
				if (!empty($background['type']) && !empty($background['value'])) {
					$params = $style->to_array();
					switch ($background['type']){
						case 'color':
							$params['elements']['form_container']['background-color'] = $background['value'];
							$params['elements']['form_container']['background-image'] = '';
							break;
						case 'image':
							$params['elements']['form_container']['background-color'] = 'transparent';
							$params['elements']['form_container']['background-image'] = $this->validate_background($background['value']);
							break;
						default:
							break;
					}
					$style->set_params($params);
				}
			}
		}
		return $this;
	}

	/**
	 * @since 3.1.1
	 * @param string $value
	 * @return string
	 */
	protected function validate_background ($value) {
		$pattern = '/url\([\'"]?.*[\'"]?\)/i';
		if(preg_match($pattern, $value) !== 1 && $value != 'none'){
			$value = 'url('.$value.')';
		}
		return $value;
	}

	/**
	 * @since 2.17.0
	 * @param string $css
	 * @return $this
	 */
	protected function set_css($css)
	{
		if (($css = Strings::sanitize_css($css)) !== FALSE) {
			$this->_settings['css'] = $css;
		}

		return $this;
	}

	/**
	 * @since 2.17.0
	 * @param string $js
	 * @return $this
	 */
	protected function set_js($js)
	{
		if (is_string($js)) {
			$this->_settings['js'] = $js;
		}

		return $this;
	}

	/**
	 * @since 2.6.0
	 * @param array $values
	 * @return $this
	 */
	public function set_amo(array $values) {
		foreach (array_keys($this->_settings['amo']) as $field) {
			if (!empty($values[$field])) {
				$this->_settings['amo'][$field] = $values[$field];
			}
		}

		return $this;
	}

	/**
	 * @since 2.6.0
	 * @param array $settings
	 * @return $this
	 */
	protected function set_submit(array $settings)
	{
		if(isset($settings['text'])){
			$this->_settings['submit']['text'] = Strings::escape($settings['text']);
		}
		if(isset($settings['color'])){
			/** @var Base_Style $style */
			if ($style = $this->get_type_style('submit')) {
				$params = $style->to_array();
				$params['elements']['submit_button']['background-color'] = $settings['color'];
				$style->set_params($params);
			}
		}
		return $this;
	}

	/**
	 * @since 2.6.0
	 * @param array $settings
	 * @return $this
	 */
	protected function set_modal(array $settings)
	{
		if (!empty($settings['text'])) {
			$this->_settings['modal']['text'] = Strings::escape($settings['text']);
		}
		return $this;
	}

	/**
	 * Set email settings
	 * @since 1.0.0
	 * @param array $email_settings
	 * @return $this
	 */
	protected function set_email(array $email_settings) {
		foreach ($this->_settings['email'] as $key => $old_value) {
			if (!empty($email_settings[$key])) {
				if ($key === 'email' && !filter_var($email_settings[$key], FILTER_VALIDATE_EMAIL)) {
					continue;
				}
				$this->_settings['email'][$key] = Strings::escape($email_settings[$key]);
			}
		}
		return $this;
	}

	/**
	 * @since 1.0.0
	 * @param array $page_settings
	 * @return $this
	 */
	protected function set_page(array $page_settings) {
		foreach ($this->_settings['page'] as $key => $value) {
			if (isset($page_settings[$key])) {
				$this->_settings['page'][$key] = (bool)$page_settings[$key];
			}
		}
		return $this;
	}

	/**
	 * @since 1.0.0
	 * @param array $confirmation
	 * @return $this
	 */
	protected function set_confirmation(array $confirmation) {
		if (!empty($confirmation['type']) && in_array($confirmation['type'], $this->_confirmation_types, TRUE)) {
			$this->_settings['confirmation']['type'] = $confirmation['type'];
		}
		if (isset($confirmation['value'])) {
			$this->_settings['confirmation']['value'] = Strings::escape($confirmation['value']);
		}
		return $this;
	}

	/**
	 * @param string $view
	 * @return $this
	 */
	protected function set_view($view) {
		if (in_array($view, $this->_form_views, TRUE)) {
			$this->_settings['view'] = $view;
		}
		return $this;
	}

	protected function set_styles($styles) {
		$this->_styles->fill_by_params($styles);
		return $this;
	}

	/**
	 * @since 2.18.5
	 * @return array|FALSE
	 */
	protected function get_captcha_field()
	{
		foreach ($this->get_fields() as $field) {
			if ($field['type'] === Fields\Types\Base_Field::TYPE_CAPTCHA) {
				return $field;
			}
		}

		return FALSE;
	}

	/**
	 * @since 2.18.5
	 * @return bool
	 */
	public function is_need_captcha_ntp()
	{
		if ($field = $this->get_captcha_field()) {
			return (isset($field['options']['captcha_ntp']) && $field['options']['captcha_ntp'] === Fields\Types\Base_Field::CAPTCHA_NTP_ENABLED);
		}

		return FALSE;
	}

	/**
	 * @since 3.0.0
	 * Edit style
	 * @param int $id
	 * @param array $params
	 * @return $this
	 * @throws Runtime
	 */
	public function edit_style($id, array $params)
	{
		$maker = Styles\Manager::instance();
		/** @var Base_Style $style */
		if (!$style = $this->_styles->get_by_id($id)) {
			$this->_styles->add($maker->make_style($params['type'], $params));
			$style = $this->_styles->get_by_id($id);
		}
		if((bool)$params['is_type_style']){
			if($old_type_models = $this->_styles->get_by_type($params['type'])){
				/** @var Base_Style $model */
				foreach($old_type_models as $model){
					$old_id = (int)$model->id();
					if($old_id != $id){
						$this->_styles->delete($old_id);
					}
				}
			}
			unset($params['object_id']);
			$style->set_params($params);
		} else {
			if($old_type_models = $this->_styles->get_by_type($params['type'])){
				/** @var Base_Style $model */
				foreach($old_type_models as $model){
					$old_id = (int)$model->id();
					$for_type = $model->get_is_style_type();
					if($for_type && $old_id == $id){
						$max_id = $this->_styles->get_max_id();
						$params['id'] = $max_id + 1;
						$this->_styles->add($maker->make_style($params['type'], $params));
					} elseif(!$for_type && $old_id == $id){
						$style->set_params($params);
					}
				}
			}
		}

		return $this;
	}

	/**
	 * @since 3.0.0
	 * Add style
	 * @param Base_Style $style
	 * @param int $field_id
	 * @return $this
	 */
	public function add_style(Base_Style $style, $field_id = 0)
	{
		if(!$this->get_type_style($style->get_type())){
			$style->set_params(['object_id' => $field_id]);
			$this->_styles->add($style);
		}

		return $this;
	}

	/**
	 * @since 3.0.0
	 * Delete style
	 * @param int $field_id
	 * @return $this
	 */
	public function delete_style($field_id)
	{
		if($style = $this->_styles->get_by_element_id($field_id)){
			$this->_styles->delete((int)$style->id());
		}
		return $this;
	}

	/**
	 * @since 3.0.0
	 * Get style by type
	 * @param string $type
	 * @return Base_Style $model | bool
	 */
	public function get_type_style($type){
		$type = (string)$type;
		$type_style = NULL;
		if($old_type_models = $this->_styles->get_by_type($type)){
			foreach($old_type_models as $model){
				/** @var Base_Style $model */
				if($model->get_is_style_type()){
					$type_style = $model;
				}
			}
		}

		return is_null($type_style) ? FALSE :$type_style;
	}

	/**
	 * @since 3.0.1
	 * Get Form style
	 * @return array
	 */
	public function get_form_style() {

		if(!$this->get_type_style('form')){
			$this->_styles->add(new Styles\Types\Form());
		}

		return $this->get_type_style('form')->to_array();
	}

	/**
	 * Reset style
	 * @since 3.1.1
	 * @param $id
	 * @return $this
	 */
	public function reset_style($id){

		/** @var Base_Style $style */
		if (!$style = $this->_styles->get_by_id($id)) {
			throw new Runtime('Style not found');
		}
		$maker = Styles\Manager::instance();

		$params = $style->to_array();
		unset($params['elements']);
		$this->_styles->delete((int)$style->id());
		$this->_styles->add($maker->make_style($params['type'], $params));

		return $this;
	}

	/**
	 * Get default style
	 * @since 3.1.1
	 * @param $id
	 * @return array
	 */
	public function get_default_style($id){
		/** @var Base_Style $style */
		if (!$style = $this->_styles->get_by_id($id)) {
			throw new Runtime('Style not found');
		}
		$maker = Styles\Manager::instance();
		$style = $maker->make_style($style->get_type());
		$style->id($id);
		return $style->to_array();
	}

	/**
	 * Reset all form styles
	 * @since 3.1.1
	 * @return $this
	 */
	public function reset_form_styles() {
		$this->_styles->rewind();
		$ids = [];
		for($i = 0; $i < $this->_styles->count(); $i++) {
			if($this->_styles->current()->get_type() != 'modal'){
				$ids[] = $this->_styles->current()->id();
			}
			$this->_styles->next();
		}
		foreach($ids as $id){
			$this->reset_style($id);
		}
		return $this;
	}

	/**
	 * Get default form style
	 * @since 3.1.1
	 * @return array
	 */
	public function get_default_form_style() {
		$default = [];
		foreach ($this->_styles as $style) {
			$default[] = $this->get_default_style((int)$style->id());
		}
		return $default;
	}

	/**
	 * Duplicate field style
	 * @since 3.1.9
	 * @return Base_Style
	 */
	public function duplicate_field_style($old_field, $new_field, $type) {
		/** @var Base_Style $style */
		if ($style = $this->_styles->get_by_element_id($old_field)) {
			$maker = Styles\Manager::instance();
			$max_id = $this->_styles->get_max_id();
			$params = $style->to_array();
			$params['id'] = $max_id + 1;
			$params['object_id'] = $new_field;
			$this->_styles->add($maker->make_style($params['type'], $params));
			$style = $this->_styles->get_by_element_id($new_field);
		} else {
			$style = $this->get_type_style($type);
		}

		return $style;
	}
}
