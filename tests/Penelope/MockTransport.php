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

	private $requests = array(), $responses = array();

	public function makeRequest($method, $path, $data = array()) {

		// Return server info.
		if ('/' === $path) {
			return array(
				'code'    => '200',
				'headers' => array(),
				'data'    => array('neo4j_version' => '2.1.6', 'cypher'  => 'cypher')
			);
		}

		$this->requests[] = compact('method', 'path', 'data');

		if (empty($this->responses)) {
			throw new \LogicException('No response to return.');
		}

		$response = array_pop($this->responses);
		return $response;
	}

	public function pushResponse($code, array $headers = array(), array $data = array()) {
		$this->responses[] = compact('code', 'headers', 'data');
	}

	public function popRequest() {
		return array_pop($this->requests);
	}

	public function getRequests() {
		return $this->requests;
	}
}
