<?php

namespace Amoforms\Libs\Hooks\PreviewPage;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Traits\Singleton;

/**
 * Class ManagePage
 * @since 2.20.8
 * @method static $this instance
 * @package Amoforms\Libs\Hooks\PreviewPage
 */
class ManagePage
{
	use Singleton;

	public function register() {
		add_action( 'init', [$this, 'check_preview_page']);
	}

	/**
	 * Check preview page
	 * @since 2.20.8
	 * @return int
	 */
	public function check_preview_page() {
		$page_id = (int)get_option(AMOFORMS_OPTION_PREVIEW_PAGE_ID);
		if(empty($page_id) || !get_page_by_title(AMOFORMS_PREVIEW_PAGE_NAME)) {
			$page_id = $this->create_preview_page();
		}
		return $page_id;
	}

	/**
	 * Create preview page
	 * @since 2.20.8
	 * @return int
	 */
	protected function create_preview_page() {

		$preview_page = get_page_by_title(AMOFORMS_PREVIEW_PAGE_NAME);
		if(!$preview_page){
			$preview_post = [
				'post_title' => AMOFORMS_PREVIEW_PAGE_NAME,
				'post_content' => 'This is a preview of how this form will appear on your website',
				'post_status' => 'draft',
				'post_type' => 'page'
			];
			$page_id = (int)wp_insert_post($preview_post);
		} else{
			$page_id = $preview_page->ID;
		}
		update_option(AMOFORMS_OPTION_PREVIEW_PAGE_ID, $page_id);
		return $page_id;
	}

}
