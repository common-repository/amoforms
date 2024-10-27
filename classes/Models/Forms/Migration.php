<?php
namespace Amoforms\Models\Forms;

use Amoforms\Models\amoCRM\Tables\amoUser;
use Amoforms\Models\Fields\Types\Base_Field;
use Amoforms\Models\Migration\Base_Migration;
use Amoforms\Models\Styles;
use Amoforms\Helpers;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Migration
 * @since 2.9.0
 * @method static $this instance
 * @package Amoforms\Models\Forms
 */
class Migration extends Base_Migration
{
	protected function migrate_from_2_8_0(array $params)
	{
		/* TODO: uncomment if it will be need
		$params['settings']['fields_size'] = Form::FIELD_SIZE_DEFAULT;
		$params['settings']['font'] = [
			'family' => Form::FORM_FONT_FAMILY_DEFAULT,
			'size'   => Form::FORM_FONT_SIZE_DEFAULT,
		];
		*/
		return $params;
	}

	protected function migrate_from_2_0_0(array $params)
	{
		// Set id to fields because they haven't them
		foreach ($params['fields'] as $index => $field) {
			$field['id'] = $index + 1;
			$params['fields'][$index] = $field;
		}
		return $params;
	}

	protected function migrate_from_2_21_1(array $params)
	{
		Manager::instance()->extend_table();
		$styles = new Styles\Collection();
		$styles->delete_all()
		       ->add(new Styles\Types\Form())
		       ->add(new Styles\Types\Submit())
		       ->add(new Styles\Types\Modal());
		$styles_manager = Styles\Manager::instance();
		foreach ($params['fields'] as $field) {
			if(isset($field['type'])){
				$styles->add($styles_manager->make_style($field['type']));
			}
		}
		$theme = $styles->to_array();

		foreach ($params['settings'] as $key => $setting) {
			switch ($key) {
				case 'borders_type':
					foreach ($theme as &$style){
						if(isset($style['elements']['field_element'])){
							$style['elements']['field_element']['border-radius'] = ($setting == 'rounded') ? '8px' : '0px';
						}
					}
					break;
				case 'form_paddings':
					switch ($setting) {
						case 'yes':
							$theme[0]['elements']['form_container']['padding'] = '1px 40px 40px 40px';
							$theme[0]['elements']['form_container']['border-width'] = '1px';
							$theme[0]['elements']['form_container']['border-color'] = 'rgba(0, 0, 0, 0.13)';
							$theme[0]['elements']['form_container']['border-style'] = 'solid';
							break;
						case 'no':
							$theme[0]['elements']['form_container']['padding'] = '1px 0px0 0';
							$theme[0]['elements']['form_container']['border-width'] = '0px';
							break;
						default:
							break;
					}
					break;
				case 'font':
					$theme[0]['elements']['form_container']['font-family'] = $setting['family'];
					$theme[0]['elements']['form_container']['font-size'] = $setting['size'];
					break;
				case 'background':
					switch ($setting['type']){
						case 'color':
							$theme[0]['elements']['form_container']['background-color'] = $setting['value'];
							$theme[0]['elements']['form_container']['background-image'] = '';
							foreach ($theme as &$style){
								$style['elements']['field_label']['color'] = Helpers::is_dark_color($setting['value']) ? '#ffffff' : '#313942';
								$style['elements']['field_label']['margin'] = '0 10px 5px 0';
							}
							break;
						case 'image':
							$theme[0]['elements']['form_container']['background-color'] = 'transparent';
							$theme[0]['elements']['form_container']['background-image'] = $setting['value'];
							break;
					}
					break;
				case 'submit':
				case 'modal':
					foreach ($theme as &$style){
						if($style['type'] == 'submit' || $style['type'] == 'modal'){
							$style['elements'][$key. '_button']['background-color'] = $setting['color'];
							switch ($setting['size']){
								case 1:
									$style['elements'][$key . '_button']['padding'] = '4px 20px';
									$style['elements'][$key . '_button']['font-size'] = '11px';
									break;
								case 2:
									$style['elements'][$key . '_button']['padding'] = '9px 39px';
									$style['elements'][$key . '_button']['font-size'] = '13px';
									break;
								case 3:
									$style['elements'][$key . '_button']['padding'] = '15px 55px';
									$style['elements'][$key . '_button']['font-size'] = '18px';
									break;
							}
							$style['elements'][$key . '_button']['width'] = 'auto';
							$style['elements'][$key . '_button']['height'] = 'auto';
						}
					}
					break;
				default:
					break;
			}
		}
		$styles->fill_by_params($theme);
		$params['styles'] = $styles->to_array();
		return $params;
	}

	protected function migrate_from_3_0_3(array $params)
	{
		foreach ($params['fields'] as $index => $field) {
			if(isset($field['options']['mask'])){
				if($field['options']['use_mask'] == Base_Field::MASK_SYSTEM){
					$field['options']['mask-system'] = $field['options']['mask'];
					unset($field['options']['mask']);
				}
				if($field['options']['use_mask'] == Base_Field::MASK_CUSTOM){
					$field['options']['mask-custom'] = $field['options']['mask'];
					unset($field['options']['mask']);
				}
				$params['fields'][$index] = $field;
			}
		}
		return $params;
	}

	protected function migrate_from_3_1_12(array $params){
		Manager::instance()->resize_table();
		return $params;
	}

	protected function migrate_from_3_1_18(array $params)
	{
		amoUser::instance()->resize_api_key_field();
		return $params;
	}
}
