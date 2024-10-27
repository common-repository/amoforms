<?php
/** @var Amoforms\Views\Interfaces\Base $this */
use Amoforms\Helpers;
use Amoforms\Helpers\Strings;
use Amoforms\Libs\Locale\I18n;
use Amoforms\Models\Forms\Form;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/** @var Form $form */
$form = $this->get('form');
$settings = $form->get_settings();
$fields = $form->get_fields();
$ga_enabled = $this->get('ga_enabled');
$styles = $form->get_styles();
?>
<link rel="stylesheet" href="<?php echo AMOFORMS_CSS_URL . '/app.css' ?>">
<link rel="stylesheet" href="<?php echo AMOFORMS_CSS_URL . '/form_settings.css' ?>">
<link rel="stylesheet" href="<?php echo includes_url('css/editor.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?php echo AMOFORMS_CSS_URL ?>/tooltipster/tooltipster.css" />
<link rel="stylesheet" type="text/css" href="<?php echo AMOFORMS_CSS_URL ?>/tooltipster/themes/tooltipster-shadow.css" />
<script src="<?php echo includes_url('js/tinymce/tinymce.min.js') ?>"></script>
<script>
	<?php
	$form_settings = [
		'type'         => $settings['confirmation']['type'],
		'value'        => ($settings['confirmation']['type'] === Form::CONFIRMATION_TYPE_TEXT) ? Strings::un_escape($settings['confirmation']['value']) : $settings['confirmation']['value'],
		'current_type' => $settings['confirmation']['type'],
		'list_items'   => [],
	];

	foreach (get_all_page_ids() as $page_id) {
		$link = get_permalink($page_id);
		$form_settings['list_items'][] = [
			'text'     => get_the_title($page_id),
			'value'    => $link,
			'selected' => $settings['confirmation']['value'] == $link  ? 'selected' : ''
		];
	}
    ?>

	window.AMOFORMS = window.AMOFORMS || {};
	AMOFORMS.page_settings = {
		form: <?php echo json_encode($form_settings) ?>
	};
</script>
<?php
wp_register_script('Colorpicker', AMOFORMS_JS_URL . '/vendor/colorpicker/colorpicker.js');
wp_register_script('mustache', AMOFORMS_JS_URL . '/vendor/mustache/mustache.js');
wp_register_script('tooltipster', AMOFORMS_JS_URL . '/vendor/tooltipster/jquery.tooltipster.min.js', array('jquery'));
wp_register_script('amoforms_form', plugins_url('/amoforms/js/core/form.js'), array('jquery', 'backbone'));
wp_register_script('amoforms_confirm', plugins_url('/amoforms/js/core/confirm.js'), array('jquery', 'backbone'));
wp_register_script('amoforms_fn', plugins_url('/amoforms/js/core/fn.js'), array('jquery'));

wp_enqueue_script(
	'amoforms_form_settings',
	plugins_url('/amoforms/js/settings/form.js'),
	array(
		'jquery',
		'underscore',
		'mustache',
		'amoforms_confirm',
		'amoforms_fn',
		'amoforms_form',
		'Colorpicker',
		'tooltipster',
	)
);
?>
<div id="templates">
<script type="x-tmpl-mustache" id="amoforms__text_editor__textarea">
    <div class="amoforms__text_editor__textarea_wrapper">
        <textarea name="form[settings][confirmation][value]" class="amoforms__text_editor__textarea" id="text-editor">{{value}}</textarea>
    </div>
