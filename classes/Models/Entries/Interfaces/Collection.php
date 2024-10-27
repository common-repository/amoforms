<?php
namespace Amoforms\Models\Entries\Interfaces;

use Amoforms\Models\Collection\Interfaces\Base_Collection;
use Amoforms\Interfaces\Array_Converting;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Collection
 * @since 2.8.0
 * @method Entry|FALSE get_by_id($id)
 * @package Amoforms\Models\Entries\Interfaces
 */
interface Collection extends Base_Collection, Array_Converting
{

}
