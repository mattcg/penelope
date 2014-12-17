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

	protected function getQueryString($aggregate = null, array &$query_params) {
		$query_string = 'MATCH (o:' . $this->object_schema->getName() . ')';

		$i = 0;
		foreach ((array) $this->properties as $name => $value) {
			$query_params['value_' . $i] = $value;
			$where_parts[] = 'ANY (m IN {value_' . $i . '} WHERE m IN o.' . $name . ')';
			$i++;
		}

		if (!empty($where_parts)) {
			$query_string .= ' WHERE ' . join(' AND ', $where_parts);
		}

		if ($aggregate) {
			$query_string .= ' RETURN ' . $aggregate . '(o)';
		} else {
			$query_string .= ' RETURN (o)';
		}

		// Order by only makes sense when not using aggregate.
		if (!$aggregate and ($order_by = $this->getOrderBy())) {
			$query_string .= ' ORDER BY o.' . join(', o.', (array) $order_by);
		}

		return $query_string;
	}
}
