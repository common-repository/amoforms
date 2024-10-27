<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Multiselect
 * @since 2.0.1
 * @package Amoforms\Models\Fields\Types
 */
class Multiselect extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_MULTISELECT;
		$this->_name = I18n::get('Multiselect');
		$this->_is_enum = TRUE;
	}
}
