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

	protected static $defaults = array(
		'path.format.collection' => '/%s/',
		'path.format.new' => '/%s/new',
		'path.format.edit' => '/%s/%s/edit',
		'path.format.object' => '/%s/%s'
	);

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

	public function getCollection($page = null, $page_size = null, array $properties = null) {
		$node_collection = new NodeCollection($this);

		if ($page) {
			$node_collection->setPage($page);
		}

		if ($page_size) {
			$node_collection->setPageSize($page_size);
		}

		if ($properties) {
			$node_collection->setProperties($properties);
		}

		$node_collection->fetch();
		return $node_collection;
	}

	public function wrap(Neo4j\Node $client_node) {
		return new Node($this, $client_node->getId(), $client_node);
	}

	public function create() {
		return new Node($this);
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
		return sprintf($this->getOption('path.format.new'), $this->getSlug());
	}

	public function getEditPath() {
		return sprintf($this->getOption('path.format.edit'), $this->getSlug(), ':node_id');
	}

	public function getPath() {
		return sprintf($this->getOption('path.format.object'), $this->getSlug(), ':node_id');
	}

	public function getCollectionPath() {
		return sprintf($this->getOption('path.format.collection'), $this->getSlug());
	}
}
