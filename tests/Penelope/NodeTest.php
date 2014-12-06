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
use Karwana\Penelope\Node;

class NodeTest extends \PHPUnit_Framework_TestCase {

	private static $schema, $client;

	public static function setUpBeforeClass() {
		$client = static::$client = new Neo4j\Client('localhost', 7474);
		$schema = static::$schema = new Schema($client);

		$schema->addNode('TEST_PERSON', 'test-person');
		$schema->addEdge('TEST_FRIEND', 'test-friend', 'TEST_PERSON', 'TEST_PERSON');

		$schema->addNode('TEST_CAR', 'test-car');
		$schema->addEdge('TEST_OWNER', 'test-owner', 'TEST_CAR', 'TEST_PERSON');
	}

	public function testGetPath_throwsForNodeWithNoId() {
		$this->setExpectedException('LogicException', 'Cannot create path for node with no ID.');
		$node = static::$schema->getNode('TEST_PERSON')->create();
		$node->getPath();
	}

	public function testGetPath_returnsPath() {
		$node = static::$schema->getNode('TEST_PERSON')->create();
		$node->save();
		$this->assertEquals('/test-person/' . $node->getId(), $node->getPath());
	}

	public function testGetEditPath_throwsForNodeWithNoId() {
		$this->setExpectedException('LogicException', 'Cannot create edit path for node with no ID.');
		$node = static::$schema->getNode('TEST_PERSON')->create();
		$node->getEditPath();
	}

	public function testGetEditPath_returnsPath() {
		$node = static::$schema->getNode('TEST_PERSON')->create();
		$node->save();
		$this->assertEquals('/test-person/' . $node->getId() . '/edit', $node->getEditPath());
	}

	public function testGetNewEdgePath_throwsForNodeWithNoId() {
		$this->setExpectedException('LogicException', 'Cannot create new edge path for node with no ID.');
		$node = static::$schema->getNode('TEST_PERSON')->create();
		$node->getNewEdgePath(static::$schema->getEdge('TEST_FRIEND'));
	}

	public function testGetNewEdgePath_throwsForUnrelatableSchema() {
		$this->setExpectedException('LogicException', 'Cannot create new edge path for unrelatable node.');
		$node = static::$schema->getNode('TEST_PERSON')->create();
		$node->save();
		$node->getNewEdgePath(static::$schema->getEdge('TEST_OWNER'));
	}

	public function testGetNewEdgePath_returnsPath() {
		$node = static::$schema->getNode('TEST_PERSON')->create();
		$node->save();
		$this->assertEquals('/test-person/' . $node->getId() . '/test-friend/new', $node->getNewEdgePath(static::$schema->getEdge('TEST_FRIEND')));
	}

	public function testGetEdgeCollectionPath_throwsForNodeWithNoId() {
		$this->setExpectedException('LogicException', 'Cannot create edge collection path for node with no ID.');
		$node = static::$schema->getNode('TEST_PERSON')->create();
		$node->getEdgeCollectionPath(static::$schema->getEdge('TEST_FRIEND'));
	}

	public function testGetEdgeCollectionPath_throwsForUnrelatableSchema() {
		$this->setExpectedException('LogicException', 'Cannot create edge collection path for unrelatable node.');
		$node = static::$schema->getNode('TEST_PERSON')->create();
		$node->save();
		$node->getEdgeCollectionPath(static::$schema->getEdge('TEST_OWNER'));
	}

	public function testGetEdgeCollectionPath_returnsPath() {
		$node = static::$schema->getNode('TEST_PERSON')->create();
		$node->save();
		$this->assertEquals('/test-person/' . $node->getId() . '/test-friend/', $node->getEdgeCollectionPath(static::$schema->getEdge('TEST_FRIEND')));
	}

	public function testFetch_throwsForNodeWithNoId() {
		$this->setExpectedException('LogicException', 'Cannot fetch without ID.');
		$node = static::$schema->getNode('TEST_PERSON')->create();
		$node->fetch();
	}

	public function testFetch_throwsForNodeWithUnknownId() {
		$this->setExpectedException('Karwana\Penelope\Exceptions\NotFoundException', 'No node with ID "' . PHP_INT_MAX . '"');
		$node = static::$schema->getNode('TEST_PERSON')->get(PHP_INT_MAX);
	}

	public function testFetch_throwsForNodeWithMismatchingId() {
		$car_node = static::$schema->getNode('TEST_CAR')->create();
		$car_node->save();

		$this->setExpectedException('Karwana\Penelope\Exceptions\SchemaException', 'Node with ID "' . $car_node->getId() . '" exists, but does not match schema "TEST_PERSON".');

		$person_node = static::$schema->getNode('TEST_PERSON')->get($car_node->getId());
	}

