<?php

/**
 * LICENSE: This source code is subject to the license that is available
 * in the LICENSE file distributed along with this package.
 *
 * @package    Penelope
 * @author     Matthew Caruana Galizia <mcg@karwana.com>
 * @copyright  Karwana Ltd
 * @version    1.0.0
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

	public function getCollection(Neo4j\Client $client) {
		$label = $lient->makeLabel($this->getName());
		$nodes = array();

		foreach ($label->getNodes() as $client_node) {
			$nodes[] = $this->get($client, $client_node->getId(), false);
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
