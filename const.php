<?php
defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

define('AMOFORMS_VERSION', '3.1.19');

// Versions for migration
define('AMOFORMS_VERSIONS_JSON', json_encode([
	'2.0.0',
	'2.8.0',
	'2.21.1',
	'3.0.3',
	'3.1.12',
	'3.1.18',
	AMOFORMS_VERSION,
]));

define('AMOFORMS_MINIMUM_WP_VERSION', '4.0');
define('AMOFORMS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AMOFORMS_PLUGIN_DIR', plugin_dir_path(__FILE__));

define('AMOFORMS_ROOT', __DIR__);
define('AMOFORMS_NS', 'Amoforms');
define('AMOFORMS_CLASSES_DIR', AMOFORMS_ROOT . '/classes');
define('AMOFORMS_VENDOR_DIR', AMOFORMS_CLASSES_DIR . '/Vendor');
define('AMOFORMS_VIEWS_DIR', AMOFORMS_ROOT . '/views');
define('AMOFORMS_IMAGES_DIR', AMOFORMS_ROOT . '/images');
define('AMOFORMS_IMAGES_URL', AMOFORMS_PLUGIN_URL . 'images');
define('AMOFORMS_CSS_DIR', AMOFORMS_ROOT . '/css');
define('AMOFORMS_CSS_URL', AMOFORMS_PLUGIN_URL . 'css');
define('AMOFORMS_JS_URL', AMOFORMS_PLUGIN_URL . 'js');
define('AMOFORMS_PLUGIN_CODE', basename(AMOFORMS_ROOT));
define('AMOFORMS_USE_MIN_JS', FALSE);

define('AMOFORMS_AJAX_SUCCESS', TRUE);
define('AMOFORMS_AJAX_ERROR', FALSE);

define('AMOFORMS_OPTION_AB_TESTS', 'amoforms_ab_tests');
define('AMOFORMS_OPTION_SETTINGS', 'amoforms_settings');

define('AMOFORMS_OPTION_STATS_REPORTING',        'amoforms_stats_reporting');
define('AMOFORMS_OPTION_INSTALL_DATE',           'amoforms_plugin_install_date');
define('AMOFORMS_OPTION_FIRST_VIEW_DATE',        'amoforms_first_view_date');
define('AMOFORMS_OPTION_GET_STARTED_DATE',       'amoforms_get_started_date');
define('AMOFORMS_OPTION_REGISTRATION_DATE',      'amoforms_registration_date');
define('AMOFORMS_OPTION_FIRST_FORM_SHOW_DATE',   'amoforms_first_form_show_date');
define('AMOFORMS_OPTION_FIRST_FORM_SUBMIT_DATE', 'amoforms_first_form_submit_date');
define('AMOFORMS_OPTION_PREVIEW_PAGE_ID',        'amoforms_preview_page_id');

define('AMOFORMS_PREVIEW_PAGE_NAME', 'amoforms_preview_page');

define('AMOFORMS_OPTIONS_JSON', json_encode([
	AMOFORMS_OPTION_AB_TESTS,
	AMOFORMS_OPTION_SETTINGS,
	AMOFORMS_OPTION_STATS_REPORTING,
	AMOFORMS_OPTION_INSTALL_DATE,
	AMOFORMS_OPTION_FIRST_VIEW_DATE,
	AMOFORMS_OPTION_GET_STARTED_DATE,
	AMOFORMS_OPTION_REGISTRATION_DATE,
	AMOFORMS_OPTION_FIRST_FORM_SHOW_DATE,
	AMOFORMS_OPTION_FIRST_FORM_SUBMIT_DATE,
]));

$config = [
	'dev_env' => FALSE,
	'api_base_url_pattern' => 'https://%s.amocrm.%s/',
	'promo_base_url_com'   => 'https://www.amocrm.com/',
	'promo_base_url_ru'    => 'https://www.amocrm.ru/',
	'forms_base_url'       => 'https://forms.amocrm.com/',
	'top_level_domains'    => [
		'ru'  => 'ru',
		'com' => 'com',
	],
];

if (file_exists(__DIR__ . '/dev_config.php')) {
	require_once __DIR__ . '/dev_config.php';
}
$use_promo_ru = get_option('USE_PROMO_RU');

define('AMOFORMS_DEV_ENV', $config['dev_env']);
define('AMOFORMS_API_BASE_URL_PATTERN', $config['api_base_url_pattern']);
define('AMOFORMS_PROMO_BASE_URL_RU', $config['promo_base_url_ru']);
define('AMOFORMS_PROMO_BASE_URL_COM', $config['promo_base_url_com']);
define('AMOFORMS_PROMO_BASE_URL', !empty($use_promo_ru) && $use_promo_ru == 'Y' ? AMOFORMS_PROMO_BASE_URL_RU : AMOFORMS_PROMO_BASE_URL_COM);
define('AMOFORMS_FORMS_BASE_URL', $config['forms_base_url']);
define('AMOFORMS_DOMAIN_RU', $config['top_level_domains']['ru']);
define('AMOFORMS_DOMAIN_COM', $config['top_level_domains']['com']);
define('AMOFORMS_DOMAIN_DEFAULT', $config['top_level_domains']['com']);