</script>
<script type="x-tmpl-mustache" id="amoforms__text_editor__select">
    <div class="select_wrapper">
        <select name="form[settings][confirmation][value]" title="Pages" class="amoforms__text_editor__select">
        	{{#list_items}}
        	<option value="{{value}}" {{selected}}>{{text}}</option>
            {{/list_items}}
		</select>
	</div>
	</script>
	<script type="x-tmpl-mustache" id="amoforms__text_editor__input">
		<input type="text" name="form[settings][confirmation][value]" placeholder="http://" value="{{value}}" title="text area" class="amoforms__text_editor__input">
	</script>
</div>
<div class="amoforms amoforms-form">
	<div class="amoforms__message amoforms__success_message">
		<p>Changes successfully saved</p>
	</div>
	<div class="amoforms__message amoforms__error_message">
		<p>Changes not saved</p>
	</div>

	<div class="wrap amoforms_form-setting_page">
		<h2>General Form Settings</h2>
		<form method="post" action="" novalidate="novalidate" id="general-form-settings">
			<?php settings_fields($this->get('nonce_field'));?>
			<input type="hidden" name="form[id]" value="<?php echo $form->id() ?>">
			<input type="hidden" name="action" value="amoforms_update_form">
			<div class="amoforms__section_top">
				<div class="amoforms__form-setting__row__inner">
					<span class="amoforms__form-setting__row__inner__name" >Form Name</span>
					<input type="text" class="amoforms__form-setting__text-input" placeholder="Call Back Request" title="Form name" name="form[settings][name]" id="amoforms-form-name" value="<?php echo $settings['name'] ?>"><br />
					<span class="amoforms__form-setting__row__inner__descr">This name is used for admin purposes</span>
				</div>

				<div class="amoforms__form-setting__row__inner">
					<div class="amoforms__radio_wrapper">
						<span class="amoforms__form-setting__row__inner__name">Form Status</span>
						<?php foreach ($form->get_statuses_types() as $type) { ?>
							<div class="amoforms_form_status_radio_btn_wrapper js-amoforms_form_status_radio_btn_wrapper">
								<input id="amoforms_<?php echo $type ?>" name="form[settings][status]" type="radio" value="<?php echo $type ?>" <?php echo $settings['status'] === $type ? 'checked="checked"' : '' ?>>
								<label for="amoforms_<?php echo $type ?>"><?php echo $type === 'public' ? 'Published' : 'Draft' ?></label>
							</div>
						<?php } ?>
					</div>
					<span class="amoforms__form-setting__row__inner__descr">Change the status from Published to Draft to prevent form output without removing the shortcode.</span>
				</div>

				<div class="amoforms__form-setting__row__inner">
					<div class="amoforms__radio_wrapper">
						<span class="amoforms__form-setting__row__inner__name" style="margin-right: 23px;">Form View</span>
						<?php foreach ($form->get_form_views() as $view):?>
							<div class="amoforms_form_views_radio_btn_wrapper js-amoforms_form_views_radio_btn_wrapper">
								<input id="amoforms_<?php echo $view ?>" name="form[settings][view]" type="radio" value="<?php echo $view ?>" <?php echo $settings['view'] === $view ? 'checked="checked"' : '' ?>>
								<label for="amoforms_<?php echo $view ?>"><?php echo $view === 'classic' ? 'Classic' : 'Modal' ?></label>
							</div>
						<?php endforeach; ?>
					</div>
					<span class="amoforms__form-setting__row__inner__descr">Change form view from Classic to Modal to show your form in modal window.</span>
					<br><br>
					<?php
					foreach ($styles as $style){
						if($style['type'] == 'modal'){
							echo "<style id=\"amoforms__custom_style\" type=\"text/css\">";
							foreach ($style['elements'] as $rule_name => $rule) {
								echo '.amoforms .amoforms_' . $rule_name . ' {' . Helpers::prepare_styles($rule, FALSE) . '} ';
							}
							echo "</style>";
							$modal = $style;
						}
					}
					$modal_opts = [
							'text' => $form->get('modal')['text'],
							'style' => isset($modal) ? $modal['elements']['modal_button'] : '',
							'id' => isset($modal) ? $modal['id'] : '',
							'active' => $settings['view'] !== 'classic'
					];
					echo $this
							->engine()
							->loadTemplate('partials/modal_edit')
							->render($modal_opts);
					?>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="amoforms__section_middle amoforms__section_ga">
				<h2>Google Analytics</h2>
				<span class="amoforms__form-setting__row__inner__descr">
					Integration with Google Analytics allows you to monitor the effectiveness of channels where leads
					are received from, assess the effectiveness of marketing campaigns and make business decisions accordingly.
					<br>
					Once the lead is closed due to successful sale, amoCRM will communicate this result to Google Analytics.
				</span>
				<br>
				<span class="amoforms__form-setting__row__inner__name">Enable</span>
				<input type="checkbox" name="ga" value="1" <?php echo $ga_enabled ? 'checked' : '' ?> title="Google Analytics">
				<div style="margin-top: 15px">
					<span class="amoforms__form-setting__row__inner__descr">
						For detailed instructions see <a href="http://lp.amocrm.com/wordpress/Google_Analytics_for_amoForms.pdf" target="_blank">Google Analytics amoForms guide</a>.
					</span>
				</div>
			</div>

			<div class="amoforms__section_bottom">
				<h2>Confirmation</h2>
				<span class="amoforms__form-setting__row__inner__descr">
					After someone submits a form, you can control what is displayed.
				</span><br>
				<span class="amoforms__form-setting__row__inner__descr">
					By default, it is a message but you can send them to another WordPress Page or a custom URL.
				</span><br>
				<div class="amoforms__radio_wrapper">
					<span class="amoforms__form-setting__row__inner__name">Type</span>
					<?php foreach($form->get_confirmation_types() as $type) { ?>
					<input id="amocrm_<?php echo $type ?>" class="amoforms__form-setting__check" name="form[settings][confirmation][type]" type="radio" value="<?php echo $type ?>" <?php echo $settings['confirmation']['type'] === $type ? 'checked="checked"' : '' ?> title="text">
						<label for="amocrm_<?php echo $type ?>" class="amoforms__form-setting__row__inner__name"><?php echo I18n::get('confirmation_type_' . $type) ?></label>
					<?php } ?>
				</div>
				<div class="amoforms__text_editor" id="amoforms__text_editor"></div>
				<div class="amoforms_save_form_block">
					<div class="second_form_buttons">
						<input type="submit" value="save" class="save_button">
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
