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

	protected function getQueryMatch() {
		return 'MATCH (n)-[o]->(' . $this->schema->getName() . ')';
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
