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
			throw new Exceptions\SchemaException('Edge does not match schema "' . $edge_schema->getName() . '".');
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

		if (!$start_node->hasId()) {
			throw new \LogicException('Cannot create collection path from node with no ID.');
		}

		// Sanity check. Perhaps assert() is more appropriate here.
		if (!$this->schema->permitsStartNode($start_node->getSchema()->getName())) {
			throw new \LogicException('Cannot create collection path from unrelatable node.');
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
			throw new Exceptions\SchemaException('Edge with ID "' . $this->id . '" exists, but does not match schema "' . $this->schema->getName() . '".');
		}

		$this->client_object = $client_edge;
		$this->setStartAndEndNodes();

		return $client_edge;
	}

	private function setStartAndEndNodes() {

		// Attach the start and end nodes.
		// Implicitly checks that the edge's relationships are permitted by the schema.
		$edge_schema = $this->getSchema();
		$client_edge = $this->client_object;

		$this->end_node = $edge_schema->getEndNodeSchema()->wrap($client_edge->getEndNode());
		$this->start_node = $edge_schema->getStartNodeSchema()->wrap($client_edge->getStartNode());
	}

	public function getStartNode() {
		if (!$this->client_object) {
			$this->fetch();
		}

		return $this->start_node;
	}

	public function getEndNode() {
		if (!$this->client_object) {
			$this->fetch();
		}

		return $this->end_node;
	}

	public function setRelationship(Node $start_node, Node $end_node) {
		if ($start_node->getId() === $end_node->getId()) {
			throw new Exceptions\SchemaException('A node may not have an edge to itself.');
		}

		$to_name = $end_node->schema->getName();
		$from_name = $start_node->schema->getName();

		if (!$this->schema->permits($from_name, $to_name)) {
			throw new Exceptions\SchemaException('Relationship between ' . $from_name . ' and ' . $to_name . ' forbidden by schema.');
		}

		$this->end_node = $end_node;
		$this->start_node = $start_node;
	}

	public function save() {

		// Make an edge if this edge is new.
		if (!$this->hasId($this->id)) {
			$client_edge = $this->client->makeRelationship();
			$client_edge->setType($this->schema->getName());
			$this->client_object = $client_edge;
		} else if (!$this->client_object) {
			$this->fetch();
		}

		$client_edge = $this->client_object;
		$client_edge->setStartNode($this->start_node->getClientObject());
		$client_edge->setEndNode($this->end_node->getClientObject());

		parent::save();
	}
}
