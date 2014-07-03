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

class EdgeSchema extends ObjectSchema {

	private $to_node_schema, $from_node_schema;

	protected $path_formats = array('collection' => '/%/%s/%s/', 'new' => '/%s/%s/%s/new', 'edit' => '/%s/%s/%s/%s/edit', 'object' => '/%s/%s/%s/%s');

	public function __construct($name, $slug, NodeSchema $from_schema, NodeSchema $to_schema, array $properties, array $options = null) {
		$this->to_schema = $to_schema;
		$this->from_schema = $from_schema;
		parent::__construct($name, $slug, $properties, $options);
	}

	public function get(Neo4j\Client $client, $id, $fetch = true) {
		$edge = new Edge($this, $client, $id);

		// Preload data before returning.
		// NotFoundException will be thrown if:
		//  - the edge does not exist
		// SchemaException will be thrown if:
		//  - there's a mismatch between the requested edge and the given schema
		//  - the start node type doesn't match the edge schema
		//  - the end node type doesn't match the edge schema
		if ($fetch) {
			$edge->fetch();
		}

		return $edge;
	}

	public function getFromSchema() {
		return $this->from_schema;
	}

	public function getToSchema() {
		return $this->to_schema;
	}

	public function canRelate($from_name, $to_name) {
		if ($this->canRelateFrom($from_name) and $this->canRelateTo($to_name)) {
			return true;
		}

		return false;
	}

	public function canRelateFrom($from_name) {
		return $this->from_schema->getName() === $from_name;
	}

	public function canRelateTo($to_name) {
		return $this->to_schema->getName() === $to_name;
	}

	public function getNewPath() {
		$node_slug = $this->from_schema->getSlug();
		return sprintf($this->getPathFormat('new'), $node_slug, ':node_id',  $this->getSlug());
	}

	public function getEditPath() {
		$node_slug = $this->from_schema->getSlug();
		return sprintf($this->getPathFormat('edit'), $node_slug, ':node_id',  $this->getSlug(), ':edge_id');
	}

	public function getPath() {
		$node_slug = $this->from_schema->getSlug();
		sprintf($node_schema->getPathFormat(), $node_slug, ':node_id',  $this->getSlug(), ':edge_id');
	}

	public function getCollectionPath() {
		$node_slug = $this->from_schema->getSlug();
		return sprintf($this->getPathFormat('collection'), $node_slug, ':node_id', $this->getSlug());
	}
}
