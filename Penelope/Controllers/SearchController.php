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

use Closure;

use Slim;
use Everyman\Neo4j;

use Karwana\Penelope\Node;
use Karwana\Penelope\Schema;

class SearchController extends Controller {

	protected $client, $schema;

	public function __construct(Slim\Slim $app, Schema $schema, Neo4j\Client $client) {
		parent::__construct($app);

		$this->client = $client;
		$this->schema = $schema;
	}

	public function run() {
		$query = trim($this->app->request->get('q'));

		$view_data = array('query' => $query);

		if ($query) {
			$view_data['title'] = $this->_m('search_title', $query);
			$view_data['nodes'] = $this->query($query);
			$view_data['result_count'] = count($view_data['nodes']);
		} else {
			$view_data['title'] = $this->_m('search_title_no_q', $query);
			$view_data['result_count'] = 0;
		}

		$this->app->render('search', $view_data);
	}

	private function query($query) {
		$index = new Neo4j\Index\NodeFulltextIndex($this->client, 'full_text');

		// Index needs to be saved just in case this is the first time it's being used.
		// Otherwise config errors will be thrown by Neo4j.
		// See: https://github.com/jadell/neo4jphp/issues/77
		$index->save();

		$escaped_query = $this->escapeQuery($query);

		try {
			$client_nodes = $index->query('full_text:' . $escaped_query);
		} catch (Neo4j\Exception $e) {
			if ($e->getCode() === 404) {
				return;
			}

			throw $e;
		}

		return array_filter(array_map(Closure::bind(function($client_node) {
			$node_schema = $this->schema->getByClientNode($client_node);
			if ($node_schema) {
				return new Node($node_schema, $this->client, $client_node);
			}

			// PHP's array_filter will strip out all elements equal to FALSE.
			return false;
		}, $this), $client_nodes));
	}

	private function escapeQuery($query) {
		$escaped_query = '';

		// List taken from org.apache.lucene.queryparser.classic.QueryParserBase::escape.
		for ($i = 0, $l = strlen($query); $i < $l; $i++) {
			$c = $query[$i];

			if (false !== strpos('\\+-!():^[]{}~?|&/ "*', $c)) {
				$escaped_query .= '\\' . $c;
			} else {
				$escaped_query .= $c;
			}
		}

		return $escaped_query;
	}
}
