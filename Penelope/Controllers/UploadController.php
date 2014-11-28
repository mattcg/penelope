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

use Karwana\Penelope\Types\File;

class UploadController extends FileController {

	public function read($file_name) {
		$system_path = File::getSystemPath($file_name);

		// Null is returned if the given file name is invalid.
		// TODO: `File::getSystemPath` should throw an exception and it should be handled here.
		if (!$system_path) {
			$this->app->notFound();
			return;
		}

		parent::read($system_path);
	}

	public static function move($temp_path, $original_name) {

		// Unless there's a security vulnerability being exploited, this method would only ever be used to move an uploaded file. Otherwise `File::store` would be used explicitly.
		if (!is_uploaded_file($temp_path)) {
			throw new \RuntimeException('Only uploaded files may be moved.');
		}

		return File::store($temp_path, $original_name);
	}
}
