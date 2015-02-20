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

class Edge extends Object {

	private $start_node, $end_node;

	public function __construct(EdgeSchema $edge_schema, $id = null, Neo4j\Relationship $client_edge = null) {
		parent::__construct($edge_schema, $id, $client_edge);

		if ($client_edge and !$edge_schema->envelopes($client_edge)) {
			throw new Exceptions\SchemaException('Edge ' . $client_edge->getId() . ' does not match schema "' . $edge_schema->getName() . '".');
		}

		if ($client_edge) {
			$this->setStartAndEndNodes();
		}
	}

	private function formatPath($path) {
		if (!$this->hasId()) {
			throw new \LogicException('Cannot get path for edge with no ID.');
		}

		if (!$this->getStartNode()->hasId()) {
			throw new \LogicException('Cannot get path from node with no ID.');
		}

		$path = preg_replace('/:edge_id/', $this->getId(), $path);
		$path = preg_replace('/:node_id/', $this->getStartNode()->getId(), $path);

		return $path;
	}

	public function getPath() {
		return $this->formatPath($this->schema->getPath());
	}

	public function getEditPath() {
		return $this->formatPath($this->schema->getEditPath());
	}

	public function getCollectionPath() {
		$start_node = $this->getStartNode();

		if (!$start_node) {
			throw new \LogicException('Cannot get collection path for edge with no start node.');
		}

		if (!$start_node->hasId()) {
			throw new \LogicException('Cannot get collection path from node with no ID.');
		}

		$path = $this->schema->getCollectionPath();
		$path = preg_replace('/:node_id/', $start_node->getId(), $path);

		return $path;
	}

	public function fetch() {
		if (!$this->hasId($this->id)) {
			throw new \LogicException('Cannot fetch without ID.');
		}

		$client_edge = $this->client->getRelationship($this->id);
		if (!$client_edge) {
			throw new Exceptions\NotFoundException('No edge with ID "' . $this->id . '".');
		}

		if (!$this->schema->envelopes($client_edge)) {
			throw new Exceptions\SchemaException('Edge with ID "' . $this->id . '" does not match schema "' . $this->schema->getName() . '".');
		}

		$this->client_object = $client_edge;
		$this->setStartAndEndNodes();

		return $client_edge;
	}

	private function setStartAndEndNodes() {
		$edge_schema = $this->getSchema();
		$client_edge = $this->client_object;

		// Attach the start and end nodes.
		// Implicitly checks that the edge's relationships are permitted by the schema.
		$this->end_node = $edge_schema->getEndNodeSchema()->wrap($client_edge->getEndNode());
		$this->start_node = $edge_schema->getStartNodeSchema()->wrap($client_edge->getStartNode());
	}

	public function getStartNode() {
		if ($this->hasId() and !$this->client_object) {
			$this->fetch();
		}

		return $this->start_node;
	}

	public function getEndNode() {
		if ($this->hasId() and !$this->client_object) {
			$this->fetch();
		}

		return $this->end_node;
	}

	public function setRelationship(Node $start_node, Node $end_node) {
		if ($start_node->hasId() and $start_node->getId() === $end_node->getId()) {
			throw new Exceptions\SchemaException('A node may not have an edge to itself.');
		}

		if (!$this->schema->permits($start_node->getSchema(), $end_node->getSchema())) {
			throw new Exceptions\SchemaException('Relationship between ' . $start_node->getSchema()->getName() . ' and ' . $end_node->getSchema()->getName() . ' forbidden by schema.');
		}

		$this->end_node = $end_node;
		$this->start_node = $start_node;
	}

	public function save() {
		$start_node = $this->start_node;
		$end_node = $this->end_node;

		if (!$start_node) {
			throw new \LogicException('Cannot save an edge with no start node.');
		}

		if (!$end_node) {
			throw new \LogicException('Cannot save an edge with no end node.');
		}

		if (!$start_node->hasId()) {
			$start_node->save();
		}

		if (!$end_node->hasId()) {
			$end_node->save();
		}

		// Make an edge if this edge is new.
		if (!$this->hasId($this->id)) {
			$client_edge = $this->client->makeRelationship();
			$client_edge->setType($this->schema->getName());
			$this->client_object = $client_edge;
		} else if (!$this->client_object) {
			$this->fetch();
		}

		$client_edge = $this->client_object;
		$client_edge->setStartNode($start_node->getClientObject());
		$client_edge->setEndNode($end_node->getClientObject());

		parent::save();
	}
}
