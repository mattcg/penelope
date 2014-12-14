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

use Karwana\Penelope\Schema;
use Karwana\Penelope\NodeSchema;
use Karwana\Penelope\NodeCollection;

class Reindexer {

	public function reindex(Schema $schema, $batch = 10, $usleep = 500000) {
		foreach ($schema->getNodes() as $node_schema) {
			$this->reindexSchema($node_schema, $batch, $usleep);
		}
	}

	public function reindexSchema(NodeSchema $node_schema, $batch, $usleep) {
		$page = 1;
		$collection = new NodeCollection($node_schema);
		$collection->setPageSize($batch);
		$collection->fetch();

		while (!empty($collection)) {
			$this->reindexCollection($collection);
			usleep($usleep);

			$page++;
			$collection->setPage($page);
			$collection->fetch();
		}
	}

	public function reindexCollection(NodeCollection $collection) {
		try {
			foreach ($collection as $node) {
				$node->index();
			}
		} catch (\Exception $e) {
			throw new \Exception('Error while indexing ' . $node->getSchema()->getName() . ' node ' . $node->getId() . '.', 0, $e);
		}
	}
}
