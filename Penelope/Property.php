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

class Property {

	protected $value, $schema;

	public function __construct(PropertySchema $property_schema) {
		$this->schema = $property_schema;
	}

	public function getSchema() {
		return $this->schema;
	}

	public function getName() {
		return $this->schema->getName();
	}

	public function getLabel() {
		return $this->schema->getLabel();
	}

	public function hasValue() {
		return !is_null($this->value);
	}

	public function clearValue() {
		$this->value = null;
	}

	public function setValue($value) {
		$value = $this->filterValue($value);

		if (is_null($value)) {
			$this->clearValue();
			return;
		}

		$options = $this->schema->getOptions();
		$type_class = $this->schema->getTypeClass();

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

	public function setSerializedValue($value) {
		if (is_null($value)) {
			$this->clearValue();
			return;
		}

		$type_class = $this->schema->getTypeClass();

		if ($this->schema->isMultiValue()) {
			$value = array_map(function($value) use ($type_class) {
				return $type_class::unserialize($value);
			}, (array) $value);
		} else {
			$value = $type_class::unserialize($value);
		}

		$this->setValue($value);
	}

	public function getSerializedValue() {
		if (!$this->hasValue()) {
			return;
		}

		if (!$this->schema->isMultiValue()) {
			return $this->value->getSerializedValue();
		}

		return array_map(function($value) {
			return $value->getSerializedValue();
		}, $this->value);
	}

	public function filterValue($value) {
		$type_class = $this->schema->getTypeClass();

		if ($type_class::isEmpty($value)) {
			return;
		}

		// All further checks are for multivalue properties.
		if (!$this->schema->isMultiValue()) {
			return $value;
		}

		$value = array_filter($value, function($value) use ($type_class) {
			return !$type_class::isEmpty($value);
		});

		// Remove duplicates from the array.
		// It doesn't make sense for any kind of multivalue property to have duplicate values.
		$value = array_unique($value, SORT_REGULAR);

		if (!empty($value)) {
			return $value;
		}
	}
}
