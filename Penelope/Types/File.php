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

namespace Karwana\Penelope\Types;

use Karwana\Penelope\Exceptions;
use Karwana\Mime\Mime;

class File extends Type {

	const PATH_KEY = 0, NAME_KEY = 1;

	protected static $system_directory;

	public static function getExtension($file_path, $reference_name = null) {
		return Mime::guessExtension($file_path, $reference_name);
	}

	public static function getMimeType($file_path, $reference_name = null) {
		return Mime::guessType($file_path, $reference_name);
	}

	public static function getSystemDirectory() {
		if (!isset(static::$system_directory)) {
			static::setSystemDirectory(getcwd() . DIRECTORY_SEPARATOR . 'files');
		}

		return static::$system_directory;
	}

	public static function setSystemDirectory($directory) {
		static::$system_directory = $directory;
	}

	public static function getSystemPath($file_name) {

		// As a security measure, ensure that the file name is actually a file name.
		// This prevents for example "../../../etc/passwd" from being passed as a value.
		if (basename($file_name) === $file_name) {
			return static::getSystemDirectory() . DIRECTORY_SEPARATOR . $file_name;
		}
	}

	public static function unserialize($value) {
		if (static::isEmpty($value)) {
			return;
		}

		// Files are encoded as non-scalar values.
		// Because Neo4j doesn't support multidimensional arrays, they must be serialized and unserialized before storage.
		$value = json_decode($value);

		// Yes, json_encode returns null on error.
		if (is_null($value)) {
			throw new Exceptions\TypeException(json_last_error_msg(), json_last_error());
		}

		return $value;
	}

	public static function isValid($value, array $options = null, &$message = null) {
		if (static::isEmpty($value)) {
			return true;
		}

		if (!is_array($value)) {
			$message = 'Unexpected type received.';
			return false;
		}

		if (empty($value[static::PATH_KEY]) or !is_string($value[static::PATH_KEY])) {
			$message = 'Unable to read file path.';
			return false;
		}

		if (empty($value[static::NAME_KEY]) or !is_string($value[static::NAME_KEY])) {
			$message = 'Unable to read file name.';
			return false;
		}

		$path = $value[static::PATH_KEY];

		if (is_uploaded_file($path)) {
			$message = 'The file must be moved to a permanent location before it can be used.';
			return false;
		}

		$system_path = static::getSystemPath($path);

		// As a security measure, only a bare filename is permitted by getSystemPath.
		if (!$path) {
			$message = 'The file name must be supplied without path.';
			return false;
		}

		if (!is_file($system_path)) {
			$message = 'Unable to read file at "' . $system_path . '".';
			return false;
		}

		return true;
	}
}
