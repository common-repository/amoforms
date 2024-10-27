<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Heading
 * @since 1.0.0
 * @package Amoforms\Models\Fields\Types
 */
class Heading extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_HEADING;
		$this->_name = I18n::get('Heading');
		$this->_default_value = I18n::get('Call Back Request');
		$this->_placeholder = I18n::get('Call Back Request');
		$this->_read_only = TRUE;
		$this->_is_resizable = FALSE;
	}
}
