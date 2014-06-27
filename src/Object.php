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

use Everyman\Neo4j;

use LogicException;
use RuntimeException;
use InvalidArgumentException;

abstract class Object {

	protected $id, $schema, $object, $properties = array();

	private $got_properties;

	public function __construct(ObjectSchema $object_schema, Neo4j\Client $client, $id = null) {
		$this->schema = $object_schema;
		$this->client = $client;

		if ($id instanceof Neo4j\PropertyContainer) {
			$this->object = $id;
			$id = $this->object->getId();
		}

		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	public function hasId() {
		return !is_null($this->id);
	}

	public function getSchema() {
		return $this->schema;
	}

	public function getSlug() {
		if (!$this->hasId()) {
			throw new LogicException('Cannot create slug for object with no ID.');
		}

		return $this->schema->getSlug() . '/' . $this->id;
	}

	public function getTitle() {
		$option = $this->schema->getOption('title');
		if (!$option) {
			return $this->getDefaultTitle();
		}

		if (!is_callable($option)) {
			throw new RuntimeException('Option for "title" must be callable.');
		}

		$title = $option($this);
		if (!$title) {
			return $this->getDefaultTitle();
		}

		return $title;
	}

	public function getDefaultTitle() {
		if ($this->hasId()) {
			return $this->schema->getName() . ' #' . $this->getId();
		}

		return '';
	}

	public function setProperty($name, $value) {
		$this->getProperty($name)->setValue($value);
	}

	public function getProperty($name) {
		if (!$this->schema->hasProperty($name)) {
			throw new InvalidArgumentException('Unknown property "' . $name . '".');
		}

		if (!$this->got_properties) {
			$this->loadProperties();
		}

		return $this->properties[$name];
	}

	public function getProperties() {
		if (!$this->got_properties) {
			$this->loadProperties();
		}

		return array_values($this->properties);
	}

	private function loadProperties() {
		$this->got_properties = true;

		// Prefill with values from the server if available.
		if ($this->hasId() and !$this->object) {
			$this->fetch();
		}

		// Look up each property separately instead of using $object#getProperties.
		// That way the order of properties as defined on the schema is maintained :).
		foreach ($this->schema->getProperties() as $property_schema) {
			$property_name = $property_schema->getName();
			$property = new Property($property_schema);
			$this->properties[$property_name] = $property;

			if ($this->object and !is_null($value = $this->object->getProperty($property_name))) {
				$property->setValue($value);
			}
		}
	}
}
