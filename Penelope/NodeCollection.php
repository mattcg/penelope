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

class NodeCollection extends ObjectCollection {

	public function __construct(NodeSchema $node_schema, array $properties = null) {
		parent::__construct($node_schema, $properties);
	}

	protected function getResultSet($aggregate = null) {
		$query = parent::getQuery('MATCH (o:' . $this->schema->getName() . ')', array(), array(), $aggregate);

		return $query->getResultSet();
	}
}
