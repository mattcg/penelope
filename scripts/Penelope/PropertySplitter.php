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

abstract class PropertySplitter extends Batchable {

	protected $object_schema;

	public $delimiter = ',', $map;

	public function __construct(ObjectSchema $object_schema, $property_name) {
		$this->object_schema = $object_schema;
		$this->property_name = $property_name;

		if ($object_schema->getProperty($property_name)->isMultiValue()) {
			throw new \InvalidArgumentException('Property "' . $property_name . '" is already a multivalue property.');
		}

		$this->map = function($value) {
			if ($value) {
				return trim($value);
			}
		};
	}

	public function process(Object $object) {
		$this->split($object);
	}

	public function split(Object $object) {
		$value = $object->getProperty($this->property_name)->getValue();

		if (!empty($value)) {
			$value_split = explode($this->delimiter, $value);
		}

		if (!empty($value_split) and $this->map) {
			$value_split = array_map($this->map, $value_split);
		}

		if (!empty($value_split)) {
			return array_filter($value_split);
		}
	}
}
