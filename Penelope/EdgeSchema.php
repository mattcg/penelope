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

class EdgeSchema extends ObjectSchema {

	private $end_schema, $start_schema;

	protected $path_formats = array('collection' => '/%s/%s/%s/', 'new' => '/%s/%s/%s/new', 'edit' => '/%s/%s/%s/%s/edit', 'object' => '/%s/%s/%s/%s', 'svg' => '/%s/%s/%s/%s/svg');

	public function __construct($name, $slug, NodeSchema $start_schema, NodeSchema $end_schema, array $properties = null, array $options = null) {
		$this->end_schema = $end_schema;
		$this->start_schema = $start_schema;
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

	public function getStartNodeSchema() {
		return $this->start_schema;
	}

	public function getEndNodeSchema() {
		return $this->end_schema;
	}

	public function envelopes(Neo4j\Relationship $client_edge) {
		return $client_edge->getType() === $this->getName();
	}

	public function permits($start_name, $end_name) {
		if ($this->permitsStartNode($start_name) and $this->permitsEndNode($end_name)) {
			return true;
		}

		return false;
	}

	public function permitsStartNode($start_name) {
		return $this->start_schema->getName() === $start_name;
	}

	public function permitsEndNode($end_name) {
		return $this->end_schema->getName() === $end_name;
	}

	public function getNewPath() {
		$node_slug = $this->start_schema->getSlug();
		return sprintf($this->getPathFormat('new'), $node_slug, ':node_id',  $this->getSlug());
	}

	public function getEditPath() {
		$node_slug = $this->start_schema->getSlug();
		return sprintf($this->getPathFormat('edit'), $node_slug, ':node_id',  $this->getSlug(), ':edge_id');
	}

	public function getPath() {
		$node_slug = $this->start_schema->getSlug();
		return sprintf($this->getPathFormat(), $node_slug, ':node_id',  $this->getSlug(), ':edge_id');
	}

	public function getSvgPath() {
		$node_slug = $this->start_schema->getSlug();
		return sprintf($this->getPathFormat('svg'), $node_slug, ':node_id',  $this->getSlug(), ':edge_id');
	}

	public function getCollectionPath() {
		$node_slug = $this->start_schema->getSlug();
		return sprintf($this->getPathFormat('collection'), $node_slug, ':node_id', $this->getSlug());
	}
}
