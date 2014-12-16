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

namespace Karwana\Penelope\Scripts;

use Karwana\Penelope\Node;
use Karwana\Penelope\Schema;
use Karwana\Penelope\NodeCollection;

require_once __DIR__ . '/Batchable.php';

class Reindexer extends Batchable {

	private $schema;

	public function __construct(Schema $schema) {
		$this->schema = $schema;
	}

	public function getCollections() {
		$collections = array();

		foreach ($this->schema->getNodes() as $node_schema) {
			$collections[] = new NodeCollection($node_schema);
		}

		return $collections;
	}

	public function process(Node $node) {
		$node->index();
	}
}
