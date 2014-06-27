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

const VERSION = '1.0.0';

use Slim;
use Everyman\Neo4j;
use Exceptions\NotFoundException;

require_once __DIR__ . '/../vendor/autoload.php';

Slim\Route::setDefaultConditions(array(
	'node_id' => '\d',
	'edge_id' => '\d'
));

class Penelope {

	private $schema, $app, $client, $crud;

	public function __construct(Neo4j\Client $client, Slim\Slim $app, DefaultTheme $theme = null) {
		$this->app = $app;
		$this->client = $client;
		$this->schema = new Schema();

		if ($theme) {
			$this->setTheme($theme);
		}

		$this->crud = new Crud($this->schema, $client, $app);

		// Set up the home route.
		$penelope = $this;
		$app->get('/', function() use ($penelope) {
			$penelope->getCrud()->readHome();
		});
	}

	public function getClient() {
		return $this->client;
	}

	public function getSchema() {
		return $this->schema;
	}

	public function getCrud() {
		return $this->crud;
	}

	public function getApp() {
		return $this->app;
	}

	public function setTheme(DefaultTheme $theme) {
		$old_theme = $this->getTheme();
		$this->app->view($theme);

		$pattern = '/' . $theme::ROUTE_SLUG . '/:resource_type/:file';

		// Slim doesn't allow a named route to be removed or overwritten once added, so some trickery is needed to rename it.
		if ($old_theme) {
			$route = $this->app->router->getNamedRoute($old_theme::ROUTE_NAME);
			$route->setName($theme::ROUTE_NAME);
			$route->setPattern($pattern);
			return;
		}

		$penelope = $this;
		$this->app->get($pattern, function($resource_type, $file) use ($penelope) {
			$theme = $penelope->getTheme();

			// Pass if not an instance or child of the default theme, as DefaultTheme#renderResource won't be present.
			// In non-standard use cases, this allows the user to use a regular Slim\View as the view.
			if (!$theme) {
				$penelope->getApp()->pass();
			}

			try {
				$theme->renderResource($resource_type, $file);
			} catch (NotFoundException $e) {
				$penelope->getCrud()->render404($e);
			}

		})->name($theme::ROUTE_NAME);
	}

	public function getTheme() {
		$view = $this->app->view();

		if ($view instanceof DefaultTheme) {
			return $view;
		}
	}

	public function getEdge($name, $id) {
		$edge_schema = $this->schema->getEdge($name);
		$edge = new Edge($edge_schema, $this->client, $id);

		// Preload data before returning.
		// Exception will be thrown if the edge does not exist or if there's a schema mismatch.
		$edge->fetch();
		return $edge;
	}

	public function defineEdge($name, $slug, array $relationships, array $properties = array(), array $options = array()) {
		$schema = $this->schema->addEdge($name, $slug, $relationships, $properties);

		foreach ($relationships as $relationship) {
			$this->defineEdgeFrom($this->schema->getNode($relationship[0]), $schema);
		}
	}

	private function defineEdgeFrom(NodeSchema $node_schema, EdgeSchema $edge_schema) {
		$app = $this->app;
		$crud = $this->crud;
		$penelope = $this;

		$app->group('/' . $node_schema->getSlug() . '/:node_id/' . $edge_schema->getSlug(), function() use ($penelope, $app, $crud, $edge_schema) {

			$app->post('/', function() use ($crud, $app) {
				$crud->createEdge($app->node, $edge_schema);
			});

			$app->get('/', function() use ($crud, $app) {
				$crud->readEdges($app->node, $edge_schema);
			});

			$app->delete('/:edge_id', function() use ($crud, $app) {
				$route = $app->router()->getCurrentRoute();

				try {

					// Attempt to preload the edge specified by the ID in the URL.
					$edge = $penelope->getEdge($name, $route->getParam('edge_id'));
				} catch (NotFoundException $e) {
					$crud->render404($e);
					$app->stop();
				}

				$app->edge = $edge;

			}, function() use ($crud, $app) {
				$crud->deleteEdge($app->edge);
			});
		});
	}

	public function defineNode($name, $slug, array $properties = array(), array $options = array()) {
		$node_schema = $this->schema->addNode($name, $slug, $properties);

		$app = $this->app;
		$penelope = $this;

		$app->get($node_schema->getCollectionPath(), function() use ($penelope, $node_schema) {
			$penelope->getCrud()->readNodes($node_schema);
		});

		$app->post($node_schema->getCollectionPath(), function() use ($penelope, $node_schema) {
			$penelope->getCrud()->createNode($node_schema);
		});

		$app->get($node_schema->getNewPath(), function() use ($penelope, $node_schema) {
			$penelope->getCrud()->renderNewNodeForm($node_schema);
		});

		// Middleware to preload the node specified by the ID in the URL.
		$node_middleware = function($route) use ($penelope, $node_schema) {
			$node_id = $route->getParam('node_id');
			$app = $penelope->getApp();

			try {
				$node = $node_schema->get($penelope->getClient(), $node_id);
			} catch (NotFoundException $e) {
				$penelope->getCrud()->render404($e);
				$app->stop();
			}

			$app->node = $node;
		};

		$node_slug = $node_schema->getSlug();
		$node_path = sprintf($node_schema->getPathFormat(), $node_slug, ':node_id');
		$node_edit_path = sprintf($node_schema->getPathFormat('edit'), $node_slug, ':node_id');

		$app->get($node_path, $node_middleware, function() use ($penelope) {
			$penelope->getCrud()->readNode($penelope->getApp()->node);
		});

		$app->put($node_path, $node_middleware, function() use ($penelope) {
			$penelope->getCrud()->updateNode($penelope->getApp()->node);
		});

		$app->delete($node_path, $node_middleware, function() use ($penelope) {
			$penelope->getCrud()->deleteNode($penelope->getApp()->node);
		});

		$app->get($node_edit_path, $node_middleware, function() use ($penelope) {
			$penelope->getCrud()->renderEditNodeForm($penelope->getApp()->node);
		});
	}
}
