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

class ObjectCollection implements \Iterator, \Countable, \ArrayAccess {

	protected $schema, $results = array();

	public function __construct(ObjectSchema $object_schema) {
		$this->schema = $object_schema;
	}

	public function getSchema() {
		return $this->schema;
	}

	public function rewind() {
		reset($this->results);
	}

	public function current() {
		$current = current($this->results);

		if ($current !== false) {
			return $this->schema->wrap($current);
		}

		return false;
	}

	public function key() {
		return key($this->results);
	}

	public function next() {
		$next = next($this->results);

		if (false !== $next) {
			return $this->schema->wrap($next);
		}

		return false;
	}

	public function valid() {
		return !is_null($this->key());
	}

	public function count() {
		return count($this->results);
	}

	public function offsetExists($offset) {
		return isset($this->results[$offset]);
	}

	public function offsetGet($offset) {
		return $this->schema->wrap($this->results[$offset]);
	}

	public function offsetSet($offset, $value) {
		throw new \Exception('Not implemented.');
	}

	public function offsetUnset($offset) {
		unset($this->results[$offset]);
	}
}
