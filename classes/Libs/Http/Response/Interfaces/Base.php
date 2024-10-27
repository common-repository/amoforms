<?php
namespace Amoforms\Libs\Http\Response\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

interface Base
{
	/**
	 * Send response
	 */
	public function send();
}
