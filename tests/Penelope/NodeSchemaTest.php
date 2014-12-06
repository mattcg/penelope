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

namespace Karwana\Penelope\Tests;

use Everyman\Neo4j;

use Karwana\Penelope\Schema;
use Karwana\Penelope\NodeSchema;

class NodeSchemaTest extends \PHPUnit_Framework_TestCase {

	public function getSchema() {
		$transport = new MockTransport();
		return new Schema(new Neo4j\Client($transport));
	}

	public function nodeSchemaProvider() {
		$schema = $this->getSchema();
		$node_schema = $schema->addNode('Person', 'people');
		return array(array($node_schema));
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetSlug_returnsSlug($node_schema) {
		$this->assertEquals('people', $node_schema->getSlug());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetName_returnsName($node_schema) {
		$this->assertEquals('Person', $node_schema->getName());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetDisplayName_returnsDisplayName($node_schema) {

		// Default name is the node name.
		$this->assertEquals('Person', $node_schema->getDisplayName());

		// An optional string can be specified.
		$node_schema->setOption('format.name', 'Persona');
		$this->assertEquals('Persona', $node_schema->getDisplayName());

		// Or a function.
		$node_schema->setOption('format.name', function($quantity) {
			if (1 === $quantity) {
				return '1 Persona';
			}

			return $quantity . ' Personas';
		});

		$this->assertEquals('1 Persona', $node_schema->getDisplayName());
		$this->assertEquals('2 Personas', $node_schema->getDisplayName(2));
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testCreate_returnsNode($node_schema) {
		$node = $node_schema->create();
		$this->assertInstanceOf('Karwana\Penelope\Node', $node);
		$this->assertEquals($node_schema, $node->getSchema());
	}



	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testHasProperty_returnsFalseForUnknownProperty($node_schema) {
		$this->assertFalse($node_schema->hasProperty('test'));
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testHasProperty_returnsTureForKnownProperty($node_schema) {
		$node_schema->defineProperty('test', 'text');
		$this->assertTrue($node_schema->hasProperty('test'));
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetProperty_returnsProperty($node_schema) {
		$node_schema->defineProperty('test', 'text');
		$property = $node_schema->getProperty('test');
		$this->assertInstanceOf('Karwana\Penelope\PropertySchema', $property);
		$this->assertEquals('test', $property->getName());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetProperty_throwsExceptionForUnknownName($node_schema) {
		$this->setExpectedException('InvalidArgumentException', 'Unknown property "test".');
		$node_schema->getProperty('test');
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetPathFormat_throwsExceptionForUnkownType($node_schema) {
		$this->setExpectedException('InvalidArgumentException', 'Invalid path type "test".');
		$node_schema->getPathFormat('test');
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetNewPath_returnsNewPath($node_schema) {
		$this->assertEquals('/people/new', $node_schema->getNewPath());

		// Test option.
		$node_schema->setOption('path.format.new', '/%s/nuevo');
		$this->assertEquals('/people/nuevo', $node_schema->getNewPath());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetEditPath_returnsEditPath($node_schema) {
		$this->assertEquals('/people/:node_id/edit', $node_schema->getEditPath());

		// Test option.
		$node_schema->setOption('path.format.edit', '/%s/%s/editar');
		$this->assertEquals('/people/:node_id/editar', $node_schema->getEditPath());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetPath_returnsPath($node_schema) {
		$this->assertEquals('/people/:node_id', $node_schema->getPath());

		// Test option.
		$node_schema->setOption('path.format.object', '/%s/%s/ver');
		$this->assertEquals('/people/:node_id/ver', $node_schema->getPath());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollectionPath_returnsPath($node_schema) {
		$this->assertEquals('/people/', $node_schema->getCollectionPath());

		// Test option.
		$node_schema->setOption('path.format.collection', '/%s/todos');
		$this->assertEquals('/people/todos', $node_schema->getCollectionPath());
	}
}
