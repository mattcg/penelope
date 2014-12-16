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

use Karwana\Penelope\Schema;
use Karwana\Penelope\Scripts\Reindexer;

function reindex(Schema $schema) {
	$reindexer = new Reindexer($schema);
	$reindexer->run();
}

if (empty($argv[1])) {
	throw new \InvalidArgumentException('Missing initialization script path.');
}

require_once __DIR__ . '/Penelope/Reindexer.php';
require_once $argv[1];

if (!isset($schema)) {
	$schema = $penelope->getSchema();
}

reindex($schema);
