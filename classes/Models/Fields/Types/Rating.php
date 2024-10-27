<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Rating
 * @since 2.21.0
 * @package Amoforms\Models\Fields\Types
 */
class Rating extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_RATING;
		$this->_name = I18n::get('Rating');
	}
}
