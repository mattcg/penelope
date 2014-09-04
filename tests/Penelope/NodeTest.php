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

use Karwana\Penelope\Node;
use Karwana\Penelope\Edge;
use Karwana\Penelope\NodeSchema;
use Karwana\Penelope\EdgeSchema;

class NodeTest extends \PHPUnit_Framework_TestCase {

	private function getClient() {
		static $client;

		if (!$client) {
			$client = new Neo4j\Client('localhost', 7474);
		}

		return $client;
	}

	public function testSave_savesNode() {
		$node_schema = new NodeSchema('Test Node Schema', 'test-node-schema', array('test-property'));
		$node_a = new Node($node_schema, $this->getClient());

		$this->assertNull($node_a->getId());

		$node_a->save();

		$this->assertNotNull($node_a->getId());
		$this->assertInstanceOf('Everyman\\Neo4j\Node', $this->getClient()->getNode($node_a->getId()));
	}

	public function testDelete_deletesNode() {
		$node_schema = new NodeSchema('Test Node Schema', 'test-node-schema', array('test-property'));
		$node_a = new Node($node_schema, $this->getClient());

		$node_a->save();

		$node_a_id = $node_a->getId();
		$this->assertNotNull($node_a_id);

		$node_a->delete();

		$this->assertNull($node_a->getId());
		$this->assertNull($this->getClient()->getNode($node_a_id));
	}

	public function testDelete_deletesNodeWithRelationship() {

		$node_schema = new NodeSchema('Test Node Schema', 'test-node-schema', array('test-property'));

		// Create an edge.
		$edge_schema = new EdgeSchema('Test Edge Schema', 'test-edge-schema', $node_schema, $node_schema, array('test-property'));
		$edge = new Edge($edge_schema, $this->getClient());

		$this->assertNull($edge->getId());

		// Create the nodes.
		$node_a = new Node($node_schema, $this->getClient());
		$node_a->save();

		$this->assertNotNull($node_a->getId());

		$node_b = new Node($node_schema, $this->getClient());
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
