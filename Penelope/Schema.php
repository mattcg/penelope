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

class Schema {

	private $nodes = array(), $edges = array(), $node_slugs = array(), $edge_slugs = array();

	public function addNode($name, $slug, array $properties = null, array $options = null) {
		if ($this->hasNode($name)) {
			throw new \InvalidArgumentException('Node name "' . $name . '" already in use.');
		}

		if ($this->hasNodeWithSlug($slug)) {
			throw new \InvalidArgumentException('Node slug "' . $slug . '" already in use.');
		}

		$schema = new NodeSchema($name, $slug, $properties, $options);
		$this->nodes[$name] = $schema;
		$this->node_slugs[$slug] = $name;

		return $schema;
	}

	public function hasNodeWithSlug($slug) {
		return isset($this->node_slugs[$slug]);
	}

	public function getNodeBySlug($slug) {
		if (!$this->hasNodeWithSlug($slug)) {
			throw new \InvalidArgumentException('Unknown node slug "' . $slug . '".');
		}

		return $this->getNode($this->node_slugs[$slug]);
	}

	public function hasNode($name) {
		return isset($this->nodes[$name]);
	}

	public function getNode($name) {
		if (!$this->hasNode($name)) {
			throw new \InvalidArgumentException('Unknown node definition "' . $name . '".');
		}

		return $this->nodes[$name];
	}

	public function getNodes() {
		return array_values($this->nodes);
	}

	public function getByClientNode(Neo4j\Node $client_node) {
		foreach ($client_node->getLabels() as $label) {
			if ($this->hasNode($label->getName())) {
				return $this->getNode($label->getName());
			}
		}
	}

	public function addEdge($name, $slug, $from_name, $to_name, array $properties = null, array $options = null) {
		if ($this->hasEdge($name)) {
			throw new \InvalidArgumentException('Edge name "' . $name . '" already in use.');
		}

		if ($this->hasEdgeWithSlug($slug)) {
			throw new \InvalidArgumentException('Edge slug "' . $slug . '" already in use.');
		}

		$schema = new EdgeSchema($name, $slug, $this->getNode($from_name), $this->getNode($to_name), $properties, $options);
		$this->edges[$name] = $schema;
		$this->edge_slugs[$slug] = $name;

		return $schema;
	}

	public function hasEdgeWithSlug($slug) {
		return isset($this->edge_slugs[$slug]);
	}

	public function getEdgeBySlug($slug) {
		if (!$this->hasEdgeWithSlug($slug)) {
			throw new \InvalidArgumentException('Unknown edge slug "' . $slug . '".');
		}

		return $this->getEdge($this->edge_slugs[$slug]);
	}

	public function hasEdge($name) {
		return isset($this->edges[$name]);
	}

	public function getEdge($name) {
		if (!$this->hasEdge($name)) {
			throw new \InvalidArgumentException('Unknown edge definition "' . $name . '".');
		}

		return $this->edges[$name];
	}

	public function getEdges() {
		return array_values($this->edges);
	}

	public function getOutEdges($node_name) {
		$node_schema = $this->getNode($node_name);

		return array_filter($this->getEdges(), function($edge_schema) use ($node_schema) {
			return $edge_schema->permitsStartNode($node_schema->getName());
		});
	}

	public function getInEdges($node_name) {
		$node_schema = $this->getNode($node_name);

		return array_filter($this->getEdges(), function($edge_schema) use ($node_schema) {
			return $edge_schema->permitsEndNode($node_schema->getName());
		});
	}
}
