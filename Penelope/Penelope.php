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

use Closure;

use Slim;
use Everyman\Neo4j;

require_once __DIR__ . '/../vendor/autoload.php';

Slim\Route::setDefaultConditions(array(
	'node_id' => '\d+',
	'edge_id' => '\d+'
));

class Penelope extends OptionContainer {

	private $schema, $app, $client;

	public function __construct(Neo4j\Client $client, Slim\Slim $app, DefaultTheme $theme = null, array $options = null) {
		parent::__construct($options);

		$this->app = $app;
		$this->client = $client;
		$this->schema = new Schema();

		if ($theme) {
			$this->setTheme($theme);
		}

		// Set up the home route.
		$app->get('/', Closure::bind(function() {
			$controller = new Controllers\Home($this->app, $this->schema, $this->client);
			$controller->read();
		}, $this));

		// Set up the uploads route.
		$app->get('/uploads/:file_name', function($file_name) {
			$controller = new Controllers\Upload($this->app);
			$controller->read($file_name);
		});
	}

	public function getClient() {
		return $this->client;
	}

	public function getSchema() {
		return $this->schema;
	}

	public function getApp() {
		return $this->app;
	}

	public function setTheme(DefaultTheme $theme) {
		$old_theme = $this->getTheme();
		$this->app->view($theme);

		$pattern = '/' . $theme::ROUTE_SLUG . '/:resource_type/:file_name';

		// Slim doesn't allow a named route to be removed or overwritten once added, so some trickery is needed to rename it.
		if ($old_theme) {
			$route = $this->app->router->getNamedRoute($old_theme::ROUTE_NAME);
			$route->setName($theme::ROUTE_NAME);
			$route->setPattern($pattern);
			return;
		}

		$this->app->get($pattern, Closure::bind(function($resource_type, $file_name) {
			$theme = $this->getTheme();

			// Pass if not an instance or child of the default theme, as DefaultTheme#renderResource won't be present.
			// In non-standard use cases, this allows the user to use a regular Slim\View as the view.
			if (!$theme) {
				$this->getApp()->pass();
			}

			$controller = new Controllers\FileController($this->app);
			$controller->read($theme->getResourcePath($resource_type, $file));

		}, $this))->name($theme::ROUTE_NAME);
	}

	public function getTheme() {
		$view = $this->app->view();

		if ($view instanceof DefaultTheme) {
			return $view;
		}
	}

	public function defineEdge($name, $slug, array $relationships, array $properties = array(), array $options = array()) {
		$schema = $this->schema->addEdge($name, $slug, $relationships, $properties, $options);

		foreach ($relationships as $from_name => $to_names) {
			$this->defineEdgeFrom($this->schema->getNode($from_name), $schema);
		}
	}

	private function defineEdgeFrom(NodeSchema $node_schema, EdgeSchema $edge_schema) {
		$app = $this->app;

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
				} catch (Exceptions\NotFoundException $e) {
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
		$node_schema = $this->schema->addNode($name, $slug, $properties, $options);

		$app = $this->app;

		// Factory middleware for creating the collection controller.
		$nodes_middleware = Closure::bind(function() {
			$controller = new Controllers\NodesController($this->app, $this->schema, $this->client);
			$this->app->controller = $controller;
		}, $this);

		$nodes_slug = $node_schema->getSlug();
		$nodes_path = $node_schema->getCollectionPath();

		$app->get($nodes_path, $nodes_middleware, Closure::bind(function() use ($nodes_slug) {
			$this->app->controller->read($nodes_slug);
		}, $this));

		$app->post($nodes_path, $nodes_middleware, Closure::bind(function() use ($nodes_slug) {
			$this->app->controller->create($nodes_slug);
		}, $this));

		$app->get($node_schema->getNewPath(), $nodes_middleware, Closure::bind(function() use ($nodes_slug) {
			$this->app->controller->renderNewForm($nodes_slug);
		}, $this));

		// Factory middleware for creating the object controller.
		$node_middleware = Closure::bind(function() {
			$controller = new Controllers\NodeController($this->app, $this->schema, $this->client);
			$this->app->controller = $controller;
		}, $this);

		$node_slug = $node_schema->getSlug();
		$node_path = $node_schema->getPath();

		$app->get($node_path, $node_middleware, Closure::bind(function($node_id) use ($node_slug) {
			$this->app->controller->read($node_slug, $node_id);
		}, $this));

		$app->put($node_path, $node_middleware, Closure::bind(function($node_id) use ($node_slug) {
			$this->app->controller->update($node_slug, $node_id);
		}, $this));

		$app->delete($node_path, $node_middleware, Closure::bind(function($node_id) use ($node_slug) {
			$this->app->controller->delete($node_slug, $node_id);
		}, $this));

		$app->get($node_schema->getEditPath(), $node_middleware, Closure::bind(function($node_id) use ($node_slug) {
			$this->app->controller->renderEditForm($node_slug, $node_id);
		}, $this));
	}
}
