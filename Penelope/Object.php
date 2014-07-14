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

namespace Karwana\Penelope;

use Everyman\Neo4j;

abstract class Object {

	protected $id, $schema, $object, $properties = array();

	private $got_properties;

	public function __construct(ObjectSchema $object_schema, Neo4j\Client $client, $id = null) {
		$this->schema = $object_schema;
		$this->client = $client;

		if ($id instanceof Neo4j\PropertyContainer) {
			$this->object = $id;
			$this->id = $this->object->getId();
		} else if (is_int($id) or ctype_digit($id)) {
			$this->id = (int) $id;
		}
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

	public function getClient() {
		return $this->client;
	}

	public function getClientObject() {
		if (!$this->object) {
			$this->fetch();
		}

		return $this->object;
	}

	public function getTitle() {
		if (!$this->hasId()) {
			return $this->getDefaultTitle();
		}

		$option = $this->schema->getOption('format.title');
		if (!$option) {
			return $this->getDefaultTitle();
		}

		if (!is_callable($option)) {
			throw new \RuntimeException('Option for "title" must be callable.');
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
			throw new \InvalidArgumentException('Unknown property "' . $name . '".');
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

	public function save() {

		// Fetch the object if it hasn't been fetched yet.
		if (!$this->object) {
			$this->fetch();
		}

		$object = $this->object;
		foreach ($this->properties as $property) {
			$object->setProperty($property->getName(), $property->getSerializedValue());
		}

		$object->save();
		$this->id = $object->getId();
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
				$property->setSerializedValue($value);
			}
		}
	}
}
