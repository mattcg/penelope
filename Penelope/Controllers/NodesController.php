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

use Karwana\Penelope\Node;
use Karwana\Penelope\TransientProperty;

class NodesController extends ObjectController {

	public function getSchemaBySlug($schema_slug) {
		try {
			$node_schema = $this->schema->getNodeBySlug($schema_slug);

		// If the node schema with the given slug doesn't exist.
		} catch (\InvalidArgumentException $e) {
			$this->render404($e);
			$this->app->stop();
		}

		return $node_schema;
	}

	public function read($schema_slug) {
		$node_schema = $this->getSchemaBySlug($schema_slug);

		$view_data = array('title' => $node_schema->getName() . ' list', 'node_schema' => $node_schema);

		$label = $this->client->makeLabel($node_schema->getName());
		$view_data['nodes'] = array();

		// TODO: Use a NodeList that lazy loads objects.
		foreach ($label->getNodes() as $node) {
			$view_data['nodes'][] = new Node($node_schema, $this->client, $node);
		}

		$this->app->render('nodes', $view_data);
	}

	public function create($schema_slug) {
		$node_schema = $this->getSchemaBySlug($schema_slug);
		$node = new Node($node_schema, $this->client);

		$transient_properties = array();
		$has_errors = false;

		$app = $this->app;

		$this->processProperties($node, $app->request->post(), $transient_properties, $has_errors);

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

	public function renderNewForm($schema_slug, array $transient_properties = null, \Exception $e = null) {
		$node_schema = $this->getSchemaBySlug($schema_slug);

		$view_data = array('title' => 'New ' . $node_schema->getName(), 'error' => $e);
		$view_data['properties'] = array();

		foreach ($node_schema->getProperties() as $property_schema) {
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

		$view_data['node_schema'] = $node_schema;
		$this->app->render('node_new', $view_data);
	}
}
