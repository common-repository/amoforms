<?php
namespace Amoforms\Models\Fields\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Manager
 * @since   1.0.0
 * @package Amoforms\Models\Fields\Interfaces
 */
interface Manager
{
	public function make_field($type);
}
