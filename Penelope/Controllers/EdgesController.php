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

abstract class EdgesController extends ObjectController {

	public function getSchemaBySlug($schema_slug) {
		try {
			$edge_schema = $this->schema->getEdgeBySlug($schema_slug);
		} catch (\InvalidArgumentException $e) {
			$this->render404($e);
			$this->app->stop();
		}

		return $edge_schema;
	}

	public function getByParams($node_schema_slug, $node_id, $edge_schema_slug) {
		$edge_schema = $this->getSchemaBySlug($edge_schema_slug);

		$controller = new NodeController($this->app, $this->schema, $this->client);
		$node = $controller->getByParams($node_schema_slug, $node_id);

		try {
			$edges = $node->getOutEdges($edge_schema);
		} catch (Exceptions\SchemaException $e) {
			$this->render404($e);
			$this->app->stop();
		}

		return $edges;
	}

	public function create(Node $from_node, EdgeSchema $edge_schema) {
		$request = $this->app->request;

		$user_input_errors = array();

		$to_node_id = $request->post('to_node_id');
		if (!$to_node_id) {
			$error_fields[] = 'to_node_id';
		}

		$to_node_name = $request->post('to_node_name');
		if (!$to_node_name) {
			$error_fields[] = 'to_node_name';
		}

		$data = $this->app->request->post();
		$user_input = array();

		foreach ($data as $name => $value) {
			if (!$schema->hasProperty($name)) {
				continue;
			}

			$user_input[$name] = $value;

			try {
				$node->setProperty($name, $value);
			} catch (Exceptions\TypeException $e) {
				$user_input_errors[$name] = $e;
			}
		}

		if (!empty($user_input_errors)) {
			$this->renderNewNodeForm($schema, $user_input, $user_input_errors);
			return;
		}

		try {
			$node->save();
		} catch (\Exception $e) {
			$this->renderNewNodeForm($schema, $user_input, null, $e);
			return;
		}

		$this->redirect($this->app->urlFor('node_' . $node->getSchema()->getName(), array('node_id' => $node->getId())), 201);



		if (!empty($error_fields)) {
			$this->app->render('edge/create/failure', array('error_fields' => $error_fields), 422);
			return;
		}

		$to_node = new Node($this->schema->getNode($to_node_name), $this->client, $to_node_id);
		try {
			$from_node->fetch();
		} catch (NotFoundException $e) {
			$error_fields[] = 'to_node_id';
			$this->app->render('edge/create/failure', array('error_fields' => $error_fields), 422);
			return;
		}

		$edge = new Edge($this->schema->getEdge($edge_name), $this->client);
		$data = $request->post();

		foreach ($data as $name => $value) {
			if (!$edge->schema->hasProperty($name)) {
				continue;
			}

			try {
				$edge->setProperty($name, $value);
			} catch (\InvalidArgumentException $e) {
				$error_fields[] = $name;
			}
		}

		if (!empty($error_fields)) {
			$this->app->render('edge/create/failure', array('error_fields' => $error_fields));
			return;
		}

		try {
			$edge->setRelationship($from_node, $to_node);
		} catch (\InvalidArgumentException $e) {
			$this->app->render('edge/create/failure', array('error' => $e), 422);
			return;
		}

		$this->app->render('edge/create/success', array(
			'from_node' => $from_node,
			'to_node' => $to_node,
			'edge' => $edge));
	}

	public function read($node_schema_slug, $node_id, $edge_schema_slug) {
		$edges = $this->getByParams($node_schema_slug, $node_id, $edge_schema_slug);
		$edge_schema = $this->schema->getEdgeBySlug($edge_schema_slug);

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
}
