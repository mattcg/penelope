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

namespace Karwana\Penelope\Controllers;

use Karwana\Penelope\Exceptions;

class EdgeController extends ObjectController {

	private function getSchemaBySlugs($node_schema_slug, $edge_schema_slug) {
		return (new EdgesController($this->app, $this->schema, $this->client))->getSchemaBySlugs($node_schema_slug, $edge_schema_slug);
	}

	public function getByParams($node_schema_slug, $node_id, $edge_schema_slug, $edge_id) {
		$edge_schema = $this->getSchemaBySlugs($node_schema_slug, $edge_schema_slug);

		// Check that:
		// - the node schema with the given slug exists
		// - the node with the given ID exists
		$controller = new NodeController($this->app, $this->schema, $this->client);
		$node = $controller->getByParams($node_schema_slug, $node_id);

		try {
			$edge = $edge_schema->get($this->client, $edge_id);

		// If the edge with the given ID doesn't exist.
		} catch (Exceptions\NotFoundException $e) {
			$this->render404($e);
			$this->app->stop();

		// If the edge with the given ID exists, but:
		//  - it doesn't match the given schema
		//  - its related nodes don't match its own schema
		} catch (Exceptions\SchemaException $e) {
			$this->render404($e);
			$this->app->stop();
		}

		// If the node with the given ID is not the start node of the edge with the given ID.
		if ($edge->getFromNode()->getId() !== $node->getId()) {
			$this->render404(new Exceptions\NotFoundException('There is no edge from the given node.'));
			$this->app->stop();
		}

		return $edge;
	}

	private function getByParamsArray($params) {
		return call_user_func_array(array($this, 'getByParams'), $params);
	}

	public function read($node_schema_slug, $node_id, $edge_schema_slug, $edge_id) {
		$edge = $this->getByParamsArray(func_get_args());
		$edge->delete();
		$this->app->render('edge', array('title' => $edge->getTitle()));
	}

	public function delete($node_schema_slug, $node_id, $edge_schema_slug, $edge_id) {
		$edge = $this->getByParamsArray(func_get_args());
		$edge->delete();
		$this->app->render('edge_deleted', array('title' => 'Deleted ' . $edge->getTitle()));
	}
}
