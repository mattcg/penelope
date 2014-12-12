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
use Karwana\Penelope\EdgeSchema;

use Karwana\Penelope\Edge;
use Karwana\Penelope\Node;

class EdgeTest extends \PHPUnit_Framework_TestCase {

	public function getSchema() {
		$transport = new MockTransport();
		$schema = new Schema(new Neo4j\Client($transport));
		$schema->addNode('Person', 'people');
		$schema->addNode('Car', 'cars');
		$schema->addEdge('OWNER', 'owns', 'Person', 'Car');

		return $schema;
	}

	public function edgeSchemaProvider() {
		$edge_schema = $this->getSchema()->getEdge('OWNER');
		return array(array($edge_schema));
	}

	public function edgeProvider() {
		$edge_schema = $this->getSchema()->getEdge('OWNER');
		return array(array($edge_schema->create()));
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testGetPath_throwsExceptionIfEdgeHasNoId($edge_schema) {
		$this->setExpectedException('LogicException', 'Cannot get path for edge with no ID.');
		$edge = new Edge($edge_schema);
		$edge->getPath();
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testGetEditPath_throwsExceptionIfEdgeHasNoId($edge_schema) {
		$this->setExpectedException('LogicException', 'Cannot get path for edge with no ID.');
		$edge = new Edge($edge_schema);
		$edge->getEditPath();
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testGetCollectionPath_throwsExceptionIfEdgeHasNoId($edge_schema) {
		$this->setExpectedException('LogicException', 'Cannot get path for edge with no ID.');
		$edge = new Edge($edge_schema);
		$edge->getCollectionPath();
	}


	/**
	 * @dataProvider edgeProvider
	 */
	public function testFetch_throwsExceptionIfEdgeHasNoId($edge) {
		$this->setExpectedException('LogicException', 'Cannot fetch without ID.');
		$edge->fetch();
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testFetch_throwsExceptionIfEdgeDoesNotExist($edge_schema) {
		$this->setExpectedException('Karwana\Penelope\Exceptions\NotFoundException', 'No edge with ID "1".');
		$edge = new Edge($edge_schema, 1);

		$transport = $edge->getClient()->getTransport();
		$transport->pushResponse(404, array(), array(
			'exception' => 'RelationshipNotFoundException',
			'fullname' => 'org.neo4j.server.rest.web.RelationshipNotFoundException',
		));

		$edge->fetch();

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/relationship/1',
			'data' => null), $transport->popRequest());

		// No more requests.
		$this->assertNull($transport->popRequest());
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testFetch_throwsExceptionIfEdgeDoesNotMatchSchema($edge_schema) {
		$this->setExpectedException('Karwana\Penelope\Exceptions\SchemaException', 'Edge with ID "1" exists, but does not match schema "OWNER".');
		$edge = new Edge($edge_schema, 1);

		$transport = $edge->getClient()->getTransport();
		$transport->pushResponse(200, array(), array(
			'start' => 'http://localhost:7474/db/data/node/1',
			'self' => 'http://localhost:7474/db/data/relationship/1',
			'type' => 'ACTED_IN',
			'end' => 'http://localhost:7474/db/data/node/2',
			'metadata' => array(
				'id' => 1,
				'type' => 'ACTED_IN'
			),
			'data' => array()
		));

		$client_edge = $edge->fetch();

		$this->assertEquals(1, $client_edge->getId());
		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/relationship/1',
			'data' => null), $transport->popRequest());

		// No more requests.
		$this->assertNull($transport->popRequest());
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testGetStartNode_returnsStartNode($edge_schema) {
		$edge = new Edge($edge_schema, 1);

		$transport = $edge->getClient()->getTransport();
		$transport->pushResponse(200, array(), array('Person'));
		$transport->pushResponse(200, array(), array('Car'));
		$transport->pushResponse(200, array(), array(
			'start' => 'http://localhost:7474/db/data/node/1',
			'self' => 'http://localhost:7474/db/data/relationship/1',
			'type' => 'OWNER',
			'end' => 'http://localhost:7474/db/data/node/2',
			'metadata' => array(
				'id' => 1,
				'type' => 'OWNER'
			),
			'data' => array()
		));

		$start_node = $edge->getStartNode();

		$this->assertEquals(1, $start_node->getId());

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/node/1/labels',
			'data' => null), $transport->popRequest());

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/node/2/labels',
			'data' => null), $transport->popRequest());

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/relationship/1',
			'data' => null), $transport->popRequest());

		// No more requests.
		$this->assertNull($transport->popRequest());
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testGetEndNode_returnsEndNode($edge_schema) {
		$edge = new Edge($edge_schema, 1);

		$transport = $edge->getClient()->getTransport();

		$transport->pushResponse(200, array(), array('Person'));
		$transport->pushResponse(200, array(), array('Car'));
		$transport->pushResponse(200, array(), array(
			'start' => 'http://localhost:7474/db/data/node/1',
			'self' => 'http://localhost:7474/db/data/relationship/1',
			'type' => 'OWNER',
			'end' => 'http://localhost:7474/db/data/node/2',
			'metadata' => array(
				'id' => 1,
				'type' => 'OWNER'
			),
			'data' => array()
		));

		$start_node = $edge->getEndNode();

		$this->assertEquals(2, $start_node->getId());

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/node/1/labels',
			'data' => null), $transport->popRequest());

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/node/2/labels',
			'data' => null), $transport->popRequest());

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/relationship/1',
			'data' => null), $transport->popRequest());

		// No more requests.
		$this->assertNull($transport->popRequest());
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testGetPath_returnsPath($edge_schema) {
		$edge = new Edge($edge_schema, 1);

		$transport = $edge->getClient()->getTransport();

		$transport->pushResponse(200, array(), array('Person'));
		$transport->pushResponse(200, array(), array('Car'));
		$transport->pushResponse(200, array(), array(
			'start' => 'http://localhost:7474/db/data/node/1',
			'self' => 'http://localhost:7474/db/data/relationship/1',
			'type' => 'OWNER',
			'end' => 'http://localhost:7474/db/data/node/2',
			'metadata' => array(
				'id' => 1,
				'type' => 'OWNER'
			),
			'data' => array()
		));

		$this->assertEquals('/people/1/owns/1', $edge->getPath());
		$this->assertEquals('/people/1/owns/1/edit', $edge->getEditPath());
		$this->assertEquals('/people/1/owns/', $edge->getCollectionPath());

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/node/1/labels',
			'data' => null), $transport->popRequest());

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/node/2/labels',
			'data' => null), $transport->popRequest());

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/relationship/1',
			'data' => null), $transport->popRequest());

		// No more requests.
		$this->assertNull($transport->popRequest());
	}
}
