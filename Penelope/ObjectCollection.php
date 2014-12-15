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

class ObjectCollection implements \Iterator, \Countable, \ArrayAccess {

	const PAGE_SIZE = 10;

	protected $order_by, $page = 1, $page_size = self::PAGE_SIZE;

	protected $properties;

	protected $schema, $position = 0, $resultset = array();

	public function __construct(ObjectSchema $object_schema, array $properties = null) {
		$this->schema = $object_schema;
		$this->properties = $properties;
	}

	public function getSchema() {
		return $this->schema;
	}

	public function setPageSize($page_size) {
		if (!is_int($page_size) or $page_size < 1) {
			throw new \InvalidArgumentException('Invalid page size "' . $page_size .'".');
		}

		$this->page_size = $page_size;
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
		return $this->schema->getOption('collection.order_by');
	}

	protected function query($id = null, $aggregate = null) {

		// If an ID is passed, get the relationships. Otherwise get nodes.
		if (is_null($id)) {
			$query_string = 'MATCH (o:' . $this->schema->getName() . ')';
		} else {
			$query_string = 'MATCH (n)-[o]->(' . $this->schema->getName() . ')';
		}

		$i = 0;
		$params = array();
		$query_parts = array();

		if (!is_null($id)) {
			$query_parts[] = 'id(n) = {node_id}';
			$params['node_id'] = $id;
		}

		foreach ((array) $this->properties as $name => $value) {
			if (!$this->schema->hasProperty($name)) {
				throw new \InvalidArgumentException('Unknown property "' . $name . '".');
			}

			$params['value_' . $i] = $value;
			$query_parts[] = 'ANY (m IN {value_' . $i . '} WHERE m IN o.' . $name . ')';
			$i++;
		}

		if (!empty($query_parts)) {
			$query_string .=  ' WHERE ' . join(' AND ', $query_parts);
		}

		if ($aggregate) {
			$query_string .= ' RETURN ' . $aggregate . '(o)';
		} else {
			$query_string .= ' RETURN (o)';

			// Order by only makes sense when not using aggregate.
			$order_by = $this->getOrderBy();
			if ($order_by) {
				$query_string .= ' ORDER BY o.' . join(', o.', (array) $order_by);
			}

			$limit = $this->page_size;

			if ($this->page > 1) {
				$skip = $this->page * $this->page_size;
			} else {
				$skip = 0;
			}

			// Aggregate results would be unexpected when using limit.
			$query_string .= ' SKIP ' . $skip . ' LIMIT ' . $limit;
		}

		$client = $this->schema->getClient();

		// The standard REST API methods for getting nodes by a label don't support paging.
		// Neither do they support querying by multiple properties (only by a single property).
		// This is why we use Cypher instead.
		$query = new Neo4j\Cypher\Query($client, $query_string, $params);

		return $query->getResultset();
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
		return $this->schema->wrap($this->resultset[$offset]['o']);
	}

	public function offsetSet($offset, $value) {
		throw new \BadMethodCallException('You cannot modify an object collection.');
	}

	public function offsetUnset($offset) {
		throw new \BadMethodCallException('You cannot modify an object collection.');
	}
}