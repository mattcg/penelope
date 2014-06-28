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
		$this->value = $this->filterValue($value);
	}

	public function getValue() {
		if ($this->hasValue()) {
			return $this->value;
		}
	}
}
