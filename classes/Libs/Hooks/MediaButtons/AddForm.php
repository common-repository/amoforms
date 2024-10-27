<?php
namespace Amoforms\Libs\Hooks\MediaButtons;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Models\Forms;

/**
 * Class AddForm
 * @since 2.11.8
 * @method static $this instance
 * @package Amoforms\Libs\Hooks\MediaButtons
 */
class AddForm extends BaseButton
{
	public function register()
	{
		if ($this->get_forms()) {
			add_filter('media_buttons', [$this, 'render_button'], 11);
			add_action('admin_footer', [$this, 'insert_front_params']);
		}

		return $this;
	}

	/**
	 * Render button for adding form
	 * @since 2.11.8
	 * @param string $context
	 */
	public function render_button($context)
	{
		if ($context === $this->_context) {
			echo '<span id="amoforms-add-form-button" class="button amoforms-add-form-button" data-editor="content" title="Add form"><span class="wp-media-buttons-icon"></span>Add form</span>';
		}
	}

	/**
	 * Inserting front-end params
	 * @since 2.11.8
	 */
	public function insert_front_params()
	{
		wp_enqueue_style('amoforms_add_form_css', AMOFORMS_CSS_URL . '/add_form_button.css');

		$this->_view
			->set('forms', $this->get_forms())
			->render('post_editor/add_form_button');

		wp_enqueue_script('amoforms_add_form_button', AMOFORMS_JS_URL . '/plugins/add_form_button/add_form_button.js');
	}

	/**
	 * Get forms list
	 * @since 2.11.8
	 * @return array
	 */
	protected function get_forms()
	{
		static $forms = NULL;
		if (!is_null($forms)) {
			return $forms;
		}
		$forms = [];

		/** @var Forms\Interfaces\Form $form */
		foreach (Forms\Manager::instance()->get_forms_collection() as $form) {
			if ($form->get('email')['to']) {
				$forms[] = [
					'name' => $form->get('name'),
					'id'   => $form->id(),
				];
			}
		}

		return $forms;
	}
}
