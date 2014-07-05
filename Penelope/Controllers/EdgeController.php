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

use Karwana\Penelope\Exceptions;

class EdgeController extends ObjectController {

	private function getByParamsArray($params) {
		return call_user_func_array(array($this, 'getEdgeByParams'), $params);
	}

	public function read($node_schema_slug, $node_id, $edge_schema_slug, $edge_id) {
		$edge = $this->getByParamsArray(func_get_args());
		$edge->delete();
		$this->app->render('edge', array('title' => $edge->getTitle(), 'edge' => $edge));
	}

	public function delete($node_schema_slug, $node_id, $edge_schema_slug, $edge_id) {
		$edge = $this->getByParamsArray(func_get_args());
		$edge->delete();
		$this->app->render('edge_deleted', array('title' => 'Deleted ' . $edge->getTitle()));
	}
}
