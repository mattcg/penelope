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

use Karwana\Penelope\Exceptions;

class Datetime extends Type {

	public static function unserialize($value) {
		if (static::isEmpty($value)) {
			return;
		}

		if (!is_string($value)) {
			return $value;
		}

		$value = strtotime($value);
		if (false === $value) {
			throw new Exceptions\TypeException('Unable to convert "' . $value . '" to a valid time.');
		}

		return $value;
	}

	public static function isValid($value, &$message = null) {
		if (static::isEmpty($value)) {
			return true;
		}

		if (!is_int($value)) {
			$message = 'Invalid type received.';
			return false;
		}

		return true;
	}
}
