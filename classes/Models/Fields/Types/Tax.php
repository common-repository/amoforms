<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Tax
 * @since 2.21.0
 * @package Amoforms\Models\Fields\Types
 */
class Tax extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_TAX;
		$this->_name = I18n::get('Tax');
		$this->_default_value = '0%';
	}
}
