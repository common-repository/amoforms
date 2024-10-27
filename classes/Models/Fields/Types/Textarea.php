<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Textarea
 * @since 1.0.0
 * @package Amoforms\Models\Fields\Types
 */
class Textarea extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_TEXTAREA;
		$this->_name = I18n::get('Text area');
		$this->_placeholder = I18n::get('What do you think?');
	}
}
