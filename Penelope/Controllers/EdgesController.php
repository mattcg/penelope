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

	public function getSchemaBySlugs($node_schema_slug, $edge_schema_slug) {

		try {
			$edge_schema = $this->schema->getEdgeBySlug($edge_schema_slug);

		// If the edge schema with the given slug doesn't exist.
		} catch (\InvalidArgumentException $e) {
			$this->render404($e);
			$this->app->stop();
		}

		$from_node_schema = $edge_schema->getOutSchema();

		// If the edge's schema defines relationships from nodes of a different schema.
		if ($from_node_schema->getSlug() !== $node_schema_slug) {
			$this->render404(new Exceptions\SchemaException('The schema for edges of type "' . $edge_schema->getName() . '" does not permit relationships with nodes of the given type.'));
			$this->app->stop();
		}

		return $edge_schema;
	}

	public function getByParams($node_schema_slug, $node_id, $edge_schema_slug) {

		// Check that:
		// - the edge schema with the given slug exists
		// - the edge schema defines relationships with nodes of the given node schema
		$edge_schema = $this->getSchemaBySlugs($node_schema_slug, $edge_schema_slug);

		// Check that:
		// - the node schema with the given slug exists
		// - the node with the given ID exists
		$controller = new NodeController($this->app, $this->schema, $this->client);
		$node = $controller->getByParams($node_schema_slug, $node_id);

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
		$edge_schema = $this->getSchemaBySlugs($node_schema_slug, $edge_schema_slug);
		$edge = new Edge($edge_schema, $this->client);

		$transient_properties = array();
		$has_errors = false;

		$app = $this->app;

		$from_node_id = $app->request->post('from_node_id');

		$from_schema = $edge_schema->getOutSchema();

		try {
			$edge = $edge_schema->get($this->client, $edge_id);
		} catch (Exceptions\Exception $e) {
			$this->render404($e);
			$this->app->stop();
		} catch (Exceptions\SchemaException $e) {
			$this->render404($e);
			$this->app->stop();
		}

		$this->processProperties($edge, $app->request->post(), $transient_properties, $has_errors);

		if ($has_errors) {
			$this->renderNewForm($schema_slug, $transient_properties);
			return;
		}

		try {
			$node->save();
		} catch (\Exception $e) {
			$this->renderNewForm($schema_slug, $transient_properties, $e);
			return;
		}

		$view_data = array('title' => $node_schema->getName() . ' #' . $node->getId() . ' created', 'node' => $node);
		$app->response->setStatus(201);
		$app->response->headers->set('Location', $node->getPath());
		$app->render('node_created', $view_data);
	}

	public function read($node_schema_slug, $node_id, $edge_schema_slug) {
		$edge_schema = $this->getSchemaBySlugs($node_schema_slug, $edge_schema_slug);
		$edges = $this->getByParams($node_schema_slug, $node_id, $edge_schema_slug);

		$view_data = array('title' => $edge_schema->getName() . ' Edges from node #' . $node->getId());
		$this->app->render('edges', $view_data);
	}

	public function delete($node_schema_slug, $node_id, $edge_schema_slug) {

		// TODO: View.
		throw \BadMethodCallException('Not implemented.');

		$edges = $this->getByParams($node_schema_slug, $node_id, $edge_schema_slug);

		// Delete the entire collection.
		foreach ($edges as $edge) {
			$edge->delete();
		}
	}

	public function renderNewForm($node_schema_slug, $node_id, $edge_schema_slug, array $transient_properties = null, \Exception $e = null) {
		$edge_schema = $this->getSchemaBySlugs($node_schema_slug, $edge_schema_slug);

		$view_data = array('title' => 'New ' . $edge_schema->getName(), 'error' => $e);
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
