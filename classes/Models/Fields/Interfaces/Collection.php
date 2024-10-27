<?php
namespace Amoforms\Models\Fields\Interfaces;

use Amoforms\Interfaces\Array_Converting;
use Amoforms\Models\Collection\Interfaces\Base_Collection;
use Amoforms\Models\Fields\Types\Interfaces\Base_Field;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Collection
 * @since 1.0.0
 * @method Base_Field|FALSE get_by_id($id)
 * @package Amoforms\Models\Fields\Interfaces
 */
interface Collection extends Base_Collection, Array_Converting
{
	public function fill_by_params(array $fields_params);
}
