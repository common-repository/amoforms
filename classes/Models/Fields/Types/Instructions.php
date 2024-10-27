<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Instructions
 * @since 2.0.1
 * @package Amoforms\Models\Fields\Types
 */
class Instructions extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_INSTRUCTIONS;
		$this->_name = I18n::get('Instructions');
	}
}
