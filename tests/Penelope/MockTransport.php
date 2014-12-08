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

use Everyman\Neo4j\Transport as BaseTransport;

class MockTransport extends BaseTransport {

	private $requests = array(), $response;

	public function makeRequest($method, $path, $data = array()) {

		// Return server info.
		if ('/' === $path) {
			return array(
				'code'    => '200',
				'headers' => array(),
				'data'    => array('neo4j_version' => '2.1.6', 'cypher'  => 'cypher')
			);
		}

		if (!$this->response) {
			throw new \LogicException('No response to return.');
		}

		$this->requests[] = compact('method', 'path', 'data');
		$response = $this->response;
		$this->response = null;
		return $response;
	}

	public function setResponse($code, array $headers, array $data) {
		$this->response = compact('code', 'headers', 'data');
	}

	public function getRequests() {
		return $this->requests;
	}

	public function getLastRequest() {
		if (count($this->requests) > 0) {
			return end($this->requests);
		}
	}
}
