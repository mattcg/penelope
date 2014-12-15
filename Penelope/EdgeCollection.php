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

class EdgeCollection extends ObjectCollection {

	public function __construct(EdgeSchema $edge_schema, Node $node, array $properties = null) {
		if (!$node->hasId()) {
			throw new \InvalidArgumentException('Cannot get an edge collection from a node with no ID.');
		}

		$this->node = $node;
		parent::__construct($edge_schema, $properties);
	}

	protected function getResultSet($aggregate = null) {
		$query_parts = array('id(n) = {node_id}');
		$query_params = array('node_id' => $this->node->getId());

		$query = parent::getQuery('MATCH (n)-[o]->(' . $this->schema->getName() . ')', $query_parts, $query_params, $aggregate);

		return $query->getResultSet();
	}
}
