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
}
