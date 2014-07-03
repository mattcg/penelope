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

	protected function processProperties(Object $object, $data, array &$transient_properties, &$has_errors) {
		$object_schema = $object->getSchema();

		// Process file uploads.
		foreach ($_FILES as $name => $file) {
			$this->processFile($object, $name, $file, $transient_properties, $has_errors);
		}

		// Process form data into properties.
		foreach ($object_schema->getProperties() as $property_schema) {
			$name = $property_schema->getName();

			// Skip over files, which are handled separately.
			if (isset($_FILES[$name])) {
				continue;
			}

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
	}

	private function processFile(Object $object, $name, $file, array &$transient_properties, &$has_errors) {
		$object_schema = $object->getSchema();
		if (!$object_schema->hasProperty($name)) {
			return;
		}

		$property_schema = $object_schema->getProperty($name);
		$transient_property = new TransientProperty($property_schema);
		$transient_properties[$name] = $transient_property;

		// Check if each uploaded file was "OK" and if so set the value on the transient propert.
		// Otherwise, set an error.
		if ($property_schema->isMultiValue()) {
			$this->processMultiFile($transient_property, $file);
		} else if (UPLOAD_ERR_OK === $file['error']) {
			$transient_property->setValue(array($file['tmp_name'], $file['name']));
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

		$value = array();

		// Array of files needs a second loop.
		foreach ($file['error'] as $i => $error) {
			if (UPLOAD_ERR_OK === $error) {
				$value[] = array($file['tmp_name'][$i], $file['name'][$i]);
			} else if (UPLOAD_ERR_NO_FILE !== $error or $transient_property->getSchema()->getOption('required')) {
				$transient_property->setError(new Exceptions\UploadException($error));
			}
		}

		$transient_property->setValue($value);
	}
}
