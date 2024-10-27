<?php
namespace Amoforms\Libs\Http\Response;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

abstract class Base implements Interfaces\Base
{
	abstract public function send();
}
