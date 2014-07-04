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

class Schema {

	private $nodes = array(), $edges = array(), $node_slugs = array(), $edge_slugs = array();

	public function addNode($name, $slug, array $properties, array $options = null) {
		$schema = new NodeSchema($name, $slug, $properties, $options);
		$this->nodes[$name] = $schema;
		$this->node_slugs[$slug] = $name;

		return $schema;
	}

	public function getNodeBySlug($slug) {
		if (!isset($this->node_slugs[$slug])) {
			throw new \InvalidArgumentException('Unknown node slug "' . $slug . '".');
		}

		return $this->getNode($this->node_slugs[$slug]);
	}

	public function getNode($name) {
		if (!isset($this->nodes[$name])) {
			throw new \InvalidArgumentException('Unknown node definition "' . $name . '".');
		}

		return $this->nodes[$name];
	}

	public function getNodes() {
		return array_values($this->nodes);
	}

	public function getOutEdges($node_name) {
		$node_schema = $this->getNode($node_name);

		return array_filter($this->getEdges(), function($edge_schema) use ($node_schema) {
			return $edge_schema->canRelateFrom($node_schema->getName());
		});
	}

	public function addEdge($name, $slug, $from_name, $to_name, array $properties, array $options = null) {
		$schema = new EdgeSchema($name, $slug, $this->getNode($from_name), $this->getNode($to_name), $properties, $options);
		$this->edges[$name] = $schema;
		$this->edge_slugs[$slug] = $name;

		return $schema;
	}

	public function getEdgeBySlug($slug) {
		if (!isset($this->edge_slugs[$slug])) {
			throw new \InvalidArgumentException('Unknown edge slug "' . $slug . '".');
		}

		return $this->getEdge($this->edge_slugs[$slug]);
	}

	public function getEdge($name) {
		if (!isset($this->edges[$name])) {
			throw new \InvalidArgumentException('Unknown edge definition "' . $name . '".');
		}

		return $this->edges[$name];
	}

	public function getEdges() {
		return array_values($this->edges);
	}
}
