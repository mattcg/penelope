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

use Everyman\Neo4j;

use Karwana\Penelope\Schema;

class NodeTest extends \PHPUnit_Framework_TestCase {

	private static $schema, $client;

	public static function setUpBeforeClass() {
		$client = static::$client = new Neo4j\Client('localhost', 7474);
		$schema = static::$schema = new Schema($client);

		$schema->addNode('TEST', 'test-node', array('test-property'));
		$schema->addEdge('TEST_EDGE', 'test-edge', 'TEST', 'TEST', array('test-property'));
	}

	public function testGetPath_throwsForNodeWithNoId() {
		$this->setExpectedException('LogicException');
		$node = static::$schema->getNode('TEST')->create();
		$node->getPath();
	}

	public function testGetPath_returnsPath() {
		$node = static::$schema->getNode('TEST')->create();
		$node->save();
		$this->assertEquals('/test-node/' . $node->getId(), $node->getPath());
	}

	public function testSave_savesNode() {
		$node_a = static::$schema->getNode('TEST')->create();

		$this->assertNull($node_a->getId());

		$node_a->save();

		$this->assertNotNull($node_a->getId());
		$this->assertInstanceOf('Everyman\\Neo4j\Node', static::$client->getNode($node_a->getId()));
	}

	public function testDelete_deletesNode() {
		$node_a = static::$schema->getNode('TEST')->create();

		$node_a->save();

		$node_a_id = $node_a->getId();
		$this->assertNotNull($node_a_id);

		$node_a->delete();

		$this->assertNull($node_a->getId());
		$this->assertNull(static::$client->getNode($node_a_id));
	}

	public function testDelete_deletesNodeWithRelationship() {
		$node_schema = static::$schema->getNode('TEST');

		// Create an edge.
		$edge = static::$schema->getEdge('TEST_EDGE')->create();

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
