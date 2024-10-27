<?php
namespace Amoforms\Libs\FileSystem\Models\Interfaces;

use Amoforms\Libs\FileSystem\Collections\Interfaces\Files;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

interface File {
	/**
	 * unlink file and unset $this
	 */
	public function rm();

	/**
	 * @param string $new_path
	 *
	 * @return static new instance of self
	 */
	public function copy_to($new_path);

	/**
	 * @param string|NULL $key
	 *
	 * @return array|string|int
	 */
	public function get($key = NULL);

	/**
	 * Notify observers about actions
	 *
	 * @param string $action
	 * @param array $params
	 */
	public function notify($action, $params = []);

	/**
	 * Attach observer
	 *
	 * @param Files $observer
	 *
	 * @return $this
	 */
	public function attach(Files $observer);

	/**
	 * Detach observer
	 *
	 * @param Files $observer
	 *
	 * @return $this
	 */
	public function detach(Files $observer);
}
