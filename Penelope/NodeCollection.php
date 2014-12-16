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

	public function __construct(NodeSchema $node_schema) {
		parent::__construct($node_schema);
	}

	protected function getQuery($aggregate = null) {
		return $this->formatQuery('MATCH (o:' . $this->object_schema->getName() . ')', array(), $aggregate);
	}
}
