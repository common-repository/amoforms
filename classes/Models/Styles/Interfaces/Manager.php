<?php
namespace Amoforms\Models\Styles\Interfaces;

use Amoforms\Models;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

interface Manager {
	public function make_style($type);
}