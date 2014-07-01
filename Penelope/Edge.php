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

	public function fetch() {
		if (is_null($this->id)) {
			throw new \LogicException('Cannot fetch without ID.');
		}

		$edge = $this->client->getRelationship($this->id);
		if (!$edge) {
			throw new NotFoundException('No edge with ID "' . $this->id . '".');
		}

		if ($edge->getType() !== $this->schema->getName()) {
			throw new NotFoundException('Edge with ID "' . $this->id . '" exists, but does not match schema "' . $schema_name . '".');
		}

		$this->object = $edge;

		return $edge;
	}

	public function setRelationship(Node $from_node, Node $to_node) {
		$from_name = $from_node->schema->getName();
		$to_name = $to_node->schema->getName();

		if (!$this->schema->canRelate($from_name, $to_name)) {
			throw new \InvalidArgumentException('Relationship between ' . $from_name . ' and ' . $to_name . ' forbidden by schema.'); // TODO: Use SchemaException.
		}

		$this->from_node = $from_node;
		$this->to_node = $to_node;
	}

	public function save() {

		// Make a node if this node is new.
		if (is_null($this->id)) {
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

			// TODO: Use NotFoundException.
			throw new \RuntimeException('Nonexistent edge "' . $this->id . '".');
		}

		$edge->delete();
		$this->id = null;
		$this->object = null;
	}
}
