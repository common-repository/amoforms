<?php
namespace Amoforms\Models\Forms\Interfaces;

use Amoforms\Interfaces\Array_Converting;
use Amoforms\Models\Collection\Interfaces\Base_Collection;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Collection
 * @since 1.0.0
 * @method Form|FALSE get_by_id($id)
 * @package Amoforms\Models\Forms\Interfaces
 */
interface Collection extends Base_Collection, Array_Converting
{

}
