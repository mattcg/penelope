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

namespace Karwana\Penelope\Controllers;

use Rhumsaa\Uuid\Uuid;

use Karwana\Penelope\Types\File;

class UploadController extends FileController {

	public function read($file_name) {
		$system_path = File::getSystemPath($file_name);

		// Null is returned if the given file name is invalid.
		if (!$system_path) {
			$this->render404();
			return;
		}

		parent::read($system_path);
	}

	public static function move($temp_path, $original_name) {
		$perm_name = Uuid::uuid4(). '.' . File::getExtension($temp_path, $original_name);

		try {
			$moved_uploaded_file = move_uploaded_file($temp_path, File::getSystemPath($perm_name));
		} catch (\Exception $e) {
			$moved_uploaded_file = false;
		}

		if (false === $moved_uploaded_file) {
			throw new \RuntimeException('Unable to move uploaded file. Please check that the directory "' . File::getSystemDirectory() . '" is writable.');
		}

		return $perm_name;
	}
}
