<?php
/** @var Amoforms\Views\Interfaces\Base $this */
defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Helpers;
use Amoforms\Libs\Captcha\Captcha;
use Amoforms\Models\Forms\Form;

wp_register_script('pickmeup', plugins_url('/amoforms/js/vendor/pickmeup/js/jquery.pickmeup.js'), ['jquery']);
wp_register_script('sifter', plugins_url('/amoforms/js/vendor/sifter/sifter.js'));
wp_register_script('dropzone', plugins_url('/amoforms/js/vendor/dropzone/dropzone.js'));
wp_register_script('serializeobject', plugins_url('/amoforms/js/vendor/serializeobject/serializeobject.js'), ['jquery']);
wp_register_script('microplugin', plugins_url('/amoforms/js/vendor/microplugin/src/microplugin.js'));
wp_register_script('selectize', plugins_url('/amoforms/js/vendor/selectize/dist/js/selectize.js'), ['microplugin', 'sifter']);
wp_register_script('amoforms_fn', plugins_url('/amoforms/js/core/fn.js'), array('jquery'));
wp_register_script('grecaptcha', plugins_url('/amoforms/js/form/grecaptcha.js'), array('jquery'));
wp_register_script('recaptcha', 'https://www.google.com/recaptcha/api.js?onload=amoformsCaptchaOnloadCallback&render=explicit', array('grecaptcha'));
wp_register_script('jquery-mask', plugins_url('/amoforms/js/vendor/jquery-mask-plugin/jquery.mask.min.js'), ['jquery']);
wp_register_script('stretchy', plugins_url('/amoforms/js/vendor/stretchy/stretchy.js'), array('jquery'));
wp_register_script('tooltipster', plugins_url('/amoforms/js/vendor/tooltipster/jquery.tooltipster.min.js'), array('jquery'));
wp_register_script('autonumeric', plugins_url('/amoforms/js/vendor/autoNumeric/autoNumeric-min.js'), array('jquery'));
wp_register_script('jquery-modal', plugins_url('/amoforms/js/vendor/jquery-modal/jquery-modal.js'), ['jquery']);

$form_dependencies = array(
	'jquery',
	'underscore',
	'pickmeup',
	'serializeobject',
	'dropzone',
	'selectize',
	'amoforms_fn',
	'grecaptcha',
	'recaptcha',
	'stretchy',
	'jquery-mask',
	'tooltipster',
	'autonumeric',
	'jquery-modal',
);

$ga = \Amoforms\Libs\Analytics\Analytics::instance();
$ga_enabled = $ga->is_enabled();
if ($ga_enabled) {
	wp_register_script('sourcebuster', plugins_url('/amoforms/js/vendor/sourcebuster/sourcebuster.min.js'));
	wp_register_script('amo_analytics', plugins_url('/amoforms/js/form/analytics.js'));
	$form_dependencies[] = 'sourcebuster';
	$form_dependencies[] = 'amo_analytics';
}
wp_register_script('erroneous', plugins_url('/amoforms/js/vendor/erroneous/erroneous.js'));
wp_enqueue_script('amoforms_errors', plugins_url('/amoforms/js/core/errors.js'), ['erroneous']);

wp_enqueue_script(
	'amoforms_form',
	plugins_url('/amoforms/js/form/form.js'),
	$form_dependencies
);


$form_style = [];
/** @var Form $form */
$is_preview = $this->get('is_preview');
$form = $this->get('form');
$form_settings = $form->get_settings();
$form_style = $form->get_form_style()['elements']['form_container'];
$submit_opts = $form->get('submit');
$modal_btn_opts = $form->get('modal');
$is_modal = $form_settings['view'] == 'modal' && !$is_preview;
?>

<link rel="stylesheet" href="<?php echo AMOFORMS_CSS_URL . ($is_preview ? '/app.css' : '/form.css') ?>">
<link rel="stylesheet" type="text/css" href="<?php echo AMOFORMS_CSS_URL ?>/tooltipster/tooltipster.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo AMOFORMS_CSS_URL ?>/tooltipster/themes/tooltipster-shadow.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo AMOFORMS_CSS_URL ?>/jquery-modal/jquery-modal.css"/>

<style id="amoforms_css_theme">
	#amoforms_form_<?php echo $form->id()?> {
		margin-bottom: <?php echo $form_settings['view'] == 'modal' ? '0' : '30px';?> !important;
	}

	#amoforms_form_<?php echo $form->id()?>.amoforms .amoforms__fields__view {
	<?php if (empty($form_style['background-image'])) { ?> background-color: <?php echo $form_style['background-color'] ?> !important;
	<?php } else { ?> background-image: url(<?php echo $form_style['background-image']?>) !important;
	<?php } ?>
		background-size: cover;
		background-position: center center;
	}

	#amoforms_form_<?php echo $form->id()?>.amoforms .amoforms__fields__row__inner__name,
	#amoforms_form_<?php echo $form->id()?>.amoforms .amoforms__fields__row__inner__control,
	#amoforms_form_<?php echo $form->id()?>.amoforms .amoforms__fields__row__inner__control input,
	#amoforms_form_<?php echo $form->id()?>.amoforms .amoforms__fields__row__submit {
		font-family: "<?php echo  $form_style['font-family'] ?>" !important;
	}


	#amoforms_form_<?php echo $form->id()?>.amoforms .selectize-input.focus + .selectize-dropdown {
		border-top: 1px solid rgb(212, 213, 216) !important;
		overflow: hidden;
	}

	#amoforms_form_<?php echo $form->id()?> #amoforms_blocked_message {
		text-align: center !important;
		font-size: 26px !important;
	}

	.amoforms_form_preview_form_wrapper {
	<?php if ($form_style['background-color'] == 'transparent') { ?> background: none !important;
	<?php } ?>
	}

	.amoforms.amoforms-preview #amoforms_form_<?php echo $form->id()?> .amoforms__fields__main {
	<?php if ($form_settings['form_paddings'] == 'no' || $form_style['background-color'] == 'transparent') { ?> padding-top: 10px !important;
	<?php } ?> <?php if ($form_style['background-color'] == 'transparent') { ?> background: none !important;
	<?php } ?>
	}

	#amoforms_form_<?php echo $form->id()?>.amoforms .amoforms__fields__view {
	<?php if ($form_settings['form_paddings'] == 'no') { ?> padding: 1px 0 0 0 !important;
	<?php } ?>
	}

	<?php if ($form_settings['form_paddings'] === Form::FORM_PADDINGS_N) { ?>
	.amoform_submit_form .amoforms__fields__row:first-of-type {
		padding-top: 1px !important;
	}

	<?php } ?>

