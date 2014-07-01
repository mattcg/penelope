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
use LogicException;

class Node extends Object {

	public function getPath() {
		if (!$this->hasId()) {
			throw new LogicException('Cannot create path for node with no ID.');
		}

		$format = $this->schema->getPathFormat();
		return sprintf($format, $this->schema->getSlug(), $this->getId());
	}

	public function getEditPath() {
		if (!$this->hasId()) {
			throw new LogicException('Cannot create edit path for node with no ID.');
		}

		$format = $this->schema->getPathFormat('edit');
		return sprintf($format, $this->schema->getSlug(), $this->getId());
	}

	public function fetch() {
		if (is_null($this->id)) {
			throw new LogicException('Cannot fetch without ID.');
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
		if ($is_new = !$this->hasId()) {
			$this->object = $this->client->makeNode();
		}

		parent::save();

		// Labels can only be added after the node is saved.
		if ($is_new) {
			$this->object->addLabels(array($this->client->makeLabel($this->schema->getName())));
		}
	}

	public function delete() {
		$node = $this->client->getNode($this->id);
		if (!$node) {
			throw new NotFoundException('Nonexistent node "' . $this->id . '".');
		}

		$node->delete();
		$this->id = null;
		$this->object = null;
	}
}
