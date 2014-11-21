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
		$this->resources = array();

		if (!$messageformat) {
			$messageformat = new MessageFormat($this->getDefaultTemplatesDirectory() . DIRECTORY_SEPARATOR . '_lang', 'en');
		}

		$this->setMessageFormat($messageformat);
		$this->setTemplatesDirectory($this->getDefaultTemplatesDirectory());
	}

	public function getMessageFormat() {
		return $this->messageformat;
	}

	public function setMessageFormat(MessageFormat $messageformat) {
		$this->messageformat = $messageformat;
	}

	public function registerResource($resource_type, $resource_path) {

		// NOOP if the resource already exists.
		if (!isset($this->resources[$resource_type]) or !in_array($resource_path, $this->resources[$resource_type])) {
			$this->resources[$resource_type][] = $resource_path;
		}
	}

	public function getRegisteredResources() {
		return $this->resources;
	}

	public function hasResource($resource_path) {
		return file_exists($this->getResourcePath($resource_path));
	}

	public function getResourcePath($resource_path) {
		if (is_array($resource_path)) {
			$resource_path = implode(DIRECTORY_SEPARATOR, $resource_path);
		}

		return $this->getTemplatePath(implode(DIRECTORY_SEPARATOR, array('resources', $resource_path)));
	}

	public function getResourceUrl($resource_path) {
		return $this->app->urlFor(static::ROUTE_NAME, array('resource_path' => $resource_path));
	}

	public function getDefaultTemplatesDirectory() {
		return implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'themes', 'default'));
	}

	public function getTemplatePath($relative_path) {
		$relative_path = DIRECTORY_SEPARATOR . ltrim($relative_path, DIRECTORY_SEPARATOR);
		$path = $this->getTemplatesDirectory() . $relative_path;

		// Allow fallback to default theme files if DefaultTheme is subclassed.
		if (!is_file($path)) {
			$path = $this->getDefaultTemplatesDirectory() . $relative_path;
		}

		return $path;
	}

	public function getTemplatePathname($relative_path) {

		// Required to override Slim's own getTemplatePathname.
		return $this->getTemplatePath($relative_path);
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
		foreach ($resources as $resource_type => $resource_paths) {
			foreach ($resource_paths as $i => $resource_path) {
				$resources[$resource_type][$i] = $this->getResourceUrl($resource_path);
			}
		}

		if (false !== $this->data->get('bookends')) {
			$header = parent::render('header.php', compact('resources'));
			$footer = parent::render('footer.php');

			return $header . parent::render($template . '.php') . $footer;
		}

		return parent::render($template . '.php');
	}

	public function _e($string) {
		return htmlspecialchars($string, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
	}

	public function _a() {

		// Like _m, but strips tags from the resulting message for use in title attributes.
		$message = call_user_func_array(array($this, '_m'), func_get_args());
		return strip_tags($message);
	}

	public function _m($message_key) {
		$args = array_slice(func_get_args(), 1);

		// Escape each of the message arguments before formatting.
		foreach ($args as $i => $arg) {
			$args[$i] = $this->_e($arg);
		}

		// If the message key has no section, use the default.
		// The is to avoid having to repeat it in the default templates and controllers.
		if (false === strpos($message_key, '.')) {
			$message_key = 'penelope.' . $message_key;
		}

		return $this->messageformat->format($message_key, $args);
	}

	public function __($string) {
		echo $string;
	}

}
