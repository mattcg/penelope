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

abstract class NodeController extends ObjectController {

	private function getSchemaBySlug($schema_slug) {
		return (new NodesController($this->app, $this->schema, $this->client))->getSchemaBySlug($schema_slug);
	}

	public function getByParams($schema_slug, $node_id) {
		$node_schema = $this->getSchemaBySlug($schema_slug);

		try {
			$node = $node_schema->get($this->client, $node_id);

		// If the node with the given ID doesn't exist.
		} catch (Exceptions\NotFoundException $e) {
			$this->render404($e);
			$this->app->stop();

		// If the node with the given ID exists, but doesn't match the given schema.
		} catch (Exceptions\SchemaException $e) {
			$this->render404($e);
			$this->app->stop();
		}

		return $node;
	}

	public function read($schema_slug, $node_id) {
		$node = $this->getByParams($schema_slug, $node_id);
		$node_schema = $node->getSchema();

		$view_data = array('title' => $node->getTitle(), 'node' => $node, 'node_schema' => $node_schema);
		$this->app->render('node', $view_data);
	}

	public function delete($schema_slug, $node_id) {
		$node = $this->getByParams($schema_slug, $node_id);
		$id = $node->getId();
		$node_schema = $node->getSchema();

		$view_data = array('title' => 'Deleted ' . $node_schema->getName() . ' #' . $id, 'node_schema' => $node_schema);

		$node->delete();
		$this->app->render('node_deleted', $view_data);
	}

	public function update($schema_slug, $node_id) {
		$node = $this->getByParams($schema_slug, $node_id);
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
		$node = $this->getByParams($schema_slug, $node_id);
		$node_schema = $node->getSchema();

		$view_data = array('title' => 'Edit ' . $node->getDefaultTitle(), 'error' => $e);
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
