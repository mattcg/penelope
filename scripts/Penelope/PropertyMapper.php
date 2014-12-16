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

namespace Karwana\Penelope\Scripts;

use Karwana\Penelope\Object;
use Karwana\Penelope\ObjectSchema;
use Karwana\Penelope\ObjectCollection;

use Everyman\Neo4j;

require_once __DIR__ . '/Batchable.php';

abstract class PropertyMapper extends Batchable {

	protected $object_schema, $mapper;

	public function __construct(ObjectSchema $object_schema, $property_name, \Closure $mapper) {
		$this->object_schema = $object_schema;
		$this->property_name = $property_name;
		$this->mapper = $mapper;
	}

	public function process(Object $object) {
		$this->map($object);
	}

	public function rename(Object $object, $new_name) {
		$value = $object->getProperty($this->property_name)->getValue();

		return array($new_name => $value);
	}

	public function replace(Object $object, $pattern, $replacement) {
		$value = $object->getProperty($this->property_name)->getValue();

		if (!is_array($value)) {
			$value = preg_replace($pattern, $replacement, $value);
		} else {
			$value = array_map(function($value) use ($pattern, $replacement) {
				return preg_replace($pattern, $replacement, $value);
			}, $value);
		}

		return array($this->property_name => $value);
	}

	public function split(Object $object, $delimiter = ',') {
		$value = $object->getProperty($this->property_name)->getValue();

		// If it's already an array, join and re-split.
		if (is_array($value)) {
			$value = implode($delimiter, $value);
		}

		if (is_string($value)) {
			$value = explode($delimiter, $value);
		}

		if (!empty($value)) {
			$value = array_map(function($value) {
				return trim($value);
			}, $value);
		}

		if (!empty($value)) {
			$value = array_filter($value);
		}

		return array($this->property_name => $value);
	}
}
