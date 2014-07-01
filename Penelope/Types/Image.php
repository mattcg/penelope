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

class Image extends File {

	public static function isValid($value, &$message = null) {
		if (!parent::isValid($value, $message)) {
			return false;
		}

		$path = $value[static::PATH_KEY];
		$name = $value[static::NAME_KEY];

		$valid_mimes = array('image/gif', 'image/jpeg', 'image/png', 'image/bmp');
		if (!in_array(static::getMimeType(static::getSystemPath($path), $name), $valid_mimes)) {
			$message = 'Unsupported image type.';
			return false;
		}

		return true;
	}
}