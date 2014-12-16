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
		return (int) $this->getResultSet('count')[0][0];
	}

	public function fetch() {
		$this->resultset = $this->getResultSet();
	}

	protected function formatQuery($match, array $where_parts = array(), $aggregate = null) {
		$query_params = array();
		$query_string = $match;

		$i = 0;
		foreach ((array) $this->properties as $name => $value) {
			$query_params['value_' . $i] = $value;
			$where_parts[] = 'ANY (m IN {value_' . $i . '} WHERE m IN o.' . $name . ')';
			$i++;
		}

		if (!empty($where_parts)) {
			$query_string .= ' WHERE ' . join(' AND ', $where_parts);
		}

		if ($aggregate) {
			$query_string .= ' RETURN ' . $aggregate . '(o)';
		} else {
			$query_string .= ' RETURN (o)';
		}

		// Order by only makes sense when not using aggregate.
		if (!$aggregate and ($order_by = $this->getOrderBy())) {
			$query_string .= ' ORDER BY o.' . join(', o.', (array) $order_by);
		}

		// Aggregate results would be unexpected when using limit.
		// Return all objects if page and page size are not set.
		if (!$aggregate and $this->page_size and $this->page) {
			if ($this->page > 1) {
				$query_string .= ' SKIP ' . (($this->page * $this->page_size) - $this->page_size);
			}

			$query_string .= ' LIMIT ' . $this->page_size;
		}

		$client = $this->object_schema->getClient();

		// The standard REST API methods for getting nodes by a label don't support paging.
		// Neither do they support querying by multiple properties (only by a single property).
		// This is why we use Cypher instead.
		return new Neo4j\Cypher\Query($client, $query_string, $query_params);
	}

	protected function getResultSet($aggregate = null) {
		return $this->getQuery($aggregate)->getResultSet();
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
