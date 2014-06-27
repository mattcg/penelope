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

use Everyman\Neo4j;

class NodeSchema extends ObjectSchema {

	public function get(Neo4j\Client $client, $id, $fetch = true) {
		$node = new Node($this, $client, $id);

		// Preload data before returning.
		// Exception will be thrown if:
		//  - the node does not exist
		//  - there's a mismatch between the requested node and the given schema
		if ($fetch) {
			$node->fetch();
		}

		return $node;
	}

	public function getNewPath() {
		return sprintf($this->getPathFormat('new'), $this->getSlug());
	}

	public function getCollectionPath() {
		return sprintf($this->getPathFormat('collection'), $this->getSlug());
	}
}
