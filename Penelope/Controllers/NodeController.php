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

		if ($abstract_property_name = $node_schema->getOption('fields.abstract')) {
			$view_data['abstract_property_name'] = $abstract_property_name;
		}

		if ($body_property_name = $node_schema->getOption('fields.body')) {
			$view_data['body_property_name'] = $body_property_name;
		}

		$edge_schemas = $this->schema->getOutEdges($node_schema->getName());

		$view_data['edge_schemas'] = $edge_schemas;
		$view_data['edges'] = array();

		foreach ($edge_schemas as $edge_schema) {
			$view_data['edges'][$edge_schema->getName()] = $node->getOutEdges($edge_schema);;
		}

		$reverse_edge_schemas = $this->schema->getInEdges($node_schema->getName());

		$view_data['reverse_edge_schemas'] = array();
		$view_data['reverse_edges'] = array();

		// Add reverse relationships, but only if a display name is specified.
		foreach ($reverse_edge_schemas as $reverse_edge_schema) {
			if (!$reverse_edge_schema->hasOption('format.reverse_name')) {
				continue;
			}

			$reverse_edges = $node->getInEdges($reverse_edge_schema);
			if (!empty($reverse_edges)) {
				$view_data['reverse_edge_schemas'][] = $reverse_edge_schema;
				$view_data['reverse_edges'][$reverse_edge_schema->getName()] = $reverse_edges;
			}
		}

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
			$this->app->notFound(new NotFoundException('The node was deleted by another user before it could be updated.'));
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
