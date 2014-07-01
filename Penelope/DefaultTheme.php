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

use Slim;
use Negotiation\FormatNegotiator;

use Karwana\Penelope\Types\File;

class DefaultTheme extends Slim\View {

	const ROUTE_NAME = 'resource';
	const ROUTE_SLUG = 'resources';

	protected $app, $resources = array();

	public function __construct(Slim\Slim $app) {
		parent::__construct();

		$this->app = $app;
		$this->resources['css'] = array('default.css');
		$this->resources['js'] = array('default.js');

		$this->setTemplatesDirectory($this->getDefaultTemplatesDirectory());
	}

	public function getDefaultTemplatesDirectory() {
		return implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'themes', 'default'));
	}

	public function getResourcePathname($resource_type, $file) {
		return $this->getTemplatePathname(implode(DIRECTORY_SEPARATOR, array('resources', $resource_type, $file)));
	}

	public function getTemplatePathname($file) {
		$relative_path = DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);
		$path = $this->getTemplatesDirectory() . $relative_path;

		// Allow fallback to default theme files if DefaultTheme is subclassed.
		if (!is_file($path)) {
			$path = $this->getDefaultTemplatesDirectory() . $relative_path;
		}

		return $path;
	}

	public function getResourceUrl($resource_type, $file) {
		return $this->app->urlFor(static::ROUTE_NAME, array('resource_type' => $resource_type, 'file' => $file));
	}

	public function renderResource($resource_type, $file) {
		$path = $this->getResourcePathname($resource_type, $file);
		if (!is_file($path)) {
			throw new NotFoundException('The file "' . $file . '" does not exist.');
		}

		$response = $this->app->response;
		$response->headers->set('Content-Type', File::getMimeType($path));
		$response->setBody(file_get_contents($path));
	}

	public function render($template, $data = null) {
		$format = 'html';

		if (!empty($_SERVER['HTTP_ACCEPT'])) {
			$negotiator = new FormatNegotiator();
			$format = $negotiator->getBestFormat($_SERVER['HTTP_ACCEPT'], array('html', 'json'));
		}

		if ('json' === $format) {
			return json_encode($this->data->all());
		}

		$helpers_file = $this->getTemplatePathname('helpers.php');
		if (is_file($helpers_file)) {
			$app = $this->app;
			require_once $helpers_file;
		}

		$resources = $this->resources;
		foreach ($resources as $resource_type => $files) {
			foreach ($files as $i => $file) {
				$resources[$resource_type][$i] = $this->getResourceUrl($resource_type, $file);
			}
		}

		$header = parent::render('header.php', compact('resources'));
		$footer = parent::render('footer.php');

		return $header . parent::render($template . '.php') . $footer;
	}

	public function __() {
		$args = func_get_args();

		foreach ($args as $i => $arg) {
			$args[$i] = htmlspecialchars($arg, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
		}

		echo implode('', $args);
	}

}
