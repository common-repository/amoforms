<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Line
 * @since 2.0.1
 * @package Amoforms\Models\Fields\Types
 */
class Line extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_LINE;
		$this->_name = I18n::get('Line');
		$this->_read_only = TRUE;
	}
}
