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

abstract class EdgeController extends ObjectController {

	private function getSchemaBySlug($schema_slug) {
		$controller = new EdgesController($this->app, $this->schema, $this->client);
		return $controller->getSchemaBySlug();
	}

	public function getByParams($node_schema_slug, $node_id, $edge_schema_slug, $edge_id) {
		$edge_schema = $this->getSchemaBySlug($edge_schema_slug);

		$controller = new NodeController($this->app, $this->schema, $this->client);
		$node = $controller->getByParams($node_schema_slug, $node_id);
		$node_schema = $node->getSchema()->getName();

		// Check if the relationship is allowed by the schema.
		if (!$edge_schema->canRelateFrom($node_schema->getName())) {
			$this->render404(new Exceptions\SchemaException('The schema for edges of type "' . $edge_schema->getName() . '" does not permit relationships from nodes of type "' . $node_schema->getName() . '".'));
			$this->app->stop();
		}

		try {
			$edge = $edge_schema->get($this->client, $edge_id);
		} catch (Exceptions\NotFoundException $e) {
			$this->render404($e);
			$this->app->stop();
		} catch (Exceptions\SchemaException $e) {
			$this->render404($e);
			$this->app->stop();
		}

		return $edge;
	}

	public function delete($node_schema_slug, $node_id, $edge_schema_slug, $edge_id) {
		$edge->getByParams($node_schema_slug, $node_id, $edge_schema_slug, $edge_id);
		$edge->delete();
		$this->app->render('edge_deleted', array('title' => 'Deleted ' . $edge->getTitle()));
	}
}
