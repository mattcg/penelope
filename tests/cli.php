<?php

namespace Karwana\Penelope\Tests;

use Everyman\Neo4j;
use Psy\Shell;
use Psy\Configuration;

require __DIR__ . '/bootstrap.php';

$client = new Neo4j\Client(new ProxyingCurlTransport());

$shell = new Shell(new Configuration(array()));
$shell->setScopeVariables(array('client' => $client));
$shell->run();
