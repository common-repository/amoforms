<?php
namespace Amoforms\Models\Forms;

use Amoforms\Exceptions\Argument;
use Amoforms\Models\Collection\Base_Collection;
use Amoforms\Models\Forms\Interfaces\Form;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Forms Collection
 * @since 1.0.0
 * @method Form first
 * @package Amoforms\Models\Forms
 */
class Collection extends Base_Collection implements Interfaces\Collection
{
	/**
	 * Add Form to collection
	 * @since 1.0.0
	 * @param Form $form
	 * @return $this
	 * @throws Argument
	 */
	public function add($form)
	{
		if (!($form instanceof Form)) {
			throw new Argument('$form is not instance of Form');
		}
		return parent::add($form);
	}

	/**
	 * Get content as array of arrays
	 * @since 1.0.0
	 * @return array
	 */
	public function to_array()
	{
		$array = [];
		/** @var Form $form */
		foreach ($this->_data as $form) {
			$array[] = $form->get_settings();
		}
		return $array;
	}
}
