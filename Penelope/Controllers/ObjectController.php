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

use Slim;
use Everyman\Neo4j;

use Karwana\Penelope\Schema;
use Karwana\Penelope\Object;
use Karwana\Penelope\TransientProperty;
use Karwana\Penelope\Exceptions;

abstract class ObjectController extends Controller {

	protected $client, $schema;

	public function __construct(Slim\Slim $app, Schema $schema, Neo4j\Client $client) {
		parent::__construct($app);

		$this->client = $client;
		$this->schema = $schema;
	}

	public function getNodeByParams($schema_slug, $node_id) {
		$node_schema = $this->getNodeSchemaBySlug($schema_slug);

		// Validate the format of the given node ID.
		if (!ctype_digit($node_id)) {
			$this->render404(new \InvalidArgumentException('Invalid node ID "' . $node_id . '".'));
			$this->app->stop();
		}

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

	public function getNodeSchemaBySlug($schema_slug) {
		try {
			$node_schema = $this->schema->getNodeBySlug($schema_slug);

		// If the node schema with the given slug doesn't exist.
		} catch (\InvalidArgumentException $e) {
			$this->render404($e);
			$this->app->stop();
		}

		return $node_schema;
	}

	public function getEdgeByParams($node_schema_slug, $node_id, $edge_schema_slug, $edge_id) {

		// Validate the format of the given edge ID.
		if (!ctype_digit($edge_id)) {
			$this->render404(new \InvalidArgumentException('Invalid edge ID "' . $edge_id . '".'));
			$this->app->stop();
		}

		// Check that:
		// - the edge schema with the given slug exists
		// - the edge's schema defines relationships from nodes of the same schema
		$edge_schema = $this->getEdgeSchemaBySlugs($node_schema_slug, $edge_schema_slug);

		// Check that:
		// - the node schema with the given slug exists
		// - the node with the given ID exists
		$node = $this->getNodeByParams($node_schema_slug, $node_id);

		try {
			$edge = $edge_schema->get($this->client, $edge_id);

		// If the edge with the given ID doesn't exist.
		} catch (Exceptions\NotFoundException $e) {
			$this->render404($e);
			$this->app->stop();

		// If the edge with the given ID exists, but:
		//  - it doesn't match the given schema
		//  - its related nodes don't match its own schema
		} catch (Exceptions\SchemaException $e) {
			$this->render404($e);
			$this->app->stop();
		}

		// If the node with the given ID is not the start node of the edge with the given ID.
		if ($edge->getFromNode()->getId() !== $node->getId()) {
			$this->render404(new Exceptions\NotFoundException('There is no edge from the given node.'));
			$this->app->stop();
		}

		return $edge;
	}

	public function getEdgeSchemaBySlugs($node_schema_slug, $edge_schema_slug) {

		try {
			$edge_schema = $this->schema->getEdgeBySlug($edge_schema_slug);

		// If the edge schema with the given slug doesn't exist.
		} catch (\InvalidArgumentException $e) {
			$this->render404($e);
			$this->app->stop();
		}

		$start_node_schema = $edge_schema->getStartNodeSchema();

		// If the edge's schema defines relationships from nodes of a different schema.
		if ($start_node_schema->getSlug() !== $node_schema_slug) {
			$this->render404(new Exceptions\SchemaException('The schema for edges of type "' . $edge_schema->getName() . '" does not permit relationships with nodes of the given type.'));
			$this->app->stop();
		}

		return $edge_schema;
	}

	protected function processProperties(Object $object, $data, array &$transient_properties, &$has_errors) {
		$object_schema = $object->getSchema();

		// Process form data into properties.
		foreach ($object_schema->getProperties() as $property_schema) {
			$name = $property_schema->getName();

			// If any properties are left out of the form, set them to null.
			if (isset($data[$name])) {
				$value = $data[$name];
			} else {
				$value = null;
			}

			// Create a transient property that will be used to contain invalid values.
			$transient_property = new TransientProperty($object_schema->getProperty($name));
			$transient_properties[$name] = $transient_property;
			$transient_property->setSerializedValue($value);

			// Check if the property schema requires that the property have a value.
			if (!$transient_property->hasValue() and $transient_property->getSchema()->getOption('required')) {
				$transient_property->setError(new Exceptions\TypeException('A value is required.'));
				$has_errors = true;
				continue;
			}

			try {
				$object->setProperty($name, $transient_property->getValue());
			} catch (Exceptions\TypeException $e) {
				$transient_property->setError($e);
				$has_errors = true;
			}
		}

		// Process file uploads.
		foreach ($_FILES as $name => $file) {
			$this->processFile($object, $name, $file, $transient_properties, $has_errors);
		}
	}

	private function processFile(Object $object, $name, $file, array &$transient_properties, &$has_errors) {
		$object_schema = $object->getSchema();
		if (!$object_schema->hasProperty($name)) {
			return;
		}

		$property_schema = $object_schema->getProperty($name);

		// When a file is resubmitted as a serialized form value, it would be present the $_POST array.
		// Therefore it would have already been set as a transient property.
		if (isset($transient_properties[$name])) {
			$transient_property = $transient_properties[$name];
		} else {
			$transient_property = new TransientProperty($property_schema);
			$transient_properties[$name] = $transient_property;
		}

		// Check if each uploaded file was "OK" and if so set the value on the transient propert.
		// Otherwise, set an error.
		if ($property_schema->isMultiValue()) {
			$this->processMultiFile($transient_property, $file);
		} else if (UPLOAD_ERR_OK === $file['error']) {
			try {
				$perm_name = UploadController::move($file['tmp_name'], $file['name']);
			} catch (\RuntimeException $e) {
				$transient_property->setError($e);
			}

			if ($perm_name) {
				$transient_property->setValue(array($perm_name, $file['name']));
			}
		} else if (UPLOAD_ERR_NO_FILE !== $file['error'] or $transient_property->getSchema()->getOption('required')) {
			$transient_property->setError(new Exceptions\UploadException($file['error']));
		}

		if ($transient_property->hasError()) {
			$has_errors = true;
			return;
		}

		try {
			$object->setProperty($name, $transient_property->getValue());
		} catch (Exceptions\TypeException $e) {
			$transient_property->setError($e);
			$has_errors = true;
		}
	}

	private function processMultiFile(TransientProperty $transient_property, $file) {

		// Expecting an array of files.
		if (!is_array($file['error'])) {
			$transient_property->setError(new Exceptions\TypeException('Expecting multiple files; only one received.'));
			return;
		}

		$value = $transient_property->getValue();

		// Array of files needs a second loop.
		foreach ($file['error'] as $i => $error) {
			if (UPLOAD_ERR_OK === $error) {
				try {
					$perm_name = UploadController::move($file['tmp_name'][$i], $file['name'][$i]);
				} catch (\RuntimeException $e) {
					$transient_property->setError($e);
				}

				if ($perm_name) {
					$value[] = array($perm_name, $file['name'][$i]);
				}
			} else if (UPLOAD_ERR_NO_FILE !== $error or $transient_property->getSchema()->getOption('required')) {
				$transient_property->setError(new Exceptions\UploadException($error));
			}
		}

		$transient_property->setValue($value);
	}
}
