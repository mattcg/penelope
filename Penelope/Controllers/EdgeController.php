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

use Karwana\Penelope\Exceptions;

class EdgeController extends ObjectController {

	private function getByParamsArray($params) {
		return call_user_func_array(array($this, 'getEdgeByParams'), $params);
	}

	public function read($node_schema_slug, $node_id, $edge_schema_slug, $edge_id) {
		$edge = $this->getByParamsArray(func_get_args());

		$viewdata = array('title' => $edge->getTitle());

		$viewdata['edge'] = $edge;
		$viewdata['node'] = $this->getNodeByParams($node_schema_slug, $node_id);

		$this->app->render('edge', $viewdata);
	}

	public function readSvg($node_schema_slug, $node_id, $edge_schema_slug, $edge_id) {
		$edge = $this->getByParamsArray(func_get_args());

		$this->app->response->headers->set('Content-Type', 'image/svg+xml');
		$this->app->render('edge_svg', array('edge' => $edge, 'bookends' => false));
	}

	public function delete($node_schema_slug, $node_id, $edge_schema_slug, $edge_id) {
		$edge = $this->getByParamsArray(func_get_args());
		$edge_title = $edge->getTitle();
		$edge->delete();

		$viewdata = array('title' => $this->_m('edge_deleted_title', $edge_title));
		$viewdata['node'] = $this->getNodeByParams($node_schema_slug, $node_id);
		$viewdata['edge_schema'] = $edge->getSchema();

		$this->app->render('edge_deleted', $viewdata);
	}

	public function renderEditForm($node_schema_slug, $node_id, $edge_schema_slug, $edge_id, array $transient_properties = null, \Exception $e = null) {
		$edge = $this->getEdgeByParams($node_schema_slug, $node_id, $edge_schema_slug, $edge_id);
		$edge_schema = $edge->getSchema();

		$view_data = array('title' => $this->_m('edit_edge_title', $edge->getTitle()), 'error' => $e);
		$view_data['edge_schema'] = $edge_schema;
		$view_data['edge'] = $edge;
		$view_data['node'] = $this->getNodeByParams($node_schema_slug, $node_id);

		$view_data['properties'] = array();

		foreach ($edge_schema->getProperties() as $property_schema) {
			$property_name = $property_schema->getName();

			if (isset($transient_properties[$property_name])) {
				$property = $transient_properties[$property_name];
			} else {
				$property = $edge->getProperty($property_name);
			}

			$view_data['properties'][] = $property;
		}

		if ($e) {
			$this->app->response->setStatus(500);
		} else if (!empty($transient_properties)) {
			$this->app->response->setStatus(422);
		}

		$this->app->render('edge_edit', $view_data);
	}
}
