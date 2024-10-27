<?php
namespace Amoforms\Models\Collection\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Base_Collection
 * @since 1.0.0
 * @package Amoforms\Models\Collection\Interfaces
 */
interface Base_Collection
{
	/**
	 * Add element
	 * @since 1.0.0
	 * @param $object
	 * @return $this
	 */
	public function add($object);

	/**
	 * Insert item to specified position in collection
	 * @param $new_item - object for insert
	 * @param int $pos - position in collection
	 * @return $this
	 */
	public function insert($new_item, $pos);

	/**
	 * Get first element
	 * @since 1.0.0
	 * @return $object|null
	 */
	public function first();

	/**
	 * Get item from collection by index
	 * @param int $index
	 * @return $object|FALSE
	 */
	public function get_by_index($index);

	/**
	 * Get item by id
	 * @param int $id
	 * @return $object|FALSE
	 */
	public function get_by_id($id);

	/**
	 * Count of elements
	 * @since 1.0.0
	 * @return int
	 */
	public function count();

	/**
	 * Get and remove first element
	 * @since 1.0.0
	 * @return $object|null
	 */
	public function remove_first();

	/**
	 * Delete item by id
	 * @since 2.9.5
	 * @param int $id
	 * @return $this
	 */
	public function delete_by_id($id);

	/**
	 * Delete all elements
	 * @since 1.0.0
	 * @return $this
	 */
	public function delete_all();

	/**
	 * Delete field by index
	 * @since 1.0.0
	 * @param int $index
	 * @return $this
	 */
	public function delete($index);

	/**
	 * Delete object form collection
	 * @since 2.9.5
	 * @param $object
	 * @return $this
	 */
	public function delete_object($object);
}
