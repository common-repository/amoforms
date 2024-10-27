<?php
/** @var Amoforms\Views\Interfaces\Base $this */
defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Helpers;
use Amoforms\Models\Fields\Types\Base_Field;

/** @var \Amoforms\Models\Forms\Form $form */
	$styles = $form->get_styles();

	foreach ($form->get_fields() as $field) {
		$mask = isset($field['options'][Base_Field::OPTION_USE_MASK]) ? (int)$field['options'][Base_Field::OPTION_USE_MASK] : FALSE;
		$options = [
			'field_id' => $field['id'],
			'field' => $field,
			'unique_captcha_id' => uniqid(),
			'field_name_position' => $form_settings['names_position'],
			'is_'.$field['type'] => TRUE,
			'use_mask' => $mask !== FALSE && $mask !== Base_Field::MASK_DISABLED,
			'is_system_mask' => $mask == Base_Field::MASK_SYSTEM,
			'is_custom_mask' => $mask == Base_Field::MASK_CUSTOM,
			'is_pos_after' => $field['description_position'] === Base_Field::DESCRIPTION_POS_AFTER,
			'is_layout_inline' => $field['layout'] === Base_Field::LAYOUT_HORIZONTAL,
			'name_position_' . $form_settings['names_position'] => TRUE,
			'grid' => [
					'left' => $field['grid'] == Base_Field::FIELD_GRID_HALF_LEFT,
					'right' => $field['grid'] == Base_Field::FIELD_GRID_HALF_RIGHT,
					'full' => $field['grid'] == Base_Field::FIELD_GRID_FULL,
			],

			'consts' => [
				'layout_inline' => Base_Field::LAYOUT_HORIZONTAL,
				'layout_vertical' => Base_Field::LAYOUT_VERTICAL,
				'pos_before' => Base_Field::DESCRIPTION_POS_BEFORE,
				'pos_after' => Base_Field::DESCRIPTION_POS_AFTER
			]
		];
		foreach ($styles as $style){
			if($style['object_id'] == $field['id']){
				$options['field_style'] = $style;
			} elseif($style['type'] == $field['type'] && (bool)$style['is_type_style']){
				$options['field_style'] = $style;
			} elseif($style['type'] == 'submit'){
				$submit = $style;
			}
		}
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

		echo $this
			->engine()
			->loadTemplate('mustache/field_in_view')
			->render($options);
		unset($options);
	}
?>
<div <?php echo (isset($submit)) ? 'id="style-' . $submit['id'] . '"' : ''?> class="amoforms__fields__row full amoforms__fields__row-submit">
	<div class="amoforms__fields__row-view">
		<button
			type="submit"
			class="amoforms__fields__row__submit amoforms_submit_button"
			>
			<span class="amoforms__form_submit_btn_text">
				<?php echo $submit_opts['text'] ?>
			</span>
		</button>
	</div>
</div>


