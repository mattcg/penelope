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

class Datetime extends Type {

	public function __construct($value = null, array $options = null) {
		if (is_string($value)) {
			$value = strtotime($value);
		}

		parent::__construct($value, $options);
	}

	public static function validate($value) {
		return is_int($value);
	}
}
