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

class NodeSchema extends ObjectSchema {

	protected $path_formats = array('collection' => '/%s/', 'new' => '/%s/new', 'edit' => '/%s/%s/edit', 'object' => '/%s/%s');

	public function get($id) {
		$node = new Node($this, $id);

		// Preload data before returning.
		// NotFoundException will be thrown if:
		//  - the node does not exist
		// SchemaException will be thrown if:
		//  - there's a mismatch between the requested node and the given schema
		$node->fetch();

		return $node;
	}

	public function wrap(Neo4j\PropertyContainer $client_node = null) {
		return new Node($this, $client_node);
	}

	public function create() {
		return new Node($this);
	}

	public function getCollectionCount() {
		$query = $this->buildQuery(null, null, null, 'count');
		return (int) $query->getResultSet()[0][0];
	}

	public function getCollection($skip = null, $limit = null) {
		$query = $this->buildQuery(null, $skip, $limit);
		return $this->convertResultSet($query->getResultSet());
	}

	public function getCollectionSearchCount(array $properties) {
		$query = $this->buildQuery($properties, null, null, 'count');
		return (int) $query->getResultSet()[0][0];
	}

	public function searchCollection(array $properties, $skip = null, $limit = null) {
		$query = $this->buildQuery($properties, $skip, $limit);
		return $this->convertResultSet($query->getResultSet());
	}

	private function buildQuery(array $properties = null, $skip = null, $limit = null, $aggregate = null) {
		$query_string = 'MATCH (n:' . $this->getName() . ')';
		$params = array();

		$i = 0;
		$query_parts = array();
		foreach ((array) $properties as $name => $value) {
			if (!$this->hasProperty($name)) {
				throw new \InvalidArgumentException('Unknown property "' . $name . '".');
			}

			$params['value_' . $i] = $value;
			$query_parts[] = 'ANY (m IN {value_' . $i . '} WHERE m IN n.' . $name . ')';
			$i++;
		}

		if (!empty($query_parts)) {
			$query_string .=  ' WHERE ' . join(' AND ', $query_parts);
		}

		if ($aggregate) {
			$query_string .= ' RETURN ' . $aggregate . '(n)';
		} else {
			$query_string .= ' RETURN (n)';

			// Order by only makes sense when not using aggregate.
			$order_by = $this->getOption('collection.order_by');
			if ($order_by) {
				$query_string .= ' ORDER BY n.' . join(', n.', (array) $order_by);
			}

			// Aggregate results would be unexpected when using limit.
			if (is_int($skip) and $skip > 0) {
				$query_string .= ' SKIP ' . $skip;
			}

			if (is_int($limit) and $limit > 0) {
				$query_string .= ' LIMIT ' . $limit;
			}
		}

		return new Neo4j\Cypher\Query($this->client, $query_string, $params);
	}

	private function convertResultSet(Neo4j\Query\ResultSet $result_set) {
		$nodes = array();
		foreach ($result_set as $row) {
			$client_node = $row['n'];
			$nodes[] = $this->wrap($client_node);
		}

		return $nodes;
	}

	public function envelopes(Neo4j\Node $client_node) {
		$schema_name = $this->getName();

		// Check that the client node matches the schema.
		foreach ($client_node->getLabels() as $label) {
			if ($label->getName() === $schema_name) {
				return true;
			}
		}

		return false;
	}

	public function getNewPath() {
		return sprintf($this->getPathFormat('new'), $this->getSlug());
	}

	public function getEditPath() {
		return sprintf($this->getPathFormat('edit'), $this->getSlug(), ':node_id');
	}

	public function getPath() {
		return sprintf($this->getPathFormat(), $this->getSlug(), ':node_id');
	}

	public function getCollectionPath() {
		return sprintf($this->getPathFormat('collection'), $this->getSlug());
	}
}
