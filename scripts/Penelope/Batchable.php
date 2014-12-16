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

use Karwana\Penelope\ObjectCollection;

use Everyman\Neo4j;

abstract class Batchable {

	protected $batch_size = 10, $usleep = 500000;

	public function setBatchSize($batch_size) {
		$this->batch_size = $batch_size;
	}

	public function setInterval($usleep) {
		$this->usleep = $usleep;
	}

	public function run() {
		foreach ($this->getCollections() as $collection) {
			$this->processCollection($collection);
		}
	}

	public function processCollection(ObjectCollection $collection) {
		$page = 1;
		$collection->setPageSize($this->batch_size);
		$collection->fetch();

		while (!empty($collection)) {
			$this->processBatch($collection);
			usleep($this->usleep);

			$page++;
			$collection->setPage($page);
			$collection->fetch();
		}
	}

	public function processBatch(ObjectCollection $collection) {
		try {
			foreach ($collection as $object) {
				$this->process($object);
			}
		} catch (\Exception $e) {
			if (isset($object)) {
				throw new \Exception('Error while processing object ' . $object->getId() . ' (' . $object->getSchema()->getName() . ').', 0, $e);
			}

			throw $e;
		}
	}
}