	public function testFetch_returnsClientNode() {
		$car_node = static::$schema->getNode('TEST_CAR')->create();
		$car_node->save();

		$id = $car_node->getId();

		$car_node = new Node(static::$schema->getNode('TEST_CAR'), $id);
		$client_object = $car_node->fetch();

		$this->assertInstanceOf('Everyman\\Neo4j\Node', $client_object);
		$this->assertEquals($id, $client_object->getId());
	}

	public function testGetOutEdges_throwsForInvalidSchema() {
		$this->setExpectedException('Karwana\Penelope\Exceptions\SchemaException', 'The schema for edges of type "TEST_FRIEND" does not permit edges from nodes of type "TEST_CAR".');

		$car_node = static::$schema->getNode('TEST_CAR')->create();
		$friend_edge_schema = static::$schema->getEdge('TEST_FRIEND');

		$car_node->getOutEdges($friend_edge_schema);
	}

	public function testGetInEdges_throwsForInvalidSchema() {
		$this->setExpectedException('Karwana\Penelope\Exceptions\SchemaException', 'The schema for edges of type "TEST_FRIEND" does not permit edges to nodes of type "TEST_CAR".');

		$car_node = static::$schema->getNode('TEST_CAR')->create();
		$friend_edge_schema = static::$schema->getEdge('TEST_FRIEND');

		$car_node->getInEdges($friend_edge_schema);
	}

	public function testGetEdges_throwsForInvalidSchema() {
		$this->setExpectedException('Karwana\Penelope\Exceptions\SchemaException', 'The schema for edges of type "TEST_FRIEND" does not permit edges to or from nodes of type "TEST_CAR".');

		$car_node = static::$schema->getNode('TEST_CAR')->create();
		$friend_edge_schema = static::$schema->getEdge('TEST_FRIEND');

		$car_node->getEdges($friend_edge_schema);
	}

	public function testGetEdges_returnsEdges() {
		$person_schema = static::$schema->getNode('TEST_PERSON');

		$person_a = $person_schema->create();
		$person_b = $person_schema->create();

		$person_a->save();
		$person_b->save();

		$friend_edge_schema = static::$schema->getEdge('TEST_FRIEND');

		$friend_edge = $friend_edge_schema->create();
		$friend_edge->setRelationship($person_a, $person_b);
		$friend_edge->save();

		// Recreate the object from scratch.
		$node = new Node($person_schema, $person_a->getId());

		$edges = $node->getEdges($friend_edge_schema);
		$this->assertCount(1, $edges);

		$edge = $edges[0];
		$this->assertEquals('TEST_FRIEND', $edge->getSchema()->getName());
		$this->assertEquals($person_a->getId(), $edge->getStartNode()->getId());
		$this->assertEquals($person_b->getId(), $edge->getEndNode()->getId());
	}

	public function testGetEdges_throwsOnInvalidDirection() {
		$direction = '💩';

		$this->setExpectedException('RuntimeException', 'Invalid direction: "' . $direction . '".');

		$edge_schema = static::$schema->getEdge('TEST_FRIEND');
		static::$schema->getNode('TEST_PERSON')->create()->getEdges($edge_schema, $direction);
	}

	public function testSave_savesNode() {
		$node_a = static::$schema->getNode('TEST_PERSON')->create();

		$this->assertNull($node_a->getId());

		$node_a->save();

		$this->assertNotNull($node_a->getId());
		$this->assertInstanceOf('Everyman\\Neo4j\Node', static::$client->getNode($node_a->getId()));
	}

	public function testDelete_deletesNode() {
		$node_a = static::$schema->getNode('TEST_PERSON')->create();

		$node_a->save();

		$node_a_id = $node_a->getId();
		$this->assertNotNull($node_a_id);

		$node_a->delete();

		$this->assertNull($node_a->getId());
		$this->assertNull(static::$client->getNode($node_a_id));
	}

	public function testDelete_deletesNodeWithRelationship() {
		$node_schema = static::$schema->getNode('TEST_PERSON');

		// Create an edge.
		$edge = static::$schema->getEdge('TEST_FRIEND')->create();

		$this->assertNull($edge->getId());

		// Create the nodes.
		$node_a = $node_schema->create();
		$node_a->save();

		$this->assertNotNull($node_a->getId());

		$node_b = $node_schema->create();
		$node_b->save();

		$this->assertNotNull($node_b->getId());

		// Save the relationship.
		$edge->setRelationShip($node_a, $node_b);
		$edge->save();

		$this->assertNotNull($edge->getId());

		// Try deleting the nodes.
		$node_a->delete();
		$node_b->delete();
	}
}
