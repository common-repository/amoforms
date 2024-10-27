<?php
namespace Amoforms\Models\Collection;

use Amoforms\Exceptions\Argument;
use Amoforms\Models;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Base_Collection
 * @since 1.0.0
 * @package Amoforms\Models\Collection
 */
abstract class Base_Collection implements Interfaces\Base_Collection, \Iterator
{
	protected $_items_class;

	/** @var \SplObjectStorage */
	protected $_data;

	public function __construct()
	{
		$this->_data = $this->make_new_storage();
	}

	protected function make_new_storage()
	{
		return new \SplObjectStorage();
	}

	protected function check_item_class($item)
	{
		if (!is_null($this->_items_class)) {
			$class = $this->_items_class;
			if (!is_object($item)) {
				throw new Argument("Item is not an Object and it must be instance of class '$class'. Item = " . var_export($item, TRUE));
			}
			if (!($item instanceof $class)) {
				throw new Argument("Item (" . get_class($item) . ") is not instance of class '$class'");
			}
		}
	}

	public function add($object)
	{
		$this->_data->attach($object);
		return $this;
	}

	public function insert($new_item, $pos)
	{
		$pos = abs((int)$pos);
		$count = $this->count();
		if (!$count || $pos >= $count) {
			return $this->add($new_item);
		}

		$new_storage = $this->make_new_storage();
		foreach ($this->_data as $index => $item) {
			if ($index === $pos) {
				$new_storage->attach($new_item);
			}
			$new_storage->attach($item);
		}
		$this->_data = $new_storage;
		return $this;
	}

	public function first()
	{
		$this->_data->rewind();
		return $this->_data->current();
	}

	public function get_by_index($index)
	{
		$index = (int)$index;
		foreach ($this->_data as $key => $item) {
			if ($key === $index) {
				return $item;
			}
		}
		return FALSE;
	}

	public function get_by_id($id)
	{
		$id = (int)$id;
		/** @var Models\Interfaces\Base $model */
		foreach ($this->_data as $model) {
			if ($model->id() === $id) {
				return $model;
			}
		}
		return FALSE;
	}

	public function count()
	{
		return $this->_data->count();
	}

	public function remove_first()
	{
		$first = $this->first();
		$this->_data->detach($first);
		return $first;
	}

	public function delete_by_id($id)
	{
		if ($item = $this->get_by_id($id)) {
			$this->delete_object($item);
		}
		return $this;
	}

	public function delete_all()
	{
		$this->_data->removeAll($this->_data);
		return $this;
	}

	public function delete($index)
	{
		$index = (int)$index;
		foreach ($this->_data as $key => $field) {
			if ($key === $index) {
				$this->_data->detach($field);
				break;
			}
		}
		return $this;
	}

	public function delete_object($object)
	{
		$this->_data->detach($object);
		return $this;
	}

	// Iterator interface
	public function current() {
		return $this->_data->current();
	}

	public function next() {
		$this->_data->next();
	}

	public function key() {
		return $this->_data->key();
	}

	public function valid() {
		return $this->_data->valid();
	}

	public function rewind() {
		$this->_data->rewind();
	}
}
