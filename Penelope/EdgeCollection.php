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

namespace Karwana\Penelope;

use Everyman\Neo4j;

class EdgeCollection extends ObjectCollection {

	const IN = Neo4j\Relationship::DirectionIn;
	const OUT = Neo4j\Relationship::DirectionOut;
	const ALL = Neo4j\Relationship::DirectionAll;

	private $node, $direction;

	public function __construct(EdgeSchema $edge_schema, Node $node, $direction = self::ALL, array $properties = null) {
		if (!$node->hasId()) {
			throw new \InvalidArgumentException('Cannot get an edge collection from a node with no ID.');
		}

		$node_schema = $node->getSchema();

		switch ($direction) {
		case self::OUT:
			if (!$edge_schema->permitsStartNode($node_schema)) {
				throw new Exceptions\SchemaException('The schema for edges of type "' . $edge_schema->getName() . '" does not permit edges from nodes of type "' . $node_schema->getName() . '".');
			}

			break;

		case self::IN:
			if (!$edge_schema->permitsEndNode($node_schema)) {
				throw new Exceptions\SchemaException('The schema for edges of type "' . $edge_schema->getName() . '" does not permit edges to nodes of type "' . $node_schema->getName() . '".');
			}

			break;

		case self::ALL:
			if (!$edge_schema->permitsEndNode($node_schema) and !$edge_schema->permitsStartNode($node_schema)) {
				throw new Exceptions\SchemaException('The schema for edges of type "' . $edge_schema->getName() . '" does not permit edges to or from nodes of type "' . $node_schema->getName() . '".');
			}

			break;

		default:
			throw new \RuntimeException('Invalid direction: "' . $direction . '".');
		}

		$this->node = $node;
		$this->direction = $direction;
		parent::__construct($edge_schema, $properties);
	}

	protected function getQueryMatch() {
		switch ($this->direction) {
		case self::ALL:
			$direction = '-[o]-';
			break;

		case self::OUT:
			$direction = '-[o]->';
			break;

		case self::IN:
			$direction = '<-[o]-';
			break;
		}

		return 'MATCH (n)' . $direction . '(' . $this->schema->getName() . ')';
	}

	protected function getQueryWhere(array &$query_params) {
		$query_where = parent::getQueryWhere($query_params);

		if (!$query_where) {
			$query_where = ' WHERE';
		}

		$query_params = array('node_id' => $this->node->getId());
		return $query_where . ' id(n) = {node_id}';
	}
}
