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

class Edge extends Object {

	private $from_node, $to_node;

	public function getPath() {
		if (!$this->hasId()) {
			throw new \LogicException('Cannot create path for edge with no ID.');
		}

		if (!$this->object) {

		}

		$path = $this->schema->getPath();
		$path = preg_replace('/:edge_id/', $this->getId(), $path);
		$path = preg_replace('/:node_id/', 'TK', $path);

		return $path;
	}

	public function getEditPath() {
		if (!$this->hasId()) {
			throw new \LogicException('Cannot create edit path for edge with no ID.');
		}

		return preg_replace('/:edge_id/', $this->getId(), $this->schema->getEditPath());
	}

	public function fetch() {
		if (!$this->hasId($this->id)) {
			throw new \LogicException('Cannot fetch without ID.');
		}

		$edge = $this->client->getRelationship($this->id);
		if (!$edge) {
			throw new Exceptions\NotFoundException('No edge with ID "' . $this->id . '".');
		}

		$schema_name = $this->schema->getName();
		if ($edge->getType() !== $schema_name) {
			throw new Exceptions\SchemaException('Edge with ID "' . $this->id . '" exists, but does not match schema "' . $schema_name . '".');
		}

		// Check that the edge's relationships are permitted by the schema.
		$edge_schema = $this->getSchema();

		$can_relate_from = false;
		foreach ($edge->getStartNode()->getLabels() as $label) {
			if ($edge_schema->canRelateFrom($label->getName())) {
				$can_relate_from = $label->getName();
				break;
			}
		}

		if (!$can_relate_from) {
			throw new Exceptions\SchemaException('Edge with ID "' . $this->id . '" exists, but has a relationship with a start node that does not match the schema "' . $schema_name . '".');
		}

		$can_relate_to = false;
		foreach ($edge->getEndNode()->getLabels() as $label) {
			if ($edge_schema->canRelateTo($label->getName())) {
				$can_relate_to = $label->getName();
				break;
			}
		}

		if (!$can_relate_to) {
			throw new Exceptions\SchemaException('Edge with ID "' . $this->id . '" exists, but has a relationship with an end node that does not match the schema "' . $schema_name . '".');
		}

		$this->object = $edge;

		return $edge;
	}

	public function getFromNode() {
		if (!$this->object) {
			$this->fetch();
		}

		return $this->from_node;
	}

	public function getToNode() {
		if (!$this->object) {
			$this->fetch();
		}

		return $this->to_node;
	}

	public function setRelationship(Node $from_node, Node $to_node) {
		$to_name = $to_node->schema->getName();
		$from_name = $from_node->schema->getName();

		if (!$this->schema->canRelate($from_name, $to_name)) {
			throw new Exceptions\SchemaException('Relationship between ' . $from_name . ' and ' . $to_name . ' forbidden by schema.');
		}

		$this->to_node = $to_node;
		$this->from_node = $from_node;
	}

	public function save() {

		// Make an edge if this edge is new.
		if (!$this->hasId($this->id)) {
			$edge = $this->client->makeRelationship();
			$edge->setType($this->schema->getName());
			$this->object = $edge;
		} else if (!$this->object) {
			$this->fetch();
		}

		$edge = $this->object;
		if ($this->from_node and $this->to_node) {
			$edge->setStartNode($this->from_node);
			$edge->setEndNode($this->to_node);
		}

		parent::save();
	}

	public function delete() {
		$edge = $this->client->getRelationship($this->id);
		if (!$edge) {
			throw new Exceptions\NotFoundException('Nonexistent edge "' . $this->id . '".');
		}

		$edge->delete();
		$this->id = null;
		$this->object = null;
	}
}
