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
		$request = $this->app->request;

		$query = trim($request->get('q'));
		$view_data = array('query' => $query);

		if (!$query) {

			// Example: /search?p[countries_of_operation]=USA&p[first_name]=Arturo&s=PERSON
			$properties = $request->get('p');
			$schema_name = $request->get('s');
		}

		if ($query) {
			$view_data['title'] = $this->_m('search_title_no_q');
			$client_nodes = $this->queryFulltext($query);
		} else if (is_array($properties) and !empty($properties) and $schema_name) {
			$view_data['title'] = 'Custom search';
			$client_nodes = $this->queryProperties($schema_name, $properties);
		} else {
			$view_data['title'] = $this->_m('search_title_no_q');
		}

		if (!empty($client_nodes)) {

			// Map client node objects.
			$view_data['nodes'] = array();
			foreach ($client_nodes as $client_node) {

				// Cypher queries return Row objects. Index lookups return Nodes.
				if ($client_node instanceof Neo4j\Query\Row) {
					$client_node = $client_node[0];
				}

				$node_schema = $this->schema->getByClientNode($client_node);
				if ($node_schema) {
					$view_data['nodes'][] = new Node($node_schema, $this->client, $client_node);
				}
			}
		}

		if (!empty($view_data['nodes'])) {
			$view_data['result_count'] = count($view_data['nodes']);
		} else {
			$view_data['result_count'] = 0;
		}

		$this->app->render('search', $view_data);
	}

	private function queryProperties($schema_name, array $properties) {
		if (!$this->schema->hasNode($schema_name)) {
			return;
		}

		$node_schema = $this->schema->getNode($schema_name);

		$query = 'MATCH (n:' . $schema_name . ') WHERE ';
		$params = array();

		$i = 0;
		$query_parts = array();
		foreach ($properties as $name => $value) {
			if ($node_schema->hasProperty($name)) {
				$params['value_' . $i] = $value;
				$query_parts[] = 'ANY (m IN {value_' . $i . '} WHERE m IN n.' . $name . ')';
				$i++;
			}
		}

		if (empty($query_parts)) {
			return;
		}

		$query .=  join(' AND ', $query_parts) . ' RETURN n';

		$query = new Neo4j\Cypher\Query($this->client, $query, $params);
		return $query->getResultSet();
	}

	private function queryFulltext($query) {
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

		return $client_nodes;
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
