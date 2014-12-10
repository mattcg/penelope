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
	public function testDefineProperty_usesTextTypeByDefault($node_schema) {
		$node_schema->defineProperty('test');
		$this->assertTrue($node_schema->hasProperty('test'));
		$this->assertEquals('test', $node_schema->getProperty('test')->getName());
		$this->assertEquals('text', $node_schema->getProperty('test')->getType());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testDefineProperty_allowsTypeToBeSpecified($node_schema) {
		$node_schema->defineProperty('test', array('type' => 'date'));
		$this->assertTrue($node_schema->hasProperty('test'));
		$this->assertEquals('test', $node_schema->getProperty('test')->getName());
		$this->assertEquals('date', $node_schema->getProperty('test')->getType());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testDefineProperty_throwsExceptionForInvalidDefinition($node_schema) {
		$this->setExpectedException('InvalidArgumentException', 'Invalid property definition.');
		$node_schema->defineProperty(0);
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testDefineProperty_allowsMultiValueTypeToBeSpecified($node_schema) {
		$node_schema->defineProperty('test', array('type' => 'date[]'));
		$this->assertEquals('date', $node_schema->getProperty('test')->getType());
		$this->assertTrue($node_schema->getProperty('test')->isMultiValue());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testDefineProperties_definesProperties($node_schema) {
		$node_schema->defineProperties(array('test1', 'test2' => array('type' => 'date')));
		$this->assertTrue($node_schema->hasProperty('test1'));
		$this->assertTrue($node_schema->hasProperty('test2'));
		$this->assertEquals('text', $node_schema->getProperty('test1')->getType());
		$this->assertEquals('date', $node_schema->getProperty('test2')->getType());
	}

	public function testConstructor_definesProperties() {
		$schema = $this->getSchema();
		$node_schema = $schema->addNode('Person', 'people', array('test'));
		$this->assertTrue($node_schema->hasProperty('test'));
		$this->assertEquals('text', $node_schema->getProperty('test')->getType());
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


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGet_returnsNode($node_schema) {
		$transport = $node_schema->getClient()->getTransport();

		$transport->pushResponse(200, array(), array('Person'));
		$transport->pushResponse(200, array(), array(
			'columns' => array('n'),
			'data' => array(
				array(array(
					'self' => 'http://localhost:7474/db/data/node/1',
					'metadata' => array('id' => 1, 'labels' => array('Person')),
					'data' => array('born' => 1964, 'name' => 'Keanu Reeves')
				))
			))
		);

		$node = $node_schema->get(1);

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/node/1/labels',
			'data' => null), $transport->popRequest());

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/node/1',
			'data' => null), $transport->popRequest());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGet_throwsExceptionForInvalidId($node_schema) {
		$this->setExpectedException('InvalidArgumentException', 'Expecting an integer for the node ID.');
		$node_schema->get('hi');
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollectionCount_returnsCount($node_schema) {
		$transport = $node_schema->getClient()->getTransport();
		$transport->pushResponse(200, array(), array('columns' => array('count(n)'), 'data' => array(array(100))));

		$count = $node_schema->getCollectionCount();
		$this->assertEquals(100, $count);

		$last_request = $transport->popRequest();
		$this->assertEquals(array('method' => 'POST', 'path' => 'cypher', 'data' => array('query' => 'MATCH (n:Person) RETURN count(n)')), $last_request);
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollection_returnsCollection($node_schema) {
		$transport = $node_schema->getClient()->getTransport();
		$transport->pushResponse(200, array(), array(
			'columns' => array('n'),
			'data' => array(
				array(array(
					'self' => 'http://localhost:7474/db/data/node/1',
					'metadata' => array('id' => 1, 'labels' => array('Person')),
					'data' => array('born' => 1964, 'name' => 'Keanu Reeves')
				)),
				array(array(
					'self' => 'http://localhost:7474/db/data/node/2',
					'metadata' => array('id' => 2, 'labels' => array('Person')),
					'data' => array('born' => 1967, 'name' => 'Carrie-Anne Moss')
				))
			))
		);

		$collection = $node_schema->getCollection();
		$this->assertCount(2, $collection);

		$last_request = $transport->popRequest();
		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (n:Person) RETURN (n)'
			)),
			$last_request
		);
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollection_returnsOrderedCollection($node_schema) {
		$transport = $node_schema->getClient()->getTransport();
		$transport->pushResponse(200, array(), array(
			'columns' => array('n'),
			'data' => array(
				array(array(
					'self' => 'http://localhost:7474/db/data/node/2',
					'metadata' => array('id' => 2, 'labels' => array('Person')),
					'data' => array('born' => 1967, 'name' => 'Carrie-Anne Moss')
				)),
				array(array(
					'self' => 'http://localhost:7474/db/data/node/1',
					'metadata' => array('id' => 1, 'labels' => array('Person')),
					'data' => array('born' => 1964, 'name' => 'Keanu Reeves')
				))
			))
		);

		$node_schema->setOption('collection.order_by', array('name', 'born'));
		$collection = $node_schema->getCollection();
		$this->assertCount(2, $collection);

		$last_request = $transport->popRequest();
		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (n:Person) RETURN (n) ORDER BY n.name, n.born'
			)),
			$last_request
		);
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollection_returnsPagedCollectionWithLimitSet($node_schema) {
		$transport = $node_schema->getClient()->getTransport();
		$transport->pushResponse(200, array(), array(
			'columns' => array('n'),
			'data' => array(
				array(array(
					'self' => 'http://localhost:7474/db/data/node/1',
					'metadata' => array('id' => 1, 'labels' => array('Person')),
					'data' => array('born' => 1964, 'name' => 'Keanu Reeves')
				))
			))
		);

		$collection = $node_schema->getCollection(null, 0, 1);
		$this->assertCount(1, $collection);

		$last_request = $transport->popRequest();
		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (n:Person) RETURN (n) LIMIT 1'
			)),
			$last_request
		);
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollection_returnsPagedCollectionWithSkipAndLimitSet($node_schema) {
		$transport = $node_schema->getClient()->getTransport();
		$transport->pushResponse(200, array(), array(
			'columns' => array('n'),
			'data' => array(
				array(array(
					'self' => 'http://localhost:7474/db/data/node/2',
					'metadata' => array('id' => 2, 'labels' => array('Person')),
					'data' => array('born' => 1967, 'name' => 'Carrie-Anne Moss')
				))
			))
		);

		$collection = $node_schema->getCollection(null, 1, 1);
		$this->assertCount(1, $collection);

		$last_request = $transport->popRequest();
		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (n:Person) RETURN (n) SKIP 1 LIMIT 1'
			)),
			$last_request
		);
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollection_returnsPagedCollectionWithSkipSet($node_schema) {
		$transport = $node_schema->getClient()->getTransport();
		$transport->pushResponse(200, array(), array(
			'columns' => array('n'),
			'data' => array(
				array(array(
					'self' => 'http://localhost:7474/db/data/node/2',
					'metadata' => array('id' => 2, 'labels' => array('Person')),
					'data' => array('born' => 1967, 'name' => 'Carrie-Anne Moss')
				))
			))
		);

		$collection = $node_schema->getCollection(null, 1);
		$this->assertCount(1, $collection);

		$last_request = $transport->popRequest();
		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (n:Person) RETURN (n) SKIP 1'
			)),
			$last_request
		);
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollection_returnsSearchResults($node_schema) {
		$transport = $node_schema->getClient()->getTransport();
		$transport->pushResponse(200, array(), array(
			'columns' => array('n'),
			'data' => array(
				array(array(
					'self' => 'http://localhost:7474/db/data/node/1',
					'metadata' => array('id' => 1, 'labels' => array('Person')),
					'data' => array('born' => 1964, 'name' => 'Keanu Reeves')
				))
			))
		);

		$node_schema->defineProperty('name');
		$collection = $node_schema->getCollection(array('name' => 'Keanu Reeves'));
		$this->assertCount(1, $collection);

		$last_request = $transport->popRequest();
		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (n:Person) WHERE ANY (m IN {value_0} WHERE m IN n.name) RETURN (n)',
				'params' => array('value_0' => 'Keanu Reeves')
			)),
			$last_request
		);
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollectionCount_returnsSearchCount($node_schema) {
		$transport = $node_schema->getClient()->getTransport();
		$transport->pushResponse(200, array(), array('columns' => array('count(n)'), 'data' => array(array(1))));

		$node_schema->defineProperty('name');
		$count = $node_schema->getCollectionCount(array('name' => 'Keanu Reeves'));
		$this->assertEquals(1, $count);

		$last_request = $transport->popRequest();
		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (n:Person) WHERE ANY (m IN {value_0} WHERE m IN n.name) RETURN count(n)',
				'params' => array('value_0' => 'Keanu Reeves')
			)),
			$last_request
		);
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollectionCount_returnsCountForEmptySearch($node_schema) {
		$transport = $node_schema->getClient()->getTransport();
		$transport->pushResponse(200, array(), array('columns' => array('count(n)'), 'data' => array(array(1))));

		$count = $node_schema->getCollectionCount(array());
		$this->assertEquals(1, $count);

		$last_request = $transport->popRequest();
		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (n:Person) RETURN count(n)'
			)),
			$last_request
		);
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollection_throwsExceptionIfPropertyIsUnknown($node_schema) {
		$this->setExpectedException('InvalidArgumentException', 'Unknown property "name".');
		$node_schema->getCollection(array('name' => 'Keanu Reeves'));
	}
}
