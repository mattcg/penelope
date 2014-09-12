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

	public function get(Neo4j\Client $client, $id, $fetch = true) {
		$node = new Node($this, $client, $id);

		// Preload data before returning.
		// NotFoundException will be thrown if:
		//  - the node does not exist
		// SchemaException will be thrown if:
		//  - there's a mismatch between the requested node and the given schema
		if ($fetch) {
			$node->fetch();
		}

		return $node;
	}

	public function getCollectionCount(Neo4j\Client $client) {
		$query = new Neo4j\Cypher\Query($client, 'MATCH (n) WHERE n:' . $this->getName() . ' RETURN count(n)');
		return (int) $query->getResultSet()[0][0];
	}

	public function getCollection(Neo4j\Client $client, $skip = null, $limit = null) {
		$query_string = 'MATCH (n) WHERE n:' . $this->getName() . ' RETURN n';

		$order_by = $this->getOption('collection.order_by');
		if ($order_by) {
			$query_string .= ' ORDER BY n.' . join(', n.', (array) $order_by);
		}

		if (is_int($skip) and $skip > 0) {
			$query_string .= ' SKIP ' . $skip;
		}

		if (is_int($limit) and $limit > 0) {
			$query_string .= ' LIMIT ' . $limit;
		}

		$query = new Neo4j\Cypher\Query($client, $query_string);
		$nodes = array();
		foreach ($query->getResultSet() as $row) {
			$client_node = $row['n'];
			$nodes[] = new Node($this, $client, $client_node);
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
