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

class Node extends Object {

	public function getPath() {
		if (!$this->hasId()) {
			throw new \LogicException('Cannot create path for node with no ID.');
		}

		return preg_replace('/:node_id/', $this->getId(), $this->schema->getPath());
	}

	public function getEditPath() {
		if (!$this->hasId()) {
			throw new \LogicException('Cannot create edit path for node with no ID.');
		}

		return preg_replace('/:node_id/', $this->getId(), $this->schema->getEditPath());
	}

	public function getNewEdgePath(EdgeSchema $edge_schema) {
		if (!$this->hasId()) {
			throw new \LogicException('Cannot create new edge path for node with no ID.');
		}

		if (!$edge_schema->canRelateFrom($this->getSchema()->getName())) {
			throw new \LogicException('Cannot create new edge path for unrelatable node.');
		}

		return preg_replace('/:node_id/', $this->getId(), $edge_schema->getNewPath());
	}

	public function getEdgeCollectionPath(EdgeSchema $edge_schema) {
		if (!$this->hasId()) {
			throw new \LogicException('Cannot create edge collection path for node with no ID.');
		}

		if (!$edge_schema->canRelateFrom($this->getSchema()->getName())) {
			throw new \LogicException('Cannot create edge collection path for unrelatable node.');
		}

		return preg_replace('/:node_id/', $this->getId(), $edge_schema->getCollectionPath());
	}

	public function fetch() {
		if (is_null($this->id)) {
			throw new \LogicException('Cannot fetch without ID.');
		}

		$node = $this->client->getNode($this->id);
		if (!$node) {
			throw new Exceptions\NotFoundException('No node with ID "' . $this->id . '".');
		}

		// Check that the node given by the ID matches the schema.
		if (!$this->schema->envelopes($node)) {
			throw new Exceptions\SchemaException('Node with ID "' . $this->id . '" exists, but does not match schema "' . $this->schema->getName() . '".');
		}

		$this->object = $node;

		return $node;
	}

	public function getOutEdges(EdgeSchema $edge_schema) {
		if (!$edge_schema->canRelateFrom($this->schema->getName())) {
			throw new Exceptions\SchemaException('The schema for edges of type "' . $edge_schema->getName() . '" does not permit relationships from nodes of type "' . $this->schema->getName() . '".');
		}

		if (!$this->object) {
			$this->fetch();
		}

		// No need to worry about caching, as the Neo4j client takes care of this.
		$edges = array();
		$relationships = $this->object->getRelationships(array($edge_schema->getName()), Neo4j\Relationship::DirectionOut);

		foreach ($relationships as $relationship) {

			// Only include edges permitted by the schema.
			// Note that if one of the edges doesn't match the schema, this probably indicates that the database is in an error state.
			// In that case, trigger a notice.
			if ($edge_schema->getInSchema()->envelopes($relationship->getEndNode())) {
				$edges[] = $edge_schema->get($this->client, $relationship->getId());
			} else {
				trigger_error('Edge with ID "' . $relationship->getId() . '" has non-confirming relationship.');
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

		// Index the node.
		$index = new Neo4j\Index\NodeFulltextIndex($this->client, 'full_text');
		$full_text = implode(' ', array_map(function($property) {
			$value = $property->getSerializedValue();
			if (is_array($value)) {
				return implode(' ', $value);
			}

			return $value;
		}, $this->getProperties()));

		// Needs to be saved before anything is ever added, otherwise config errors will be thrown.
		// See: https://github.com/jadell/neo4jphp/issues/77
		$index->save();
		$index->add($this->object, 'full_text', $full_text);
		$index->save();
	}

	public function delete() {
		$node = $this->client->getNode($this->id);
		if (!$node) {
			throw new Exceptions\NotFoundException('Nonexistent node "' . $this->id . '".');
		}

		$index = new Neo4j\Index\NodeFulltextIndex($this->client, 'full_text');
		$index->remove($node);
		$index->save();

		$node->delete();
		$this->id = null;
		$this->object = null;
	}
}
