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

class EdgeSchema extends ObjectSchema {

	private $relationships;

	public function __construct($name, $slug, array $relationships, array $properties, array $options = null) {
		parent::__construct($name, $slug, $properties, $options);

		$this->relationships = $relationships;
	}

	public function canRelate($from_name, $to_name) {
		foreach ($this->relationships as &$relationship) {
			if ($from_name === $relationship[0] and $to_name === $relationships[1]) {
				return true;
			}
		}

		return false;
	}

	public function canRelateFrom($from_name) {
		foreach ($this->relationships as &$relationship) {
			if ($from_name === $relationship[1]) {
				return true;
			}
		}

		return false;
	}
}
