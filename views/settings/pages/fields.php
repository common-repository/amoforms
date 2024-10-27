<?php
/** @var Amoforms\Views\Interfaces\Base $this */
use Amoforms\Helpers;
use Amoforms\Libs\Captcha\Captcha;
use Amoforms\Libs\FileSystem\Collections\Backgrounds;
use Amoforms\Libs\FileSystem\Models\Interfaces\ImageWithThumb;
use Amoforms\Models\Fields\Types\Base_Field;
use Amoforms\Models\Forms\Form;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/** @var Form $form */
$form = $this->get('form');
$fields = $form->get_fields();
$styles = $form->get_styles();
$form_style = $form->get_form_style()['elements']['form_container'];
$has_captcha_field = $form->has_captcha();
$can_use_captcha = Captcha::instance()->can_use_captcha();

wp_register_script('Sortable', plugins_url('/amoforms/js/vendor/Sortable/Sortable.js'));
wp_register_script('mustache', plugins_url('/amoforms/js/vendor/mustache/mustache.js'));
wp_register_script('Colorpicker', plugins_url('/amoforms/js/vendor/colorpicker/colorpicker.js'));
wp_register_script('pickmeup', plugins_url('/amoforms/js/vendor/pickmeup/js/jquery.pickmeup.js'), ['jquery']);
wp_register_script('amoforms_form', plugins_url('/amoforms/js/core/form.js'), array('jquery', 'backbone'));
wp_register_script('amoforms_confirm', plugins_url('/amoforms/js/core/confirm.js'), array('jquery', 'backbone'));
wp_register_script('amoforms_fn', plugins_url('/amoforms/js/core/fn.js'), array('jquery'));
wp_register_script('amoforms_views', plugins_url('/amoforms/js/core/views.js'), array('jquery'));
wp_register_script('amoforms_field', plugins_url('/amoforms/js/fields/field.js'), array('jquery', 'backbone'));
wp_register_script('amoforms_fields_settings', plugins_url('/amoforms/js/fields/settings.js'), array('jquery', 'backbone'));
wp_register_script('amoforms_submit', plugins_url('/amoforms/js/fields/submit.js'), array('jquery', 'backbone'));
wp_register_script('amoforms_storage', plugins_url('/amoforms/js/fields/storage.js'), array('jquery', 'backbone'));
wp_register_script('dropzone', plugins_url('/amoforms/js/vendor/dropzone/dropzone.js'));
wp_register_script('tooltipster', plugins_url('/amoforms/js/vendor/tooltipster/jquery.tooltipster.min.js'), array('jquery'));
wp_register_script('jquery-mask', plugins_url('/amoforms/js/vendor/jquery-mask-plugin/jquery.mask.min.js'), ['jquery']);
wp_register_script('cssjson', plugins_url('/amoforms/js/vendor/cssjson/cssjson.js'));

wp_enqueue_script(
	'amoforms_fields',
	plugins_url('/amoforms/js/fields/index.js'),
	array(
		'jquery',
		'backbone',
		'Sortable',
		'dropzone',
		'mustache',
		'Colorpicker',
		'pickmeup',
		'amoforms_form',
		'amoforms_confirm',
		'amoforms_fn',
		'amoforms_views',
		'amoforms_field',
		'amoforms_fields_settings',
		'amoforms_submit',
		'amoforms_storage',
		'tooltipster',
		'jquery-mask',
		'cssjson',
	)
);

$form_settings = $form->get_settings();
?>
<link rel="stylesheet" type="text/css" href="<?php echo  AMOFORMS_CSS_URL ?>/tooltipster/tooltipster.css" />
<link rel="stylesheet" type="text/css" href="<?php echo  AMOFORMS_CSS_URL ?>/tooltipster/themes/tooltipster-shadow.css" />

