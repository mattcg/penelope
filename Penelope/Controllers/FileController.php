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

namespace Karwana\Penelope\Controllers;

use Karwana\Penelope\Types\File;
use Karwana\Penelope\Exceptions;

class FileController extends Controller {

	public function read($system_path) {
		if (!is_readable($system_path)) {
			$this->render404(new Exceptions\Exception('The requested file is unreadable or does not exist.'));
			return;
		}

		$response = $this->app->response;
		$response->headers->set('Content-Type', File::getMimeType($system_path));
		$response->headers->set('Content-Length', filesize($system_path));

		readfile($system_path);
	}
}
