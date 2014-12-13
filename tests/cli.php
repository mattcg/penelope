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

use Everyman\Neo4j;
use Psy\Shell;
use Psy\Configuration;

require __DIR__ . '/bootstrap.php';

$client = new Neo4j\Client(new ProxyingCurlTransport());

$shell = new Shell(new Configuration(array()));
$shell->setScopeVariables(array('client' => $client));
$shell->run();
