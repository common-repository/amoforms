<?php
/** @var Amoforms\Views\Interfaces\Base $this */
defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Models\Forms\Interfaces\Form;

/** @var Form $form */
$form = $this->get('form');
$settings = $form->get_settings();

?>
<link rel="stylesheet" href="<?php echo AMOFORMS_CSS_URL . '/preview.css' ?>">
<div class="amoforms amoforms-preview">

	<div class="amoforms__message amoforms__success_message" id="amoforms__success_message_wrapper">
		<p id="amoforms__success_message_text">Changes successfully saved</p>
	</div>
	<div class="amoforms__message amoforms__error_message" id="amoforms__error_message_wrapper">
		<p id="amoforms__error_message_text">An error has occurred</p>
	</div>

	<div class="amoforms__form__actions--clear">
		<div class="amoforms__fields__expander" id="amoforms_sidebar">
			<div class="amoforms__fields__expander__item expanded">
				<div class="amoforms__fields__expander__item__head">
					<p class="amoforms__fields__expander__item__head__text">Custom CSS</p>
				</div>

				<div class="amoforms__fields__expander__item__content">
					<div class="amoforms__fields__expander__item__content__inner">
						<p>Type your CSS here</p>

						<div class="amoforms__fields__expander__item__content__css_area amoforms__custom_code_area_wrapper clearfix">
							<form id="amoforms_custom_css_form">
								<textarea name="css"
										  id="amoforms_css_area"
										  class="amoforms__custom_code_area amoforms__fields__edit__textarea"
										  placeholder=".amoforms {}"
										  title="CSS Area"><?php echo $settings['css'] ?></textarea>
								<input type="hidden" name="form[id]" value="<?php echo $form->id() ?>">
								<input type="hidden" name="type" value="css">
							</form>
						</div>

						<div class="amoforms__custom_code__save_buttons__wrapper">
							<button id="amoforms_save_css_btn" data-type="css" class="save_button amoforms_save_css_button" disabled>Save</button>
						</div>
					</div>
				</div>
			</div>

			<div class="amoforms__fields__expander__item">
				<div class="amoforms__fields__expander__item__head">
					<p class="amoforms__fields__expander__item__head__text">Custom JS</p>
				</div>
				<div class="amoforms__fields__expander__item__content">
					<div class="amoforms__fields__expander__item__content__inner">
						<p>Type your JS here</p>

						<div class="amoforms__fields__expander__item__content__js_area amoforms__custom_code_area_wrapper clearfix">
							<form id="amoforms_custom_js_form">
								<textarea name="js"
										  id="amoforms_js_area"
										  class="amoforms__custom_code_area amoforms__fields__edit__textarea"
										  placeholder="console.log('...');"
										  title="JS Area"><?php echo $settings['js'] ?></textarea>
								<input type="hidden" name="form[id]" value="<?php echo $form->id() ?>">
								<input type="hidden" name="type" value="js">
							</form>
						</div>

						<div class="amoforms__custom_code__save_buttons__wrapper">
							<button id="amoforms_test_js_btn" class="save_button">Test</button>
							<button id="amoforms_save_js_btn" data-type="js" class="save_button amoforms_save_js_button" disabled>Save</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="amoforms__form__actions">
			<div class="amoforms__support_info_wrapper">
				<p>
					For detailed instructions see
					<a href="http://lp.amocrm.com/wordpress/amoForms_Custom_CSS_JS_User_Guide.pdf" target="_blank">amoForms Custom CSS/JS guide</a>.
					<br>
					<br>
					If you need any help, <br>
					please email us at
					<a href="mailto:support@amocrm.com" target="_blank">support@amocrm.com</a><br>
					or call us on +1 (415) 523-7743
				</p>
			</div>
		</div>
	</div>

	<div class="amoforms_form_preview_form_wrapper">
		<?php $this->render('form/form') ?>
	</div>
</div>
<?php
wp_enqueue_script(
	'amoforms_preview_settings',
	plugins_url('/amoforms/js/settings/preview.js'),
	array(
		'jquery',
		'backbone',
	)
);
