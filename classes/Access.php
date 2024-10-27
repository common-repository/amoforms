<?php
namespace Amoforms;

use Amoforms\Traits\Singleton;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Access
 * @since 1.0.0
 * @method static $this instance
 * @package Amoforms
 */
class Access
{
	use Singleton;

	/**
	 * Check capability
	 * @since 2.8.0
	 * @param string|TRUE $capability
	 * @return bool
	 */
	public function check($capability)
	{
		return ($capability === TRUE) ? TRUE : current_user_can($capability);
	}

	/**
	 * Die with html error block
	 * @since 1.0.0
	 */
	public function die_error()
	{
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
}
