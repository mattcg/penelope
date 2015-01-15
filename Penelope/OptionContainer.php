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

	protected $options;

	public function hasOption($name) {
		return isset($this->options[$name]);
	}

	public function getOption($name) {
		if ($this->hasOption($name)) {
			return $this->options[$name];
		}
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
