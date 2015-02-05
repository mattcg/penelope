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

namespace Karwana\Penelope\Controllers;

use Slim;
use Everyman\Neo4j;

use Karwana\Penelope\Schema;

class HomeController extends Controller {

	protected $client, $schema;

	public function __construct(Slim\Slim $app, Schema $schema, Neo4j\Client $client) {
		parent::__construct($app);

		$this->client = $client;
		$this->schema = $schema;
	}

	public function read() {
		$view_data = array('title' => $this->_m('home_title'));

		$view_data['node_schemas'] = $this->schema->getNodes();

		// Sort by name.
		usort($view_data['node_schemas'], function($a, $b) {
			return strcmp($a->getName(), $b->getName());
		});


		$view_data['node_totals'] = array();

		// Initialize totals array with zeroes.
		foreach ($view_data['node_schemas'] as $node_schema) {
			$view_data['node_totals'][$node_schema->getName()] = 0;
		}

		// Get label totals.
		$query = new Neo4j\Cypher\Query($this->client, 'MATCH n RETURN DISTINCT count(labels(n)), labels(n);');
		foreach ($query->getResultSet() as $row) {

			// The query result groups nodes with the same label. For example, a group of nodes will have `array('PERSON', 'DOCUMENT')` and another `array('PERSON')`.
			foreach ($row[1] as $label) {
				if (isset($view_data['node_totals'][$label])) {
					$view_data['node_totals'][$label] += $row[0];
				}
			}
		}

		$view_data['total_nodes'] = array_sum($view_data['node_totals']);

		$this->app->render('home', $view_data);
	}
}
