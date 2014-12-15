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

class NodeCollection extends ObjectCollection {

	private $properties, $page = 1, $page_size = 10;

	public function __construct(NodeSchema $node_schema, array $properties = null) {
		$this->properties = $properties;
		parent::__construct($node_schema);
	}

	public function setPageSize($page_size) {
		if (!is_int($page_size) or $page_size < 1) {
			throw new \InvalidArgumentException('Invalid page size "' . $page_size .'".');
		}

		$this->page_size = $page_size;
	}

	public function getPageSize() {
		return $this->page_size;
	}

	public function setPage($page) {
		if (!is_int($page) or $page < 1) {
			throw new \InvalidArgumentException('Invalid page "' . $page .'".');
		}

		$this->page = $page;
	}

	public function getPage() {
		return $this->page;
	}

	public function getTotalCount() {
		return (int) $this->query('count')[0][0];
	}

	public function fetch() {
		$this->client_objects[] = $this->query();
	}

	private function query($aggregate = null) {
		$query_string = 'MATCH (o:' . $this->schema->getName() . ')';
		$params = array();

		$i = 0;
		$query_parts = array();
		foreach ((array) $this->properties as $name => $value) {
			if (!$this->schema->hasProperty($name)) {
				throw new \InvalidArgumentException('Unknown property "' . $name . '".');
			}

			$params['value_' . $i] = $value;
			$query_parts[] = 'ANY (m IN {value_' . $i . '} WHERE m IN o.' . $name . ')';
			$i++;
		}

		if (!empty($query_parts)) {
			$query_string .=  ' WHERE ' . join(' AND ', $query_parts);
		}

		if ($aggregate) {
			$query_string .= ' RETURN ' . $aggregate . '(o)';
		} else {
			$query_string .= ' RETURN (o)';

			// Order by only makes sense when not using aggregate.
			$order_by = $this->schema->getOption('collection.order_by');
			if ($order_by) {
				$query_string .= ' ORDER BY o.' . join(', o.', (array) $order_by);
			}

			$limit = $this->page_size;

			if ($this->page > 1) {
				$skip = $this->page * $this->page_size;
			} else {
				$skip = 0;
			}

			// Aggregate client_objects would be unexpected when using limit.
			$query_string .= ' SKIP ' . $skip . ' LIMIT ' . $limit;
		}

		$client = $this->schema->getClient();
		$query = new Neo4j\Cypher\Query($client, $query_string, $params);

		return $query->getResultset();
	}
}
