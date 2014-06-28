<?php

/**
 * LICENSE: This source code is subject to the license that is available
 * in the LICENSE file distributed along with this package.
 *
 * @package    Penelope
 * @author     Matthew Caruana Galizia <mcg@karwana.com>
 * @copyright  Karwana Ltd
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

namespace Karwana\Penelope;

class Property {

	protected $value, $schema, $type_class;

	public function __construct(PropertySchema $property_schema) {
		$this->schema = $property_schema;
		$this->type_class = __NAMESPACE__ . '\\Types\\' . ucfirst($property_schema->getType());
	}

	public function getSchema() {
		return $this->schema;
	}

	public function getName() {
		return $this->schema->getName();
	}

	public function hasValue() {
		return !is_null($this->value);
	}

	public function setValue($value) {
		$type_class = $this->type_class;
		$options = $this->schema->getOptions();

		$value = $this->filterValue($value);
		if (is_null($value)) {
			$this->value = null;
			return;
		}

		if ($this->schema->isMultiValue()) {
			$this->value = array_map(function($value) use ($type_class, $options) {
				return new $type_class($value, $options);
			}, $value);
		} else {
			$this->value = new $type_class($value, $options);
		}
	}

	public function getValue() {
		if (!$this->hasValue()) {
			return;
		}

		if (!$this->schema->isMultiValue()) {
			return $this->value->getValue();
		}

		return array_map(function($value) {
			return $value->getValue();
		}, $this->value);
	}

	public function filterValue($value) {
		$type_class = $this->type_class;

		if (!$this->schema->isMultiValue()) {
			if (!$type_class::isEmpty($value)) {
				return $value;
			}
		}

		$value = array_filter((array) $value, function($value) use ($type_class) {
			return !$type_class::isEmpty($value);
		});

		if (!empty($value)) {
			return $value;
		}
	}
}