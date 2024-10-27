<?php

namespace Amoforms\Libs\Hooks\PreviewPage;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Traits\Singleton;
use Amoforms\Router;
use Amoforms\Controllers;

/**
 * Class PutForm
 * @since 2.20.8
 * @method static $this instance
 * @package Amoforms\Libs\Hooks\PreviewPage
 */
class PutForm
{
	use Singleton;

	/** @var \Amoforms\Controllers\Form $_form_controller */
	protected $_form_controller;

	public function register() {
		if($this->is_preview_page() && $this->get_form_id()){
			add_filter( 'the_content', [$this, 'render_form']);
		}
		return $this;
	}

	/**
	 * Check is preview page
	 * @since 2.20.8
	 * @return bool
	 */
	protected function is_preview_page() {
		return $this->get_preview_page() === (int)Router::instance()->get_page_id();

	}

	/**
	 * Render Form
	 * @since 2.20.8
	 * @return string
	 */
	public function render_form() {
		$result = $this->get_form_controller()->render_form_by_id($this->get_form_id());
		return $result;
	}

	/**
	 * Return form id from query string
	 * @since 2.20.8
	 * @return int|bool
	 */
	protected function get_form_id() {
		$form_id = Router::instance()->get_form_id();
		return !empty($form_id)? (int)$form_id : FALSE;
	}

	/**
	 * Get preview page from settings
	 * @since 2.20.8
	 * @return int
	 */
	protected function get_preview_page() {
		$page_id = (int)get_option(AMOFORMS_OPTION_PREVIEW_PAGE_ID);
		if(empty($page_id)){
			$page_id = 0;
		}
		return $page_id;
	}

	/**
	 * Get Form Controller
	 * @since @since 2.20.8
	 * @return Controllers\Form
	 */
	protected function get_form_controller()
	{
		if (!$this->_form_controller) {
			$this->_form_controller = new Controllers\Form();
		}
		return $this->_form_controller;
	}

}
