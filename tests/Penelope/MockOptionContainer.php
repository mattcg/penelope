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

namespace Karwana\Penelope\Tests;

use Karwana\Penelope\OptionContainer;

class MockOptionContainer {

	use OptionContainer;

	public function __construct(array $options = null) {
		$this->setOptions($options);
	}
}
