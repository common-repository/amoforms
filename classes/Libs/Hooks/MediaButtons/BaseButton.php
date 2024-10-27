<?php
namespace Amoforms\Libs\Hooks\MediaButtons;

use Amoforms\Traits\Singleton;
use Amoforms\Views\PostEditor;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class BaseButton
 * @since 2.11.8
 * @package Amoforms\Libs\Hooks\MediaButtons
 */
abstract class BaseButton
{
	use Singleton;

	/**
	 * Media buttons context
	 * @var string
	 */
	protected $_context = 'content';

	/**
	 * @var \Amoforms\Views\Interfaces\Base
	 */
	protected $_view;


	protected function __construct()
	{
		$this->_view = new PostEditor();
	}

	/**
	 * Register button
	 * @since 2.11.8
	 * @return self
	 */
	abstract public function register();
}
