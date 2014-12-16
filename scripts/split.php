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

use Karwana\Penelope\Node;
use Karwana\Penelope\NodeSchema;
use Karwana\Penelope\Scripts\NodePropertyMapper;

function split_node_property(NodeSchema $node_schema, $property_name, $delimeter) {
	$mapper = new NodePropertyMapper($node_schema, $property_name, function(Node $node) use (&$mapper, $delimeter) {
		return $mapper->split($node, $delimeter);
	});

	$mapper->run();
}

if (empty($argv[1])) {
	throw new \InvalidArgumentException('Missing initialization script path.');
}

if (empty($argv[2])) {
	throw new \InvalidArgumentException('Missing schema name.');
}

if (empty($argv[3])) {
	throw new \InvalidArgumentException('Missing property name.');
}

if (empty($argv[4])) {
	throw new \InvalidArgumentException('Missing delimeter.');
}

require_once __DIR__ . '/Penelope/NodePropertyMapper.php';
require_once $argv[1];

if (!isset($schema)) {
	$schema = $penelope->getSchema();
}

split_node_property($schema->getNode($argv[2]), $argv[3], $argv[4]);
