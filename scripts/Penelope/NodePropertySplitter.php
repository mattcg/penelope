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

namespace Karwana\Penelope\Scripts;

use Karwana\Penelope\Object;
use Karwana\Penelope\NodeSchema;
use Karwana\Penelope\NodeCollection;

use Everyman\Neo4j;

require_once __DIR__ . '/PropertySplitter.php';

class NodePropertySplitter extends PropertySplitter {

	public function __construct(NodeSchema $node_schema, $property_name) {
		parent::__construct($node_schema, $property_name);
	}

	public function getCollections() {
		return array(new NodeCollection($this->object_schema));
	}

	public function split(Object $node) {
		$value_split = parent::split($node);

		$query_string = 'MATCH (n) WHERE id(n) = ' . $node->getId() . ' SET n.`' . $this->property_name . '` = {value} RETURN n';
		$query_params = array('value' => $value_split);
		$query = new Neo4j\Cypher\Query($node->getClient(), $query_string, $query_params);

		$query->getResultSet();
	}
}
