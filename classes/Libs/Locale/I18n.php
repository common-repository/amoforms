<?php
namespace Amoforms\Libs\Locale;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class I18n
 * @since 1.0.0
 * @package Amoforms\Libs\Locale
 */
class I18n implements Interfaces\I18n
{
	/**
	 * Current lang
	 * @var string
	 */
	protected static $_lang;

	//TODO: move to separate file
	protected static $_langs = [
		'confirmation_type_text'     => 'Text',
		'confirmation_type_wp_page'  => 'WordPress Page',
		'confirmation_type_redirect' => 'Redirect',

		'field_type_heading'         => 'Heading',
		'field_type_name'            => 'Name',
		'field_type_phone'           => 'Phone',
		'field_type_email'           => 'Email',
		'field_type_company'         => 'Company',
		'field_type_textarea'        => 'Text area',
		'field_type_text'            => 'Text',
		'field_type_number'          => 'Number',
		'field_type_select'          => 'Select',
		'field_type_multiselect'     => 'Multiselect',
		'field_type_radio'           => 'Radio',
		'field_type_checkbox'        => 'Checkbox',
		'field_type_date'            => 'Date',
		'field_type_url'             => 'URL',
		'field_type_address'         => 'Address',
		'field_type_file'            => 'File',
		'field_type_instructions'    => 'Instructions',
		'field_type_captcha'         => 'Captcha',
		'field_type_line'            => 'Line',
		'field_type_city'            => 'City',
		'field_type_state'           => 'State',
		'field_type_country'         => 'Country',
		'field_type_zippost'         => 'Zip/Post',
		'field_type_antispam'        => 'Antispam',
		'field_type_rating'          => 'Rating',
		'field_type_tax'     	     => 'Tax',
		'field_type_total'     	     => 'Total',
	];

	/**
	 * Get translated string
	 * @param string $string
	 * @return string
	 */
	public static function get($string)
	{
		return isset(self::$_langs[$string]) ? self::$_langs[$string] : $string;
	}

	/**
	 * Get current lang
	 * @since 2.15.11
	 * @return string
	 */
	public static function get_lang()
	{
		if (!self::$_lang) {
			self::$_lang = 'en';
			if ($locale = get_locale()) {
				$locale = strtolower($locale);
				if (strlen($locale) === 2) {
					self::$_lang = $locale;
				} else {
					$langs = explode('_', $locale);
					if (isset($langs[0])) {
						self::$_lang = $langs[0];
					} else {
						self::$_lang = 'en';
					}
				}
			}
		}

		return self::$_lang;
	}
}
