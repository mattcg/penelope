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

namespace Karwana\Penelope\Types;

abstract class Type {

	private $value;

	public function __construct($value = null, array $options = null) {
		if (is_null($value)) {
			return;
		}

		if (static::validate($value)) {
			$this->value = $value;
		} else {
			throw new \InvalidArgumentException('Invalid type.');
		}
	}

	public function getValue() {
		return $this->value;
	}
}