</style>
<style type="text/css">
	<?php
	foreach ($form->get_styles() as $style){
		foreach ($style['elements'] as $rule_name => $rule) {
			switch ($rule_name) {
				case 'form_container':
					echo '#amoforms_form_'. $form->id() .'.amoforms .amoforms_theme-container' . ' {' . Helpers::prepare_styles($rule, FALSE) . '} ';
					break;
				case 'form_row':
					echo '#amoforms_form_'. $form->id() .'.amoforms .amoforms__fields__container' . ' {' . Helpers::prepare_styles($rule, FALSE) . '} ';
					break;
				case 'radio_item_selected':
					echo '#amoforms_form_'. $form->id() .'.amoforms #style-' . $style['id'] . ' .amoforms__radio input:checked + b:before {' . Helpers::prepare_styles($rule, FALSE) . '} ';
					break;
				case 'rating_star_hover':
					echo '#amoforms_form_'. $form->id() .'.amoforms #style-' . $style['id'] . ' .amoforms_rating_star:hover {' . Helpers::prepare_styles($rule, FALSE) . '} ';
					break;
				case 'rating_star_select':
					echo '#amoforms_form_'. $form->id() .'.amoforms #style-' . $style['id'] . ' .amoforms_rating_star.pressed {' . Helpers::prepare_styles($rule, FALSE) . '} ';
					break;
				case 'modal_button':
					echo '#amoforms_modal_'. $form->id() .'.amoforms_modal_button {' . Helpers::prepare_styles($rule, FALSE) . '} ';
					break;
				default:
					echo '#amoforms_form_'. $form->id() .'.amoforms #style-' . $style['id'] . ' .amoforms_' . $rule_name . ' {' . Helpers::prepare_styles($rule, FALSE) . '} ';
					break;
			}
		}
	}
	?>
</style>
<?php if ($is_modal): ?>
<div class="amoforms_modal fade" id="amoforms-<?php echo $form->id() ?>">
	<?php endif; ?>
	<div class="amoforms" id="amoforms_form_<?php echo $form->id() ?>">
		<div
			class="<?php if ($is_preview) { ?>amoforms__fields__main<?php } ?> amoforms__fields name-inside-1 name-before-1"
			id="amoforms_fields" data-form-id="<?php echo $form->id() ?>">
			<a class='close' data-dismiss='modal'></a>

			<div class="amoforms_theme-container amoforms__fields__view">
				<form action="<?php echo $this->get('submit_url') ?>" method="post" id="submit_form"
					  class="amoform_submit_form" enctype="multipart/form-data">
					<input type="hidden" name="form[id]" value="<?php echo $form->id() ?>">
					<input type="hidden" name="action" value="amoforms_submit">
					<input type="hidden" name="analytics" class="amoforms__analytics_field">
					<?php if ($form->is_blocked()) { ?>
						<h3 id="amoforms_blocked_message">Form is temporarily unavailable.</h3>
					<?php } else {
						include __DIR__ . '/fields.php';
					}
					?>
				</form>
			</div>
		</div>
	</div>
	<?php if ($is_modal): ?>
</div>
	<button class="amoforms__fields__row__modal amoforms_modal_button amoforms__form_submit_btn_text" id="amoforms_modal_<?php echo $form->id() ?>" data-modal="amoforms-<?php echo $form->id() ?>">
				<?php echo $modal_btn_opts['text'] ?>
			</span></button>
<?php endif; ?>
<script>
	(function (global) {
		global.AMOFORMS = global.AMOFORMS || {};
	}(window));
</script>
<?php if ($form->has_captcha()) { ?>
	<script>
		(function (global) {
			global.AMOFORMS.captcha = global.AMOFORMS.captcha || {};
			global.AMOFORMS.captcha.site_key = global.AMOFORMS.captcha.site_key || '<?php echo Captcha::G_CAPTCHA_SITE_KEY ?>';
			global.AMOFORMS.captcha.stokens = global.AMOFORMS.captcha.stokens || [];
			global.AMOFORMS.captcha.stokens.push('<?php echo Captcha::instance()->get_captcha_token($form->is_need_captcha_ntp()); ?>');
		}(window));
	</script>
<?php } ?>

<style class="amoforms_custom_css_style">
	<?php echo $form_settings['css'] ?>
</style>

<div class="amoforms_custom_js_wrapper">
	<script class="amoforms_custom_js_script">
		var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
		var pluginurl = '<?php echo plugins_url().'/amoforms/js' ?>';
		(function () {
			try {
				<?php echo $form_settings['js'] ?>
			} catch (e) {
				console.error('amoForms custom JS error: ', e);
			}
		})();
	</script>
</div>