<style id="amoforms__custom_style" type="text/css">
	<?php
	$css = '';
	foreach ($styles as $style){
		foreach ($style['elements'] as $rule_name => $rule) {
			switch ($rule_name) {
				case 'form_container':
					$css .= '.amoforms .amoforms_theme-container' . ' {' . Helpers::prepare_styles($rule, FALSE) . '} ';
					break;
				case 'form_row':
					$css .= '.amoforms .amoforms__fields__container' . ' {' . Helpers::prepare_styles($rule, FALSE) . '} ';
					break;
				case 'rating_star_hover':
					$css .= '.amoforms #style-' . $style['id'] . ' .amoforms_rating_star:hover {' . Helpers::prepare_styles($rule, FALSE) . '} ';
					break;
				case 'rating_star_select':
					$css .= '.amoforms #style-' . $style['id'] . ' .amoforms_rating_star.pressed {' . Helpers::prepare_styles($rule, FALSE) . '} ';
					break;
				default:
					$css .= '.amoforms #style-' . $style['id'] . ' .amoforms_' . $rule_name . ' {' . Helpers::prepare_styles($rule, FALSE) . '} ';
					break;
			}
		}
	}
	echo $css;
	?>
</style>

<div class="amoforms_fields_settings">
	<div class="clearfix">
		<div class="amoforms__form__actions--clear">
		<div class="amoforms__fields__expander" id="amoforms_sidebar">
			<?php foreach (Base_Field::get_categories() as $cat) { ?>
			<div class="amoforms__fields__expander__item expanded">
				<div class="amoforms__fields__expander__item__head">
					<p class="amoforms__fields__expander__item__head__text"><?php echo  $cat['name'] ?></p>
				</div>

				<div class="amoforms__fields__expander__item__content">
					<div class="amoforms__fields__expander__item__content__inner">
						<p>Click or Drag to add fields</p>
						<div class="amoforms__fields__expander__item__content__fields clearfix">
							<?php foreach (Base_Field::get_fields_types_names($cat['name']) as $type => $type_name) { ?>
								<div>
									<div
										class="amoforms__fields__expander__item__content__fields__field"
										data-type="<?php echo $type ?>"
										<?php
										if ($type === Base_Field::TYPE_CAPTCHA) {
											if ($has_captcha_field || !$can_use_captcha) { ?>
												data-active="false"
											<?php }
											if (!$can_use_captcha) { ?>
												title="To use captcha, you need access to openssl_encrypt or mcrypt_encrypt function"
											<?php } ?>
										<?php } ?>
										>
										<span class="<?php echo $type ?>"></span>
										<p><?php echo $type_name ?></p>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>

			<div class="amoforms__fields__expander__item">
				<div class="amoforms__fields__expander__item__head">
					<p class="amoforms__fields__expander__item__head__text">Shortcode</p>
				</div>
				<div class="amoforms__fields__expander__item__content">
					<div class="amoforms__fields__expander__item__content__inner">
						<?php if (!$form->get('email')['to']) { ?>
							<p>Set up <a href="<?php echo \Amoforms\Router::instance()->get_settings_page_url('edit_email', $form->id()) ?>">email settings</a> to get shortcode</p>
						<?php } else { ?>
							<p>Add forms to your Posts or Pages by locating the <b>amoForms</b> button in the area above your post/page editor.</p>
							<p>You may also manually insert the shortcode into a post/page.</p>
							<div style="margin-top:10px">
								<div style="float:left;padding-top:12px;margin-right:10px">Shortcode</div>
								<div style="overflow:hidden"><input type="text" class="amoforms__text-input js-amoforms-shortcode" value='[amoforms id="<?php echo $form->id() ?>"]' readonly="readonly"></div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>

			</div>
			<div class="amoforms__form__actions">
				<button class="amoforms__form__actions__btn amoforms__form__actions__btn-user_guide" id="user_guide">Visual Editor User Guide</button>
				<button class="amoforms__form__actions__btn amoforms__form__actions__btn-duplicate" id="duplicate_form">Duplicate form</button>
				<button class="amoforms__form__actions__btn amoforms__form__actions__btn-reset" id="reset_form">Reset styles</button>
				<button class="amoforms__form__actions__btn amoforms__form__actions__btn-delete" id="delete_form">Delete form</button>
			</div>
		</div>
		<div class="amoforms__fields__main amoforms__fields name-inside-1 name-before-1" id="amoforms_fields" data-form-id="<?php echo $form->id()?>">
			<div class="amoforms_modal fade" id="amoforms-settings">

			</div>
			<div class="amoforms__fields__settings">
				<button class="amoforms__fields__settings__btn" data-type="form-css"><i></i><b>Form Styling</b></button><!--
				--><button class="amoforms__fields__settings__btn" data-type="themes"><i></i><b>Design themes</b></button><!--
				--><button class="amoforms__fields__settings__btn" data-type="name-position" data-value="<?php echo $form_settings['names_position']?>"><i></i><b>Name Position</b></button><!--
				--><button class="amoforms__fields__settings__btn" data-value="<?php echo $form_settings['borders_type']?>" data-type="field-form"><i></i><b>Field Form</b></button><!--
				--><button class="amoforms__fields__settings__btn" data-value="<?php echo $form_settings['form_paddings']?>" data-type="form-paddings"><i></i><b>Border</b></button><!--
				--><button class="amoforms__fields__settings__btn" data-type="font" data-font-size="<?php echo  $form_style['font-size'] ?>" data-font-family="<?php echo  $form_style['font-family'] ?>"><i></i><b>Font</b></button><!--
				--><nobr><button class="amoforms__fields__settings__btn" data-type="background-color"><b>Background</b><span class="color" style="background-color: <?php echo  $form_style['background-color'] ?>;"></span></button><!--
				--><button class="amoforms__fields__settings__btn" data-type="background-image" data-value="<?php echo  $form_style['background-image'] ?>"><i></i></button></nobr>
			</div>
			<div class="amoforms_theme-container amoforms__fields__editor">
				<div class="js-fields-sortable">
				<?php foreach ($fields as $field) {
					$mask = isset($field['options'][Base_Field::OPTION_USE_MASK]) ? (int)$field['options'][Base_Field::OPTION_USE_MASK] : FALSE;
					$masks = [];
					foreach(Base_Field::get_masks_list() as $key => $value){
						$masks[] = [
								'name' => $key,
								'mask' => $value,
								'text' => ucfirst($key).' '.$value,
								'active' => (isset($field['options']['mask-system']) && $key == $field['options']['mask-system'])
						];
					}
					$options = [
						'field_id' => $field['id'],
						'field' => $field,
						'edit_mode' => $this->get('edit_mode'),
						'use_mask' => $mask !== FALSE && $mask !== Base_Field::MASK_DISABLED,
						'is_'.$field['type'] => TRUE,
						'is_pos_after' => $field['description_position'] === Base_Field::DESCRIPTION_POS_AFTER,
						'captcha_ntp' => !empty($field['options'][Base_Field::OPTION_USE_CAPTCHA_NTP]),
						'is_layout_inline' => $field['layout'] === Base_Field::LAYOUT_HORIZONTAL,
						'name_position_' . $form_settings['names_position'] => TRUE,
						'grid' => [
							'left' => $field['grid'] == Base_Field::FIELD_GRID_HALF_LEFT,
							'right' => $field['grid'] == Base_Field::FIELD_GRID_HALF_RIGHT,
							'full' => $field['grid'] == Base_Field::FIELD_GRID_FULL,
						],
						'system_masks' => !empty($masks) ? $masks : FALSE,

						'consts' => [
							'layout_inline' => Base_Field::LAYOUT_HORIZONTAL,
							'layout_vertical' => Base_Field::LAYOUT_VERTICAL,
							'captcha_ntp_disabled' => Base_Field::CAPTCHA_NTP_DISABLED,
							'captcha_ntp_enabled' => Base_Field::CAPTCHA_NTP_ENABLED,
							'pos_before' => Base_Field::DESCRIPTION_POS_BEFORE,
							'pos_after' => Base_Field::DESCRIPTION_POS_AFTER
						]
					];
					foreach ($styles as $style){
						if($style['object_id'] == $field['id']){
							$options['field_style'] = $style;
						} elseif ($style['type'] == $field['type'] && (bool)$style['is_type_style']){
							$options['field_style'] = $style;
						} elseif($style['type'] == 'submit'){
							$submit = $style;
						}
					}
					if($mask !== FALSE){
						$options['is_system_mask'] = $mask == Base_Field::MASK_SYSTEM;
						$options['is_custom_mask'] = $mask == Base_Field::MASK_CUSTOM;
						if($mask == Base_Field::MASK_SYSTEM){
							if(!empty($options['field']['options']['mask-system'])) {
								switch ($options['field']['options']['mask-system']) {
									case Base_Field::MASK_TYPE_EURO:
										$options['currency_mask'] = '€';
										break;
									case Base_Field::MASK_TYPE_DOLLAR:
										$options['currency_mask'] = '$';
										break;
									case Base_Field::MASK_TYPE_DATE:
									case Base_Field::MASK_TYPE_PHONE:
										$options['field']['options']['mask-system'] = Base_Field::get_masks_list($options['field']['options']['mask-system']);
										break;
								}
							}
						}
					}
					echo $this
						->engine()
						->loadTemplate('mustache/field_in_edit')
						->render($options);
					unset($options);
				}
				?>
				</div>
				<?php
				$submit_opts = [
						'text' => $form->get('submit')['text'],
						'style' => isset($submit) ? $submit['elements']['submit_button'] : '',
						'id' => isset($submit) ? $submit['id'] : ''
				];
				echo $this
						->engine()
						->loadTemplate('partials/submit_edit')
						->render($submit_opts);
				?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	<?php

		/** @var Backgrounds $backgrounds_objects */
        $backgrounds_objects = $this->get('backgrounds');
        $backgrounds = [];
        /** @var ImageWithThumb $background */
        foreach($backgrounds_objects as $background) {
        	$thumb = '';
        	if ($background->get_thumb()) {
        		$backgrounds[] = [
					'url' => $background->get('url'),
					'thumb' => [
						'url' => $background->get_thumb()->get('url')
					],
					'img_basename' => $background->get('basename')
				];
        	}
        }
	 ?>
	(function (global, $) {
		global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
			consts: {
				layout_inline: '<?php echo Base_Field::LAYOUT_HORIZONTAL?>',
				layout_vertical: '<?php echo Base_Field::LAYOUT_VERTICAL?>',
				pos_before: '<?php echo Base_Field::DESCRIPTION_POS_BEFORE?>',
				pos_after: '<?php echo Base_Field::DESCRIPTION_POS_AFTER?>'
			},
			images: {
				url: "<?php echo AMOFORMS_IMAGES_URL . '/'?>",
				backgrounds: <?php echo json_encode($backgrounds) ?>
			},
			style: {
				max_id: <?php echo $form->get_styles_max_id() ?>,
				css: '<?php echo $css ?>',
				form: <?php echo json_encode($form->get_form_style()) ?>
			}
		});
	}(window, jQuery))
</script>

<?php foreach (glob(AMOFORMS_VIEWS_DIR.'/partials/*.mustache') as $file) { ?>
<script type="text/mustache" data-partial="1" data-name="<?php echo basename($file, '.mustache')?>"><?php echo file_get_contents($file)?></script>
<?php } ?>

<?php foreach (glob(AMOFORMS_VIEWS_DIR.'/mustache/*.mustache') as $file) { ?>
<script type="text/mustache" data-name="<?php echo basename($file, '.mustache')?>"><?php echo file_get_contents($file)?></script>
<?php } ?>
