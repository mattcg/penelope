<?php

/**
 * LICENSE: This source code is subject to the license that is available
 * in the LICENSE file distributed along with this package.
 *
 * @package    Penelope
 * @author     Matthew Caruana Galizia <mcg@karwana.com>
 * @copyright  Karwana Ltd
 * @since      File available since Release 1.0.0
 */

namespace Karwana\Penelope;

use Everyman\Neo4j;

abstract class ObjectCollection implements \Iterator, \Countable, \ArrayAccess {

	protected $order_by, $page = 1, $page_size;

	protected $properties;

	protected $object_schema, $position = 0, $resultset = array();

	public function __construct(ObjectSchema $object_schema) {
		$this->object_schema = $object_schema;
	}

	public function getSchema() {
		return $this->object_schema;
	}

	public function setProperties(array $properties) {
		foreach (array_keys($properties) as $name) {
			if (!$this->object_schema->hasProperty($name)) {
				throw new \InvalidArgumentException('Unknown property "' . $name . '".');
			}
		}

		$this->properties = $properties;
	}

	public function clearProperties() {
		$this->properties = null;
	}

	public function setPageSize($page_size) {
		if (!is_int($page_size) or $page_size < 1) {
			throw new \InvalidArgumentException('Invalid page size "' . $page_size .'".');
		}

		$this->page_size = $page_size;
	}

	public function clearPageSize() {
		$this->page_size = null;
	}

	public function getPageSize() {
		return $this->page_size;
	}

	public function setPage($page) {
		if (!is_int($page) or $page < 1) {
			throw new \InvalidArgumentException('Invalid page "' . $page .'".');
		}

		$this->page = $page;
	}

	public function clearPage() {
		$this->page = null;
	}

	public function getPage() {
		return $this->page;
	}

	public function setOrderBy($order_by) {
		$this->order_by = $order_by;
	}

	public function getOrderBy() {
		if ($this->order_by) {
			return $this->order_by;
		}

		// Return the default.
		return $this->object_schema->getOption('collection.order_by');
	}

	public function getTotalCount() {
		return (int) $this->query('count')[0][0];
	}

	public function fetch() {
		$this->resultset = $this->query();
	}

	protected function query($aggregate = null) {
		$query_params = array();
		$query_string = $this->getQueryString($aggregate, $query_params);

		// Aggregate results would be unexpected when using limit.
		// Return all objects if page and page size are not set.
		if (!$aggregate and $this->page_size and $this->page) {
			$limit = $this->page_size;
			$query_string .= ' SKIP ' . (($this->page * $limit) - $limit) . ' LIMIT ' . $limit;
		}

		// The standard REST API methods for getting nodes by a label don't support paging.
		// Neither do they support querying by multiple properties (only by a single property).
		// This is why we use Cypher instead.
		$query = new Neo4j\Cypher\Query($this->object_schema->getClient(), $query_string, $query_params);

		return $query->getResultSet();
	}

	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		return $this[$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		++$this->position;
	}

	public function valid() {
		return isset($this->resultset[$this->position]);
	}

	public function count() {
		return count($this->resultset);
	}

	public function offsetExists($offset) {
		return isset($this->resultset[$offset]);
	}

	public function offsetGet($offset) {
		return $this->object_schema->wrap($this->resultset[$offset]['o']);
	}

	public function offsetSet($offset, $value) {
		throw new \BadMethodCallException('You cannot modify an object collection.');
	}

	public function offsetUnset($offset) {
		throw new \BadMethodCallException('You cannot modify an object collection.');
	}
}
