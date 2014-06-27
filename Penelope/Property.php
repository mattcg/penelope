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
	protected $values = array(), $schema;

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
		return !empty($this->values);		
	}

	public function setValue($value) {
		$type_class = 'Karwana\\Penelope\\Types\\' . ucfirst($this->schema->getType());
		$options = $this->schema->getOptions();

		if ($this->schema->isMultiValue()) {
			$this->values = array_map(function($value) use ($type_class, $options) {
				return new $type_class($value, $options);
			}, (array) $value);

		} else {
			$this->values[0] = new $type_class($value, $options);
		}
	}

	public function getValue() {
		if ($this->schema->isMultiValue()) {
			return array_map(function($value) {
				return $value->getValue();
			}, $this->values);
		}

		if (!empty($this->values)) {
			return $this->values[0]->getValue();
		}
	}
}
