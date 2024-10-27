<?php
namespace Amoforms\Libs\UI;

use Amoforms\Helpers\Strings;
use Amoforms\Libs\Locale\I18n;
use Amoforms\Traits\Singleton;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Mustache
 * @method static $this instance
 * @package Amoforms\Libs\UI
 */
class Mustache
{
	use Singleton;

	/** @var Mustache_Engine $_engine */
	protected $_engine;

	protected function __construct()
	{
		$this->_engine = new Mustache_Engine([
			'template_class_prefix'  => '__AmoformsTemplates_',
			'cache_lambda_templates' => FALSE,
			'loader'                 => new Mustache_Loader_FilesystemLoader(AMOFORMS_VIEWS_DIR),
			'partials_loader'        => new Mustache_Loader_FilesystemLoader(AMOFORMS_VIEWS_DIR . '/partials'),
			'helpers'                => ['i18n' => function ($text) {
				return I18n::get($text);
			}],
			'escape'                 => function ($value) {
				return Strings::escape($value);
			},
			'charset'                => 'UTF-8',
			'strict_callables'       => TRUE,
			'pragmas'                => [Mustache_Engine::PRAGMA_FILTERS],
		]);
	}

	/**
	 * @return Mustache_Engine
	 */
	public function get_engine() {
		return $this->_engine;
	}
}
