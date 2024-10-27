<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Zippost
 * @since 2.21.0
 * @package Amoforms\Models\Fields\Types
 */
class Zippost extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_ZIPPOST;
		$this->_name = I18n::get('Zip/Post');
		$this->_placeholder = I18n::get('Zip/Post code');
	}
}
