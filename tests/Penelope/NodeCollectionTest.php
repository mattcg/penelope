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
use Karwana\Penelope\NodeCollection;

class NodeCollectionTest extends \PHPUnit_Framework_TestCase {

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
	public function testGetCollectionCount_returnsCount($node_schema) {
		$transport = $node_schema->getClient()->getTransport();
		$transport->pushResponse(200, array(), array('columns' => array('count(o)'), 'data' => array(array(100))));

		$collection = new NodeCollection($node_schema);
		$count = $collection->getTotalCount();
		$this->assertEquals(100, $count);

		$last_request = $transport->popRequest();
		$this->assertEquals(array('method' => 'POST', 'path' => 'cypher', 'data' => array('query' => 'MATCH (o:Person) RETURN count(o)')), $last_request);
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollection_returnsCollection($node_schema) {
		$transport = $node_schema->getClient()->getTransport();

		$transport->pushResponse(200, array(), array('Person'));
		$transport->pushResponse(200, array(), array('Person'));
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

		$collection = new NodeCollection($node_schema);
		$collection->fetch();

		$this->assertCount(2, $collection);

		$this->assertInstanceOf('Karwana\Penelope\Node', $collection[0]);
		$this->assertInstanceOf('Karwana\Penelope\Node', $collection[1]);

		$this->assertEquals(1, $collection[0]->getId());
		$this->assertEquals(2, $collection[1]->getId());

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/node/2/labels',
			'data' => null), $transport->popRequest());

		$this->assertEquals(array(
			'method' => 'GET',
			'path' => '/node/1/labels',
			'data' => null), $transport->popRequest());

		$last_request = $transport->popRequest();
		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (o:Person) RETURN (o)'
			)),
			$last_request
		);
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollection_returnsOrderedCollection($node_schema) {
		$transport = $node_schema->getClient()->getTransport();

		$transport->pushResponse(200, array(), array('Person'));
		$transport->pushResponse(200, array(), array('Person'));
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
		$collection = new NodeCollection($node_schema);
		$collection->fetch();
		$this->assertCount(2, $collection);

		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (o:Person) RETURN (o) ORDER BY o.name, o.born'
			)), $transport->popRequest());

		$this->assertNull($transport->popRequest());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollection_returnsPagedCollectionWithSkipAndLimitSet($node_schema) {
		$transport = $node_schema->getClient()->getTransport();

		$transport->pushResponse(200, array(), array('Person'));
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

		$collection = new NodeCollection($node_schema);
		$collection->setPage(2);
		$collection->setPageSize(1);
		$collection->fetch();

		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (o:Person) RETURN (o) SKIP 1 LIMIT 1'
			)), $transport->popRequest());

		$this->assertNull($transport->popRequest());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollection_returnsSearchResults($node_schema) {
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

		$node_schema->defineProperty('name');

		$collection = new NodeCollection($node_schema);
		$collection->setProperties(array('name' => 'Keanu Reeves'));
		$collection->fetch();

		$this->assertCount(1, $collection);

		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (o:Person) WHERE ANY (m IN {value_0} WHERE m IN o.name) RETURN (o)',
				'params' => array('value_0' => 'Keanu Reeves')
			)), $transport->popRequest());

		$this->assertNull($transport->popRequest());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollectionCount_returnsSearchCount($node_schema) {
		$transport = $node_schema->getClient()->getTransport();
		$transport->pushResponse(200, array(), array('columns' => array('count(o)'), 'data' => array(array(1))));

		$node_schema->defineProperty('name');

		$collection = new NodeCollection($node_schema);
		$collection->setProperties(array('name' => 'Keanu Reeves'));
		$count = $collection->getTotalCount();

		$this->assertEquals(1, $count);

		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (o:Person) WHERE ANY (m IN {value_0} WHERE m IN o.name) RETURN count(o)',
				'params' => array('value_0' => 'Keanu Reeves')
			)),
			$transport->popRequest()
		);

		$this->assertNull($transport->popRequest());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollectionCount_returnsCountForEmptySearch($node_schema) {
		$transport = $node_schema->getClient()->getTransport();
		$transport->pushResponse(200, array(), array('columns' => array('count(o)'), 'data' => array(array(1))));

		$collection = new NodeCollection($node_schema);
		$count = $collection->getTotalCount();

		$this->assertEquals(1, $count);

		$this->assertEquals(array(
			'method' => 'POST',
			'path' => 'cypher',
			'data' => array(
				'query' => 'MATCH (o:Person) RETURN count(o)'
			)),
			$transport->popRequest()
		);

		$this->assertNull($transport->popRequest());
	}


	/**
	 * @dataProvider nodeSchemaProvider
	 */
	public function testGetCollection_throwsExceptionIfPropertyIsUnknown($node_schema) {
		$collection = new NodeCollection($node_schema);

		$this->setExpectedException('InvalidArgumentException', 'Unknown property "name".');

		$collection->setProperties(array('name' => 'Keanu Reeves'));
	}
}
