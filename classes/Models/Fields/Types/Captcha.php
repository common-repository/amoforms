<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Captcha
 * @since 2.0.1
 * @package Amoforms\Models\Fields\Types
 */
class Captcha extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_CAPTCHA;
		$this->_name = I18n::get('Captcha');
		$this->_options[self::OPTION_USE_CAPTCHA_NTP] = self::CAPTCHA_NTP_DEFAULT;
	}
}
