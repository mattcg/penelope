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

class NodeController extends ObjectController {

	public function read($schema_slug, $node_id) {
		$node = $this->getNodeByParams($schema_slug, $node_id);
		$node_schema = $node->getSchema();

		$view_data = array('title' => $node->getTitle(), 'node' => $node, 'node_schema' => $node_schema);
		$view_data['edge_schemas'] = $this->schema->getOutEdges($node->getSchema()->getName());
		$this->app->render('node', $view_data);
	}

	public function delete($schema_slug, $node_id) {
		$node = $this->getNodeByParams($schema_slug, $node_id);
		$node_title = $node->getTitle();
		$node_schema = $node->getSchema();

		$view_data = array('title' => $this->_m('node_deleted_title', $node_title), 'node_schema' => $node_schema);

		$node->delete();
		$this->app->render('node_deleted', $view_data);
	}

	public function update($schema_slug, $node_id) {
		$node = $this->getNodeByParams($schema_slug, $node_id);
		$transient_properties = array();
		$has_errors = false;

		$this->processProperties($node, $this->app->request->put(), $transient_properties, $has_errors);

		if ($has_errors) {
			$this->app->response->setStatus(422);
			$this->renderEditForm($schema_slug, $node_id, $transient_properties);
			return;
		}

		try {
			$node->save();
		} catch (Exceptions\NotFoundException $e) { // Thrown when the node isn't found. Indicates an edit conflict in this case.
			$this->render404(new NotFoundException('The node was deleted by another user before it could be updated.'));
			return;
		} catch (\Exception $e) {
			$this->renderEditForm($schema_slug, $node_id, $transient_properties, $e);
			return;
		}

		$this->read($schema_slug, $node_id);
	}

	public function renderEditForm($schema_slug, $node_id, array $transient_properties = null, \Exception $e = null) {
		$node = $this->getNodeByParams($schema_slug, $node_id);
		$node_schema = $node->getSchema();

		$view_data = array('title' => $this->_m('edit_node_title', $node->getTitle()), 'error' => $e);
		$view_data['properties'] = array();

		foreach ($node_schema->getProperties() as $property_schema) {
			$property_name = $property_schema->getName();

			if (isset($transient_properties[$property_name])) {
				$property = $transient_properties[$property_name];
			} else {
				$property = $node->getProperty($property_name);
			}

			$view_data['properties'][] = $property;
		}

		if ($e) {
			$this->app->response->setStatus(500);
		} else if (!empty($transient_properties)) {
			$this->app->response->setStatus(422);
		}

		$view_data['node_schema'] = $node_schema;
		$view_data['node'] = $node;
		$this->app->render('node_edit', $view_data);
	}
}
