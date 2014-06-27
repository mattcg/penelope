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

namespace Karwana\Penelope;

use Slim;
use Everyman\Neo4j;
use Exceptions\NotFoundException;

class Crud {

	private $schema, $client, $app;

	public function __construct(Schema $schema, Neo4j\Client $client, Slim\Slim $app) {
		$this->schema = $schema;
		$this->client = $client;
		$this->app = $app;
	}

	public function readHome() {
		$view_data = array('title' => 'Welcome', 'node_schemas' => $this->schema->getNodes());
		$this->app->render('home', $view_data);
	}

	public function createNode(NodeSchema $node_schema) {
		$node = new Node($node_schema, $this->client);
		$data = $this->app->request->post();

		$transient_properties = array();
		$has_errors = false;

		foreach ($data as $name => $value) {
			if (!$node_schema->hasProperty($name)) {
				continue;
			}

			$transient_property = new TransientProperty($node_schema->getProperty($name));
			$transient_property->setValue($value);

			try {
				$node->setProperty($name, $value);
			} catch (\InvalidArgumentException $e) {
				$transient_property->setError($e);
				$has_errors = true;
			}

			$transient_properties[$name] = $transient_property;
		}

		if ($has_errors) {
			$this->renderNewNodeForm($node_schema, $transient_properties);
			return;
		}

		try {
			$node->save();
		} catch (\Exception $e) {
			$this->renderNewNodeForm($node_schema, $transient_properties, $e);
			return;
		}

		$view_data = array('title' => $node_schema->getName() . ' #' . $node->getId() . ' created', 'node' => $node);
		$this->app->response->setStatus(201);
		$this->app->response->headers->set('Location', $node->getPath());
		$this->app->render('node_created', $view_data);
	}

	public function renderNewNodeForm(NodeSchema $node_schema, array $transient_properties = null, \Exception $e = null) {
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

	public function updateNode(Node $node) {
		$data = $this->app->request->put();

		$node_schema = $node->getSchema();
		$transient_properties = array();
		$has_errors = false;

		foreach ($data as $name => $value) {
			if (!$node_schema->hasProperty($name)) {
				continue;
			}

			$transient_property = new TransientProperty($node_schema->getProperty($name));
			$transient_property->setValue($value);
	
			try {
				$node->setProperty($name, $value);
			} catch (\InvalidArgumentException $e) {
				$transient_property->setError($e);
				$has_errors = true;
			}
	
			$transient_properties[$name] = $transient_property;
		}

		if ($has_errors) {
			$this->app->response->setStatus(422);
			$this->renderEditNodeForm($node, $transient_properties);
			return;
		}

		try {
			$node->save();
		} catch (NotFoundException $e) { // Thrown when the node isn't found. Indicates an edit conflict in this case.
			$this->render404(new NotFoundException('The node was deleted by another user before it could be updated.'));
			return;
		} catch (\Exception $e) {
			$this->renderEditNodeForm($node, $transient_properties, $e);
			return;
		}

		$this->readNode($node);
	}

	public function renderEditNodeForm(Node $node, array $transient_properties = null, \Exception $e = null) {
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

	public function readNode(Node $node) {
		$node_schema = $node->getSchema();

		$view_data = array('title' => $node->getTitle(), 'node' => $node, 'node_schema' => $node->getSchema());
		$this->app->render('node', $view_data);
	}

	public function readNodes(NodeSchema $node_schema) {
		$view_data = array('title' => $node_schema->getName() . ' list', 'node_schema' => $node_schema);

		$label = $this->client->makeLabel($node_schema->getName());
		$view_data['nodes'] = array();

		// TODO: Use a NodeList that lazy loads objects.
		foreach ($label->getNodes() as $node) {
			$view_data['nodes'][] = new Node($node_schema, $this->client, $node);
		}

		$this->app->render('nodes', $view_data);
	}

	public function deleteNode(Node $node) {
		$id = $node->getId();
		$node_schema = $node->getSchema();

		$view_data = array('title' => 'Deleted ' . $node_schema->getName() . ' #' . $id, 'node_schema' => $node_schema);

		$node->delete();
		$this->app->render('node_deleted', $view_data);
	}

	public function createEdge(Node $from_node, EdgeSchema $edge_schema) {
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
			} catch (\InvalidArgumentException $e) {
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

	public function readEdges(Node $node, EdgeSchema $edge_schema) {
		$edges = $node->getEdges($edge_schema);
		$this->app->render('edges', array('title' => $edge_schema->getName() . ' Edges from node #' . $node->getId()));
	}

	public function deleteEdge(Edge $edge) {
		$id = $edge->getId();
		$schema = $edge->getSchema();

		$edge->delete();
		$this->app->render('edge_deleted', array('title' => 'Deleted edge ' . $id));
	}

	public function render404(\Exception $e) {
		$this->app->render('error/404', array('title' => 'Not found', 'error' => $e), 404);
	}
}
