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

use Rhumsaa\Uuid\Uuid;
use Dflydev\ApacheMimeTypes;

use Karwana\Penelope\Exceptions;

class File extends Type {

	const PATH_KEY = 0, NAME_KEY = 1;

	public function __construct($value = null, array $options = null) {
		if (isset($value[static::PATH_KEY], $value[static::NAME_KEY]) and is_uploaded_file($value[static::PATH_KEY])) {
			$temp_path = $value[static::PATH_KEY];
			$perm_name = Uuid::uuid4(). '.' . static::getExtension($temp_path, $value[static::NAME_KEY]);

			try {
				$moved_uploaded_file = move_uploaded_file($temp_path, static::getSystemPath($perm_name));
			} catch (\Exception $e) {
				$moved_uploaded_file = false;
			}

			if (false === $moved_uploaded_file) {
				throw new Exceptions\TypeException('Unable to move uploaded file. Please check that the directory "' . static::getSystemPath() . '" is writable.');
			}

			$value[static::PATH_KEY] = $perm_name;
		}

		parent::__construct($value, $options);
	}

	private static function getMimeRepository() {
		static $repo;

		if (!isset($repo)) {
			$repo = new ApacheMimeTypes\PhpRepository();
		}

		return $repo;
	}

	public static function getExtension($file_path, $file_name = null) {
		if (!$file_name) {
			$file_name = basename($file_path);
		}

		$extension = pathinfo($file_name, PATHINFO_EXTENSION);
		if ($extension) {
			return strtolower($extension);
		}

		// Attempt to guess the extension using MIME magic.
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime_type = finfo_file($finfo, $file_path);
		finfo_close($finfo);
		if ($mime_type and $extensions = self::getMimeRepository()->findExtensions($mime_type)) {
			return $extensions[0];
		}

		return 'bin';
	}

	public static function getMimeType($file_path, $file_name = null) {
		$extension = static::getExtension($file_path, $file_name);
		if ($extension and $mime_type = self::getMimeRepository()->findType($extension)) {
			return $mime_type;
		}

		return 'application/octet-stream';
	}

	public static function getSystemPath($file_name = null) {

		// As a security measure, ensure that the file name is actually a file name.
		if ($file_name and basename($file_name) !== $file_name) {
			return false;
		}

		$path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'uploads';
		if ($file_name) {
			$path .=  DIRECTORY_SEPARATOR . $file_name;
		}

		return $path;
	}

	public static function unserialize($value) {
		if (static::isEmpty($value)) {
			return;
		}

		// Files are encoded as non-scalar values.
		// Because Neo4j doesn't support multidimensional arrays, they must be serialized and unserialized before storage.
		$value = json_decode($value, false, 512, JSON_BIGINT_AS_STRING);

		// Yes, json_encode returns null on error.
		if (is_null($value)) {
			throw new Exceptions\TypeException(json_last_error_msg(), json_last_error());
		}

		return $value;
	}

	public static function isValid($value, &$message = null) {
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
		if (!is_file(static::getSystemPath($path))) {
			$message = 'Unable to read file.';
			return false;
		}

		// As a security measure, only a bare filename is permitted.
		// This prevents for example "../../../etc/passwd" from being passed as a value.
		if (basename($path) !== $path) {
			$message = 'File name must be supplied without path.';
			return false;
		}

		return true;
	}
}
