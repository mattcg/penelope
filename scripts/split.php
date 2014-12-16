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

use Karwana\Penelope\NodeSchema;
use Karwana\Penelope\Scripts\NodePropertySplitter;

function split_node(NodeSchema $node_schema, $property_name) {
	$splitter = new NodePropertySplitter($node_schema, $property_name);
	$splitter->run();
}

if (empty($argv[1])) {
	throw new \InvalidArgumentException('Missing initialization script path.');
}

if (empty($argv[2])) {
	throw new \InvalidArgumentException('Missing schema name.');
}

if (empty($argv[2])) {
	throw new \InvalidArgumentException('Missing property name.');
}

require_once __DIR__ . '/Penelope/NodePropertySplitter.php';
require_once $argv[1];

if (!isset($schema)) {
	$schema = $penelope->getSchema();
}

split_node($schema->getNode($argv[2]), $argv[3]);
