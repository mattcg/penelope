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

class TransientProperty extends Property {
	private $exception;

	public function setError(\Exception $e) {
		$this->exception = $e;
	}

	public function getError() {
		return $this->exception;
	}

	public function setValue($value) {
		if ($this->schema->isMultiValue()) {
			$values = (array) $value;
			$this->values = array(); // Reset.
			foreach ($values as $value) {
				$this->values[] = $value;
			}
		} else {
			$this->values[0] = $value;
		}
	}

	public function getValue() {
		if ($this->schema->isMultiValue()) {
			return $this->values;
		}

		if (!empty($this->values)) {
			return $this->values[0];
		}
	}
}
