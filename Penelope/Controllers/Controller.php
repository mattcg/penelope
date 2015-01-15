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

use Slim;

class Controller {

	protected $app;

	public function __construct(Slim\Slim $app) {
		$this->app = $app;
	}

	public function _e($message) {
		return $this->app->view->_e($message);
	}

	public function _m() {
		$view = $this->app->view();

		return call_user_func_array(array($view, '_m'), func_get_args());
	}
}
