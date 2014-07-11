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

use Slim;

use Karwana\Penelope\Exceptions;
use Karwana\Penelope\DefaultTheme;

abstract class Controller {

	protected $app;

	public function __construct(Slim\Slim $app) {
		$this->app = $app;
	}

	// Only Penelope application-generated exceptions are permitted.
	public function render404(Exceptions\Exception $e) {
		$this->app->render('error/404', array('title' => 'Not found', 'error' => $e), 404);
	}

	public function _e($string) {
		$view = $this->app->view();

		if ($view instanceof DefaultTheme) {
			return $view->_e($string);
		}

		return htmlspecialchars($string, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
	}

	public function _m() {
		$view = $this->app->view();

		if (!($view instanceof DefaultTheme)) {
			return $message_key;
		}

		return call_user_func_array(array($view, '_m'), func_get_args());
	}
}
