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

namespace Karwana\Penelope\Exceptions;

class UploadException extends Exception {

	public function __construct($code) {
		parent::__construct(static::codeToMessage($code), $code);
	}

	private static function codeToMessage($code) {
		switch ($code) {
		case UPLOAD_ERR_INI_SIZE:
			return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
		case UPLOAD_ERR_FORM_SIZE:
			return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
		case UPLOAD_ERR_PARTIAL:
			return 'The uploaded file was only partially uploaded.';
		case UPLOAD_ERR_NO_FILE:
			return 'No file was uploaded.';
		case UPLOAD_ERR_NO_TMP_DIR:
			return 'Missing a temporary folder.';
		case UPLOAD_ERR_CANT_WRITE:
			return 'Failed to write file to disk.';
		case UPLOAD_ERR_EXTENSION:
			return 'File upload stopped by extension.';
		default:
			return 'Unknown upload error: ' . $code . '.';
		}
	}
}