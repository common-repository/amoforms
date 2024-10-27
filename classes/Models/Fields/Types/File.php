<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class File
 * @since 2.0.1
 * @package Amoforms\Models\Fields\Types
 */
class File extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_FILE;
		$this->_name = I18n::get('File');
		$this->_label = 'To attach files drag & drop here or browse file';
		$this->_size = 1;
		$this->_extensions = '';
	}
}
