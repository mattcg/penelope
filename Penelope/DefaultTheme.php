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
use Karwana\MessageFormat\MessageFormat;

class DefaultTheme extends Slim\View {

	const ROUTE_NAME = 'resource';
	const ROUTE_SLUG = 'resources';

	protected $app, $resources = array(), $messageformat;

	public function __construct(Slim\Slim $app, MessageFormat $messageformat = null) {
		parent::__construct();

		$this->app = $app;
		$this->resources['css'] = array('default.css');
		$this->resources['js'] = array('default.js');

		if (!$messageformat) {
			$messageformat = new MessageFormat($this->getDefaultTemplatesDirectory() . DIRECTORY_SEPARATOR . '_lang', 'en');
		}

		$this->messageformat = $messageformat;

		$this->setTemplatesDirectory($this->getDefaultTemplatesDirectory());
	}

	public function getDefaultTemplatesDirectory() {
		return implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'themes', 'default'));
	}

	public function getResourcePath($resource_type, $file) {
		return $this->getTemplatePath(implode(DIRECTORY_SEPARATOR, array('resources', $resource_type, $file)));
	}

	public function getTemplatePath($file) {
		$relative_path = DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);
		$path = $this->getTemplatesDirectory() . $relative_path;

		// Allow fallback to default theme files if DefaultTheme is subclassed.
		if (!is_file($path)) {
			$path = $this->getDefaultTemplatesDirectory() . $relative_path;
		}

		return $path;
	}

	public function getTemplatePathname($file) {
		return $this->getTemplatePath($file);
	}

	public function getResourceUrl($resource_type, $file) {
		return $this->app->urlFor(static::ROUTE_NAME, array('resource_type' => $resource_type, 'file_name' => $file));
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

		$helpers_file = $this->getTemplatePath('helpers.php');
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

	public function _e($string) {
		return htmlspecialchars($string, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
	}

	public function _m($message_key) {
		$args = array_slice(func_get_args(), 1);

		// Escape each of the message arguments before formatting.
		foreach ($args as $i => $arg) {
			$args[$i] = $this->_e($arg);
		}

		return $this->messageformat->format($message_key, $args);
	}

	public function __($string) {
		echo $string;
	}

}
