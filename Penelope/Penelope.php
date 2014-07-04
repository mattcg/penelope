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
			$controller = new Controllers\HomeController($this->app, $this->schema);
			$controller->read();
		}, $this));

		// Set up the uploads route.
		$app->get('/uploads/:file_name', function($file_name) {
			$controller = new Controllers\UploadController($this->app);
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
			$controller->read($theme->getResourcePath($resource_type, $file_name));

		}, $this))->name($theme::ROUTE_NAME);
	}

	public function getTheme() {
		$view = $this->app->view();

		if ($view instanceof DefaultTheme) {
			return $view;
		}
	}

	public function defineEdge($name, $slug, $from_name, $to_name, array $properties = array(), array $options = null) {
		$edge_schema = $this->schema->addEdge($name, $slug, $from_name, $to_name, $properties, $options);
		$from_schema = $this->schema->getNode($from_name);

		$app = $this->app;

		// Factory middleware for creating the collection controller.
		$edges_middleware = Closure::bind(function() {
			$controller = new Controllers\EdgesController($this->app, $this->schema, $this->client);
			$this->app->controller = $controller;
		}, $this);

		$from_slug = $from_schema->getSlug();
		$edge_slug = $edge_schema->getSlug();

		$edges_path = $edge_schema->getCollectionPath();

		// Read a collection of edges coming from a node, by schema name.
		$app->get($edges_path, $edges_middleware, Closure::bind(function($node_id) use ($from_slug, $edge_slug) {
			$this->app->controller->read($from_slug, $node_id, $edge_slug);
		}, $this));

		// Get the form for creating a new edge within the collection.
		$app->get($edge_schema->getNewPath(), $edges_middleware, Closure::bind(function($node_id) use ($from_slug, $edge_slug) {
			$this->app->controller->renderNewForm($from_slug, $node_id, $edge_slug);
		}, $this));

		// Create a new edge within a collection of edges from a node, with the schema name given in the path.
		$app->post($edges_path, $edges_middleware, Closure::bind(function($node_id) use ($from_slug, $edge_slug) {
			$this->app->controller->create($from_slug, $node_id, $edge_slug);
		}, $this));

		// Factory middleware for creating the object controller.
		$edge_middleware = Closure::bind(function() {
			$controller = new Controllers\EdgeController($this->app, $this->schema, $this->client);
			$this->app->controller = $controller;
		}, $this);

		$edge_path = $edge_schema->getPath();

		// Read the edge, with the given schema name ID, coming from the node with the given schema name and ID.
		$app->get($edge_path, $edge_middleware, Closure::bind(function($node_id, $edge_id) use ($from_slug, $edge_slug) {
			$this->app->controller->read($from_slug, $node_id, $edge_slug, $edge_id);
		}, $this));

		// Delete the edge, with the given schema name ID, coming from the node with the given schema name and ID.
		$app->delete($edge_path, $edge_middleware, Closure::bind(function($node_id, $edge_id) use ($from_slug, $edge_slug) {
			$this->app->controller->delete($from_slug, $node_id, $edge_slug, $edge_id);
		}, $this));
	}

	public function defineNode($name, $slug, array $properties = array(), array $options = null) {
		$node_schema = $this->schema->addNode($name, $slug, $properties, $options);

		$app = $this->app;

		// Factory middleware for creating the collection controller.
		$nodes_middleware = Closure::bind(function() {
			$controller = new Controllers\NodesController($this->app, $this->schema, $this->client);
			$this->app->controller = $controller;
		}, $this);

		$node_slug = $node_schema->getSlug();
		$nodes_path = $node_schema->getCollectionPath();

		// Read a collection of nodes by schema name.
		$app->get($nodes_path, $nodes_middleware, Closure::bind(function() use ($node_slug) {
			$this->app->controller->read($node_slug);
		}, $this));

		// Create a new node within the collection.
		$app->post($nodes_path, $nodes_middleware, Closure::bind(function() use ($node_slug) {
			$this->app->controller->create($node_slug);
		}, $this));

		// Get the form for creating a new node within the collection.
		$app->get($node_schema->getNewPath(), $nodes_middleware, Closure::bind(function() use ($node_slug) {
			$this->app->controller->renderNewForm($node_slug);
		}, $this));

		// Factory middleware for creating the object controller.
		$node_middleware = Closure::bind(function() {
			$controller = new Controllers\NodeController($this->app, $this->schema, $this->client);
			$this->app->controller = $controller;
		}, $this);

		$node_path = $node_schema->getPath();

		// Read a node by schema name and ID.
		$app->get($node_path, $node_middleware, Closure::bind(function($node_id) use ($node_slug) {
			$this->app->controller->read($node_slug, $node_id);
		}, $this));

		// Update a node by schema name and ID.
		$app->put($node_path, $node_middleware, Closure::bind(function($node_id) use ($node_slug) {
			$this->app->controller->update($node_slug, $node_id);
		}, $this));

		// Delete a node by schema name and ID.
		$app->delete($node_path, $node_middleware, Closure::bind(function($node_id) use ($node_slug) {
			$this->app->controller->delete($node_slug, $node_id);
		}, $this));

		// Get the form for editing a node by schema name and ID.
		$app->get($node_schema->getEditPath(), $node_middleware, Closure::bind(function($node_id) use ($node_slug) {
			$this->app->controller->renderEditForm($node_slug, $node_id);
		}, $this));
	}
}
