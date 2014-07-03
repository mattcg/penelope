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

	public function hasValue() {
		return !is_null($this->value);
	}

	public function clearValue() {
		$this->setValue(null);
	}

	public function setValue($value) {
		$value = $this->filterValue($value);

		if (is_null($value)) {
			$this->value = null;
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
			$this->setValue($value);
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

		$type_class = $this->schema->getTypeClass();

		if (!$this->schema->isMultiValue()) {
			return $type_class::serialize($this->getValue());
		}

		return array_map(function($value) use ($type_class) {
			return $type_class::serialize($value);
		}, $this->getValue());
	}

	public function filterValue($value) {
		$type_class = $this->schema->getTypeClass();

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
