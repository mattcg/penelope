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

class PropertySchema extends OptionContainer {

	private $name, $type, $is_multi_value;

	public function __construct($name, $type, $is_multi_value = false, array $options = null) {
		parent::__construct($options);

		if (empty($name)) {
			throw new \InvalidArgumentException('Property name can not be empty.');
		}

		if (!in_array($type, array('text', 'date', 'datetime', 'file', 'image', 'country', 'url', 'enum'))) {
			throw new \InvalidArgumentException('Unknown type "' . $type . '".');
		}

		$this->name = $name;
		$this->type = $type;
		$this->is_multi_value = (bool) $is_multi_value;
	}

	public function getName() {
		return $this->name;
	}

	public function getLabel() {
		if ($label = $this->getOption('label')) {
			return $label;
		}

		return $this->getName();
	}

	public function isMultiValue() {
		return $this->is_multi_value;
	}

	public function getType() {
		return $this->type;
	}

	public function getTypeClass() {
		return __NAMESPACE__ . '\\Types\\' . ucfirst($this->type);
	}
}
