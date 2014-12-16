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

require_once __DIR__ . '/PropertyMapper.php';

class NodePropertyMapper extends PropertyMapper {

	public function __construct(NodeSchema $node_schema, $property_name, \Closure $mapper) {
		parent::__construct($node_schema, $property_name, $mapper);
	}

	public function getCollections() {
		return array(new NodeCollection($this->object_schema));
	}

	public function map(Object $node) {
		$mapper = $this->mapper;

		$pair = $mapper($node);
		$property_name = key($pair);

		// Note that because this update bypasses `Node#save`, reindexing will have to be done manually after mapping.
		// This is by design, as you might want to perform multiple mappings and indexing is expensive, so it should be done last.
		$query_string = 'MATCH (n) WHERE id(n) = ' . $node->getId() . ' SET n.`' . $property_name . '` = {value}';

		// If a different property name is returned, remove it.
		if ($property_name !== $this->property_name) {
			$query_string = $query_string . ' REMOVE n.`' . $this->property_name . '`';
		}

		$query_string = $query_string . ' RETURN n';
		$query_params = array('value' => $pair[$property_name]);
		$query = new Neo4j\Cypher\Query($node->getClient(), $query_string, $query_params);

		$query->getResultSet();
	}
}
