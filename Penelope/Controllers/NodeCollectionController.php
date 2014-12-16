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

use Karwana\Penelope\Node;
use Karwana\Penelope\NodeCollection;
use Karwana\Penelope\TransientProperty;

class NodeCollectionController extends ObjectCollectionController {

	public function read($schema_slug) {
		$node_schema = $this->getNodeSchemaBySlug($schema_slug);

		// Don't use NodeSchema#getCollection as we don't want to fetch as yet.
		$node_collection = new NodeCollection($node_schema);

		$view_data = $this->readPagedCollection($node_collection);

		$view_data['title'] = $this->_m('node_collection_title', $node_schema->getDisplayName(0));
		$view_data['node_schema'] = $node_schema;
		$view_data['nodes'] = $node_collection;

		$this->app->render('nodes', $view_data);
	}

	public function create($schema_slug) {
		$node_schema = $this->getNodeSchemaBySlug($schema_slug);
		$node = $node_schema->create();

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

		$view_data = array('title' => $this->_m('node_created_title', $node->getTitle()), 'node' => $node);
		$app->response->setStatus(201);
		$app->response->headers->set('Location', $node->getPath());
		$app->render('node_created', $view_data);
	}

	public function renderNewForm($schema_slug, array $transient_properties = null, \Exception $e = null) {
		$node_schema = $this->getNodeSchemaBySlug($schema_slug);

		$view_data = array('title' => $this->_m('new_node_title', $node_schema->getDisplayName()), 'error' => $e);
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
