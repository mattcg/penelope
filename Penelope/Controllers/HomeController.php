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

use Karwana\Penelope\Schema;

class HomeController extends Controller {

	protected $schema;

	public function __construct(Slim\Slim $app, Schema $schema) {
		parent::__construct($app);
		$this->schema = $schema;
	}

	public function read() {
		$view_data = array('title' => 'Welcome', 'node_schemas' => $this->schema->getNodes());
		$this->app->render('home', $view_data);
	}
}
