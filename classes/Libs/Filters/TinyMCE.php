<?php
namespace Amoforms\Libs\Filters;

use Amoforms\Access;
use Amoforms\Models\Forms;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class TinyMCE
 * @since 1.0.0
 * @package Amoforms\Libs\Filters
 */
class TinyMCE implements Interfaces\Filter
{
	protected $_right = 'manage_options';

	/**
	 * Register plugin
	 * @since 1.0.0
	 * @return $this
	 */
	public function register()
	{
		if ($forms = $this->get_forms_settings()) {
			add_filter('tiny_mce_before_init', function($settings) use ($forms) {
				$settings['amoforms'] = json_encode(['forms' => $forms]);
				return $settings;
			});
			add_filter('mce_external_plugins', [$this, 'add_plugin']); // load our js plugin for TinyMCE
			add_filter('mce_buttons', [$this, 'add_button']); // add button to editor
		}

		return $this;
	}

	/**
	 * Get forms settings
	 * @since 2.9.0
	 * @return array
	 */
	public function get_forms_settings()
	{
		$forms = [];

		/** @var Forms\Interfaces\Form $form */
		foreach (Forms\Manager::instance()->get_forms_collection() as $form) {
			if ($form->get('email')['to']) {
				$forms[] = [
					'text'  => $form->get('name'),
					'value' => $form->id(),
				];
			}
		}

		return $forms;
	}

	/**
	 * Add our plugin for TinyMCE
	 * @since 1.0.0
	 * @param array $plugins - array of paths to TinyMCE plugins
	 * @return array
	 */
	public function add_plugin($plugins)
	{
		if ($this->check_rights()) {
			$plugins[AMOFORMS_PLUGIN_CODE] = AMOFORMS_PLUGIN_URL . 'js/plugins/mce/editor_plugin.js';
		}
		return $plugins;
	}

	/**
	 * Add our buttons to TinyMCE plugin
	 * @since 1.0.0
	 * @param array $buttons - buttons of TinyMCE plugin
	 * @return array
	 */
	public function add_button($buttons)
	{
		if ($this->check_rights()) {
			$buttons[] = 'amoforms_add_form';
		}
		return $buttons;
	}

	/**
	 * Check rights for using this plugin
	 * @return bool
	 */
	protected function check_rights()
	{
		return Access::instance()->check($this->_right);
	}
}
