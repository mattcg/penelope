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

use Karwana\Penelope\Edge;
use Karwana\Penelope\Exceptions;
use Karwana\Penelope\TransientProperty;

class EdgesController extends ObjectController {

	public function getEdgesByParams($node_schema_slug, $node_id, $edge_schema_slug) {
		$node = $this->getNodeByParams($node_schema_slug, $node_id);

		// Check that:
		// - the edge schema with the given slug exists
		// - the edge schema defines relationships with nodes of the given node schema
		$edge_schema = $this->getEdgeSchemaBySlugs($node_schema_slug, $edge_schema_slug);

		try {
			$edges = $node->getOutEdges($edge_schema);

		// If the edge schema does not define relationships from nodes of the given type.
		} catch (Exceptions\SchemaException $e) {
			$this->render404($e);
			$this->app->stop();
		}

		return $edges;
	}

	public function create($node_schema_slug, $node_id, $edge_schema_slug) {
		$app = $this->app;

		$edge_schema = $this->getEdgeSchemaBySlugs($node_schema_slug, $edge_schema_slug);
		$edge = new Edge($edge_schema, $this->client);

		$transient_properties = array();
		$has_errors = false;

		$from_node = $this->getNodeByParams($node_schema_slug, $node_id);
		$to_node = $this->getNodeByParams($edge_schema->getOutSchema()->getSlug(), $app->request->post('to_node'));

		if ($from_node->getId() === $to_node->getId()) {
			throw new Exceptions\SchemaException('A node may not have an edge to itself.');
		}

		try {
			$edge->setRelationShip($from_node, $to_node);
		} catch (Exceptions\SchemaException $e) {
			$this->renderNewForm($node_schema_slug, $node_id, $edge_schema_slug, $transient_properties, $e);
			return;
		}

		$this->processProperties($edge, $app->request->post(), $transient_properties, $has_errors);

		if ($has_errors) {
			$this->renderNewForm($node_schema_slug, $node_id, $edge_schema_slug, $transient_properties);
			return;
		}

		try {
			$edge->save();
		} catch (\Exception $e) {
			$this->renderNewForm($node_schema_slug, $node_id, $edge_schema_slug, $transient_properties, $e);
			return;
		}

		$view_data = array('title' => $edge->getTitle() . ' created', 'edge' => $edge);
		$app->response->setStatus(201);
		$app->response->headers->set('Location', $edge->getPath());
		$app->render('edge_created', $view_data);
	}

	public function read($node_schema_slug, $node_id, $edge_schema_slug) {
		$node = $this->getNodeByParams($node_schema_slug, $node_id);

		$edge_schema = $this->getEdgeSchemaBySlugs($node_schema_slug, $edge_schema_slug);
		$edges = $this->getEdgesByParams($node_schema_slug, $node_id, $edge_schema_slug);

		$view_data = array('title' => $edge_schema->getName() . ' relationships from ' . $node->getTitle());
		$view_data['node'] = $node;
		$view_data['edge_schema'] = $edge_schema;

		$this->app->render('edges', $view_data);
	}

	public function delete($node_schema_slug, $node_id, $edge_schema_slug) {

		// TODO: View.
		throw \BadMethodCallException('Not implemented.');

		$edges = $this->getEdgesByParams($node_schema_slug, $node_id, $edge_schema_slug);

		// Delete the entire collection.
		foreach ($edges as $edge) {
			$edge->delete();
		}
	}

	public function renderNewForm($node_schema_slug, $node_id, $edge_schema_slug, array $transient_properties = null, \Exception $e = null) {
		$edge_schema = $this->getEdgeSchemaBySlugs($node_schema_slug, $edge_schema_slug);
		$node = $this->getNodeByParams($node_schema_slug, $node_id);

		$view_data = array('title' => 'New ' . $edge_schema->getName() . ' relationship from ' . $node->getTitle(), 'error' => $e);
		$view_data['node'] = $node;
		$view_data['properties'] = array();

		foreach ($edge_schema->getProperties() as $property_schema) {
			$property_name = $property_schema->getName();

			if (isset($transient_properties[$property_name])) {
				$transient_property = $transient_properties[$property_name];
			} else {
				$transient_property = new TransientProperty($property_schema);
			}

			$view_data['properties'][] = $transient_property;
		}

		if ($e) {
			$this->app->response->setStatus(500);
		} else if (!empty($transient_properties)) {
			$this->app->response->setStatus(422);
		}

		$view_data['edge_schema'] = $edge_schema;
		$this->app->render('edge_new', $view_data);
	}
}
