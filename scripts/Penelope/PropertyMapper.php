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

	public function split(Object $object, $delimiter = ',') {
		$value = $object->getProperty($this->property_name)->getValue();

		if (!empty($value) and is_string($value)) {
			$value_split = explode($delimiter, $value);
		}

		if (!empty($value_split)) {
			$value_split = array_map(function($value) {
				return trim($value);
			}, $value_split);
		}

		if (!empty($value_split)) {
			$value_split = array_filter($value_split);
		}

		return array($this->property_name => $value_split);
	}
}
