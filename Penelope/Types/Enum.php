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

class Enum extends Type {

	public static function isValid($value, array $options = null, &$message = null) {
		if (empty($options['allowed']) or !is_array($options['allowed'])) {
			throw new \RuntimeException('Enum requires the "allowed" option be set to a non-empty array.');
		}

		if (static::isEmpty($value)) {
			return true;
		}

		if (!in_array($value, $options['allowed'], true)) {
			$message = 'Value "' . $value . '" is not allowed.';
			return false;
		}

		return true;
	}
}
