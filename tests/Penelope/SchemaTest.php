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

class SchemaTest extends \PHPUnit_Framework_TestCase {

	public function getSchema() {
		$transport = new MockTransport();
		return new Schema(new Neo4j\Client($transport));
	}

	public function schemaProvider() {
		return array(array($this->getSchema()));
	}

	public function nodeSchemaProvider() {
		$schema = $this->getSchema();
		$node_schema = $schema->addNode('Person', 'people');
		return array(array($schema, $node_schema));
	}

	public function edgeSchemaProvider() {
		$schema = $this->getSchema();
		$schema->addNode('Person', 'people');
		$schema->addNode('Movie', 'movies');
		$edge_schema = $schema->addEdge('ACTS_IN', 'acts-in', 'Person', 'Movie');
		return array(array($schema, $edge_schema));
	}


	/**
	 * @dataProvider schemaProvider
	 */
	public function testAddNode_returnsNodeSchema($schema) {
		$node_schema = $schema->addNode('Person', 'people');
		$this->assertInstanceOf('Karwana\Penelope\NodeSchema', $node_schema);
		$this->assertEquals('Person', $node_schema->getName());
		$this->assertEquals('people', $node_schema->getSlug());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testAddNode_throwsExceptionWhenNameAlreadyInUse($schema, $node_schema) {
		$this->setExpectedException('InvalidArgumentException', 'Node name "Person" already in use.');
		$schema->addNode('Person', 'movies');
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testAddNode_throwsExceptionWhenSlugAlreadyInUse($schema, $node_schema) {
		$this->setExpectedException('InvalidArgumentException', 'Slug "people" already in use.');
		$schema->addNode('Movie', 'people');
	}


	/**
	 * @dataProvider schemaProvider
	 */
	public function testHasNodeWithSlug_returnsFalse($schema) {
		$this->assertFalse($schema->hasNodeWithSlug('people'));
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testHasNodeWithSlug_returnsTrue($schema, $node_schema) {
		$this->assertTrue($schema->hasNodeWithSlug('people'));
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetNodeBySlug_returnsNodeSchema($schema, $node_schema) {
		$node_schema = $schema->getNodeBySlug('people');
		$this->assertInstanceOf('Karwana\Penelope\NodeSchema', $node_schema);
		$this->assertEquals('Person', $node_schema->getName());
		$this->assertEquals('people', $node_schema->getSlug());
	}


	/**
	 * @dataProvider schemaProvider
	 */
	public function testGetNodeBySlug_throwsWhenSlugIsUnknown($schema) {
		$this->setExpectedException('InvalidArgumentException', 'Unknown node slug "people".');
		$schema->getNodeBySlug('people');
	}


	/**
	 * @dataProvider schemaProvider
	 */
	public function testHasNode_returnsFalse($schema) {
		$this->assertFalse($schema->hasNode('Person'));
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testHasNode_returnsTrue($schema, $node_schema) {
		$this->assertTrue($schema->hasNode('Person'));
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetNode_returnsNode($schema, $node_schema) {
		$node_schema = $schema->getNode('Person');
		$this->assertInstanceOf('Karwana\Penelope\NodeSchema', $node_schema);
		$this->assertEquals('Person', $node_schema->getName());
		$this->assertEquals('people', $node_schema->getSlug());
	}


	/**
	 * @dataProvider schemaProvider
	 */
	public function testGetNodeBySlug_throwsWhenNameIsUnknown($schema) {
		$this->setExpectedException('InvalidArgumentException', 'Unknown node definition "Person".');
		$schema->getNode('Person');
	}


	/**
	 * @dataProvider schemaProvider
	 */
	public function testGetNodes_returnsAllNodeSchemas($schema) {
		$this->assertCount(0, $schema->getNodes());
		$schema->addNode('Person', 'people');
		$node_schemas = $schema->getNodes();
		$this->assertCount(1, $node_schemas);
		$this->assertInstanceOf('Karwana\Penelope\NodeSchema', $node_schemas[0]);
		$this->assertEquals('Person', $node_schemas[0]->getName());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetByClientNode_returnsNodeSchema($schema, $node_schema) {
		$client = $schema->getClient();
		$transport = $client->getTransport();

		$client_node = new Neo4j\Node($client);
		$client_node->setId(1);
		$transport->setResponse(200, array(), array('Person'));
		$node_schema = $schema->getByClientNode($client_node);
		$this->assertEquals('Person', $node_schema->getName());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetByClientNode_returnsNull($schema, $node_schema) {
		$client = $schema->getClient();
		$transport = $client->getTransport();

		$client_node = new Neo4j\Node($client);
		$client_node->setId(1);
		$transport->setResponse(200, array(), array());
		$node_schema = $schema->getByClientNode($client_node);
		$this->assertNull($node_schema);
	}


	/**
	 * @dataProvider schemaProvider
	 */
	public function testAddEdge_returnsEdgeSchema($schema) {
		$schema = $this->getSchema();
		$schema->addNode('Person', 'people');
		$schema->addNode('Movie', 'movies');
		$edge_schema = $schema->addEdge('ACTS_IN', 'acts-in', 'Person', 'Movie');

		$this->assertInstanceOf('Karwana\Penelope\EdgeSchema', $edge_schema);
		$this->assertEquals('ACTS_IN', $edge_schema->getName());
		$this->assertEquals('acts-in', $edge_schema->getSlug());
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testAddEdge_throwsExceptionWhenNameAlreadyInUse($schema, $edge_schema) {
		$this->setExpectedException('InvalidArgumentException', 'Edge name "ACTS_IN" already in use.');
		$schema->addEdge('ACTS_IN', 'acts-in', 'Person', 'Move');
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testAddEdge_throwsExceptionWhenSlugAlreadyInUse($schema, $edge_schema) {
		$this->setExpectedException('InvalidArgumentException', 'Slug "acts-in" already in use.');
		$schema->addEdge('RATED', 'acts-in', 'Person', 'Movie');
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testHasEdgeWithSlug_returnsTrue($schema, $edge_schema) {
		$this->assertTrue($schema->hasEdgeWithSlug('acts-in'));
	}


	/**
	 * @dataProvider schemaProvider
	 */
	public function testHasEdgeWithSlug_returnsFalse($schema) {
		$this->assertFalse($schema->hasEdgeWithSlug('acts-in'));
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testGetEdgeBySlug_returnsEdgeSchema($schema, $edge_schema) {
		$edge_schema = $schema->getEdgeBySlug('acts-in');
		$this->assertEquals('ACTS_IN', $edge_schema->getName());
		$this->assertEquals('acts-in', $edge_schema->getSlug());
	}


	/**
	 * @dataProvider schemaProvider
	 */
	public function testGetEdgeBySlug_throwsExceptionForUnknownSlug($schema) {
		$this->setExpectedException('InvalidArgumentException', 'Unknown edge slug "friends".');
		$schema->getEdgeBySlug('friends');
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testHasEdge_returnsTrue($schema, $edge_schema) {
		$this->assertTrue($schema->hasEdge('ACTS_IN'));
	}


	/**
	 * @dataProvider schemaProvider
	 */
	public function testHasEdge_returnsFalse($schema) {
		$this->assertFalse($schema->hasEdge('ACTS_IN'));
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testGetEdge_returnsEdgeSchema($schema, $edge_schema) {
		$edge_schema = $schema->getEdge('ACTS_IN');
		$this->assertEquals('ACTS_IN', $edge_schema->getName());
		$this->assertEquals('acts-in', $edge_schema->getSlug());
	}


	/**
	 * @dataProvider schemaProvider
	 */
	public function testGetEdge_throwsExceptionForUnknownSlug($schema) {
		$this->setExpectedException('InvalidArgumentException', 'Unknown edge definition "FRIENDS".');
		$schema->getEdge('FRIENDS');
	}


	/**
	 * @dataProvider schemaProvider
	 */
	public function testGetEdges_returnsAllEdgeSchema($schema) {
		$this->assertCount(0, $schema->getEdges());

		$schema->addNode('Person', 'people');
		$schema->addNode('Movie', 'movies');
		$schema->addEdge('ACTS_IN', 'acts-in', 'Person', 'Movie');

		$edge_schemas = $schema->getEdges();
		$this->assertCount(1, $edge_schemas);
		$this->assertInstanceOf('Karwana\Penelope\EdgeSchema', $edge_schemas[0]);
		$this->assertEquals('ACTS_IN', $edge_schemas[0]->getName());
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testGetOutEdges_returnsAllOutEdges($schema, $edge_schema) {
		$out_edges = $schema->getOutEdges('Person');
		$this->assertCount(1, $out_edges);
		$this->assertEquals('ACTS_IN', $out_edges[0]->getName());
	}


	/**
	 * @dataProvider edgeSchemaProvider
	 */
	public function testGetOutEdges_returnsAllInEdges($schema, $edge_schema) {
		$out_edges = $schema->getInEdges('Movie');
		$this->assertCount(1, $out_edges);
		$this->assertEquals('ACTS_IN', $out_edges[0]->getName());
	}
}
