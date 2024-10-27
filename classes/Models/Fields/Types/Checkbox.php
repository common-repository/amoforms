<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Checkbox
 * @since 2.0.1
 * @package Amoforms\Models\Fields\Types
 */
class Checkbox extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_CHECKBOX;
		$this->_name = I18n::get('Checkbox');
	}
}
