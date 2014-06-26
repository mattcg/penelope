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

use Everyman\Neo4j;

class Node extends Object {

	public function fetch() {
		if (is_null($this->id)) {
			throw new \LogicException('Cannot fetch without ID.');
		}

		$node = $this->client->getNode($this->id);
		if (!$node) {
			throw new NotFoundException('No node with ID "' . $this->id . '".');
		}

		// Check that the node given by the ID matches the schema.
		$schema_name = $this->schema->getName();
		$found_schema = false;
		foreach ($node->getLabels() as $label) {
			if ($label->getName() === $schema_name) {
				$found_schema = true;
				break;
			}
		}

		if (!$found_schema) {
			throw new NotFoundException('Node with ID "' . $this->id . '" exists, but does not match schema "' . $schema_name . '".');
		}

		$this->object = $node;

		return $node;
	}

	public function getEdges(EdgeSchema $schema) {
		if (!$this->object) {
			$this->fetch();
		}

		// No need to worry about caching, as the Neo4j client takes care of this.
		$edges = array();
		$relationships = $this->object->getRelationships(array($schema->getName()), Neo4j\Relationship::DirectionOut);

		$name = $this->getName();

		foreach ($relationships as $relationship) {

			// Only include edges permitted by the schema.
			$to_names = $relationship->getEndNode()->getLabels();

			foreach ($to_names as $to_name) {
				if ($schema->canRelate($name, $to_name)) {
					$edge = new Edge($schema, $this->client, $relationship->getId());
					$edge->fetch();
					$edges[] = $edge;
					break;
				}
			}
		}

		return $edges;
	}

	public function save() {

		// Make a node if this node is new.
		if (is_null($this->id)) {
			$node = $this->client->makeNode();
			$this->object = $node;

		// Fetch the node if it has an ID but hasn't been fetched yet.
		} else if (!$this->object) {
			$node = $this->fetch();
		} else {
			$node = $this->object;
		}

		foreach ($this->properties as $property) {
			$node->setProperty($property->getName(), $property->getValue());
		}

		$node->save();

		// Labels can only be added after the node is saved.
		if (is_null($this->id)) {
			$node->addLabels(array($this->client->makeLabel($this->schema->getName())));
			$this->id = $node->getId();
		}
	}

	public function delete() {
		$node = $this->client->getNode($this->id);
		if (!$node) {

			// TODO: Use NotFoundException.
			throw new \RuntimeException('Nonexistent node "' . $this->id . '".');
		}

		$node->delete();
		$this->id = null;
		$this->object = null;
	}
}
