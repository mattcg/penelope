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

	public function __construct(NodeSchema $node_schema, $id = null, Neo4j\Node $client_node = null) {
		parent::__construct($node_schema, $id, $client_node);

		// Check that the node given by the ID matches the schema.
		if ($client_node and !$node_schema->envelopes($client_node)) {
			throw new Exceptions\SchemaException('Node does not match schema "' . $node_schema->getName() . '".');
		}
	}

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

		if (!$edge_schema->permitsStartNode($this->getSchema()->getName())) {
			throw new \LogicException('Cannot create new edge path for unrelatable node.');
		}

		return preg_replace('/:node_id/', $this->getId(), $edge_schema->getNewPath());
	}

	public function getEdgeCollectionPath(EdgeSchema $edge_schema) {
		if (!$this->hasId()) {
			throw new \LogicException('Cannot create edge collection path for node with no ID.');
		}

		if (!$edge_schema->permitsStartNode($this->getSchema()->getName())) {
			throw new \LogicException('Cannot create edge collection path for unrelatable node.');
		}

		return preg_replace('/:node_id/', $this->getId(), $edge_schema->getCollectionPath());
	}

	public function fetch() {
		if (is_null($this->id)) {
			throw new \LogicException('Cannot fetch without ID.');
		}

		$client_node = $this->client->getNode($this->id);
		if (!$client_node) {
			throw new Exceptions\NotFoundException('No node with ID "' . $this->id . '".');
		}

		// Check that the node given by the ID matches the schema.
		if (!$this->schema->envelopes($client_node)) {
			throw new Exceptions\SchemaException('Node with ID "' . $this->id . '" exists, but does not match schema "' . $this->schema->getName() . '".');
		}

		$this->client_object = $client_node;

		return $client_node;
	}

	public function getOutEdges(EdgeSchema $edge_schema) {
		return $this->getEdges($edge_schema, Neo4j\Relationship::DirectionOut);
	}

	public function getInEdges(EdgeSchema $edge_schema) {
		return $this->getEdges($edge_schema, Neo4j\Relationship::DirectionIn);
	}

	public function getEdges(EdgeSchema $edge_schema, $direction = Neo4j\Relationship::DirectionAll) {
		if (Neo4j\Relationship::DirectionOut === $direction) {
			if (!$edge_schema->permitsStartNode($this->schema->getName())) {
				throw new Exceptions\SchemaException('The schema for edges of type "' . $edge_schema->getName() . '" does not permit edges from nodes of type "' . $this->schema->getName() . '".');
			}
		} else if (Neo4j\Relationship::DirectionIn === $direction) {
			if (!$edge_schema->permitsEndNode($this->schema->getName())) {
				throw new Exceptions\SchemaException('The schema for edges of type "' . $edge_schema->getName() . '" does not permit edges to nodes of type "' . $this->schema->getName() . '".');
			}
		} else if (Neo4j\Relationship::DirectionAll === $direction) {
			if (!$edge_schema->permitsEndNode($this->schema->getName()) and !$edge_schema->permitsStartNode($this->schema->getName())) {
				throw new Exceptions\SchemaException('The schema for edges of type "' . $edge_schema->getName() . '" does not permit edges to or from nodes of type "' . $this->schema->getName() . '".');
			}
		} else {
			throw new \RuntimeException('Invalid direction: "' . $direction . '".');
		}

		if (!$this->client_object) {
			$this->fetch();
		}

		// No need to worry about caching, as the Neo4j client takes care of this.
		$edges = array();
		$client_edges = $this->client_object->getRelationships(array($edge_schema->getName()), $direction);

		// TODO: Return an EdgeCollection that automatically wraps the client objects on access instead of looping and creating them all that once here.
		foreach ($client_edges as $client_edge) {

			// Only include edges permitted by the schema (checks are made within object fetching logic).
			// Note that if one of the edges doesn't match the schema, this probably indicates that the database is in an error state.
			// In that case, trigger a notice.
			try {
				$edges[] = $edge_schema->wrap($client_edge);
			} catch (Exceptions\SchemaException $e) {
				trigger_error('Edge with ID "' . $client_edge->getId() . '" of type "' . $client_edge->getType() . '" does not conform to schema.');
			}
		}

		return $edges;
	}

	public function save() {

		// Make a node if this node is new.
		if ($is_new = !$this->hasId()) {
			$this->client_object = $this->client->makeNode();
		}

		parent::save();

		// Labels can only be added after the node is saved.
		if ($is_new) {
			$this->client_object->addLabels(array($this->client->makeLabel($this->schema->getName())));
		}

		// Index the node.
		$index = new Neo4j\Index\NodeFulltextIndex($this->client, 'full_text');
		$full_text = implode(' ', array_filter(array_map(function($property) {

			// Some properties might be flagged for leaving out of the index (passwords, for example).
			if ($property->getSchema()->getOption('index.ignore')) {
				return false;
			}

			$value = $property->getSerializedValue();
			if (is_array($value)) {
				$value = implode(' ', $value);
			}

			// Strip punctuation. This is needed because given "John, Mary and Jane", Lucene will match "Mary" but not "John" (though it will match "John,").
			$value = preg_replace('/\pP\s?/u', ' ', $value);

			return $value;
		}, $this->getProperties())));

		// Needs to be saved before anything is ever added, otherwise config errors will be thrown.
		// See: https://github.com/jadell/neo4jphp/issues/77
		$index->save();
		$index->add($this->client_object, 'full_text', $full_text);
		$index->save();
	}

	public function delete() {
		$client_node = $this->getClientObject();

		// Remove the full text index.
		$index = new Neo4j\Index\NodeFulltextIndex($this->client, 'full_text');
		$index->remove($client_node);
		$index->save();

		// Orphan the node.
		$client_edges = $client_node->getRelationships();
		if ($client_edges) {
			foreach ($client_edges as $client_edge) {
				$client_edge->delete();
			}
		}

		parent::delete();
	}
}
