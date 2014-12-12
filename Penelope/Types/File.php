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

use Rhumsaa\Uuid\Uuid;

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

	public static function store($temp_path, $original_name) {
		$perm_name = Uuid::uuid4(). '.' . static::getExtension($temp_path, $original_name);
		$perm_path = static::getSystemPath($perm_name);

		// If the file is NOT an uploaded file, copy instead of moving.
		// This is done in case file is being restored from backup which we don't want to mutate.
		if (!is_uploaded_file($temp_path)) {
			$stored = copy($temp_path, $perm_path);

		// Even though PHP clears temporary uploaded files automatically, it's better to use `rename` instead of `copy`, for efficiency.
		} else {
			$stored = rename($temp_path, $perm_path);
		}

		if (false === $stored) {
			throw new \RuntimeException('Unable to store file. Is "' . static::getSystemDirectory() . '" writable?');
		}

		if (false === chmod($perm_path, 0644)) {
			throw new \RuntimeException('Unable change file permissions. Is "' . $perm_path . '" writable?');
		}

		return $perm_name;
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
