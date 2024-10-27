<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Antispam
 * @since 2.21.0
 * @package Amoforms\Models\Fields\Types
 */
class Antispam extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_ANTISPAM;
		$this->_name = I18n::get('Antispam');
		$this->_spam = [
			'question' => 'What is fourteen minus 8?',
			'answer' => '6'
		];
	}
}
