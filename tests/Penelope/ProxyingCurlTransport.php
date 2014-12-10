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

class ProxyingCurlTransport extends Neo4j\Transport\Curl {

	public function __construct($proxy_host = 'localhost', $proxy_port = 8888, $host = 'localhost', $port = 7474) {
		parent::__construct($host, $port);

		$curl_handle = $this->getHandle();

		curl_setopt($curl_handle, CURLOPT_PROXYPORT, $proxy_port);
		curl_setopt($curl_handle, CURLOPT_PROXY, $proxy_host);
	}
}
