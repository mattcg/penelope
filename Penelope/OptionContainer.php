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

trait OptionContainer {

	public function hasDefault($name) {
		if (!isset(static::$defaults)) {
			return false;
		}

		return isset(static::$defaults[$name]);
	}

	public function getDefault($name) {
		if ($this->hasDefault($name)) {
			return static::$defaults[$name];
		}
	}

	public function hasOption($name) {
		return isset($this->options[$name]);
	}

	public function getOption($name) {
		if ($this->hasOption($name)) {
			return $this->options[$name];
		}

		return $this->getDefault($name);
	}

	public function setOption($name, $value) {
		$this->options[$name] = $value;
	}

	public function clearOption($name) {
		unset($this->options[$name]);
	}

	public function getOptions() {
		return $this->options;
	}

	public function setOptions(array $options = null) {
		$this->options = $options;
	}
}
