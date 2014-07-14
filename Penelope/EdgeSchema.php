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

	private $in_schema, $out_schema;

	protected $path_formats = array('collection' => '/%s/%s/%s/', 'new' => '/%s/%s/%s/new', 'edit' => '/%s/%s/%s/%s/edit', 'object' => '/%s/%s/%s/%s');

	public function __construct($name, $slug, NodeSchema $out_schema, NodeSchema $in_schema, array $properties, array $options = null) {
		$this->in_schema = $in_schema;
		$this->out_schema = $out_schema;
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

	public function getOutSchema() {
		return $this->out_schema;
	}

	public function getInSchema() {
		return $this->in_schema;
	}

	public function envelopes(Neo4j\Relationship $client_edge) {
		return $client_edge->getType() === $this->getName();
	}

	public function canRelate($from_name, $to_name) {
		if ($this->canRelateFrom($from_name) and $this->canRelateTo($to_name)) {
			return true;
		}

		return false;
	}

	public function canRelateFrom($from_name) {
		return $this->out_schema->getName() === $from_name;
	}

	public function canRelateTo($to_name) {
		return $this->in_schema->getName() === $to_name;
	}

	public function getNewPath() {
		$node_slug = $this->out_schema->getSlug();
		return sprintf($this->getPathFormat('new'), $node_slug, ':node_id',  $this->getSlug());
	}

	public function getEditPath() {
		$node_slug = $this->out_schema->getSlug();
		return sprintf($this->getPathFormat('edit'), $node_slug, ':node_id',  $this->getSlug(), ':edge_id');
	}

	public function getPath() {
		$node_slug = $this->out_schema->getSlug();
		return sprintf($this->getPathFormat(), $node_slug, ':node_id',  $this->getSlug(), ':edge_id');
	}

	public function getCollectionPath() {
		$node_slug = $this->out_schema->getSlug();
		return sprintf($this->getPathFormat('collection'), $node_slug, ':node_id', $this->getSlug());
	}
}
