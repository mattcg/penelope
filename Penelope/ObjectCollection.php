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

	protected $schema, $client_objects = array();

	public function __construct(ObjectSchema $object_schema) {
		$this->schema = $object_schema;
	}

	public function getSchema() {
		return $this->schema;
	}

	public function rewind() {
		reset($this->client_objects);
	}

	public function current() {
		$current = current($this->client_objects);

		if ($current !== false) {
			return $this->schema->wrap($current['o']);
		}

		return false;
	}

	public function key() {
		return key($this->client_objects);
	}

	public function next() {
		$next = next($this->client_objects);

		if (false !== $next) {
			return $this->schema->wrap($next['o']);
		}

		return false;
	}

	public function valid() {
		return !is_null($this->key());
	}

	public function count() {
		return count($this->client_objects);
	}

	public function offsetExists($offset) {
		return isset($this->client_objects[$offset]);
	}

	public function offsetGet($offset) {
		return $this->schema->wrap($this->client_objects[$offset]['o']);
	}

	public function offsetSet($offset, $value) {
		throw new \Exception('Not implemented.');
	}

	public function offsetUnset($offset) {
		unset($this->client_objects[$offset]);
	}
}
