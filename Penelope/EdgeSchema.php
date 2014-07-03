<?php

/**
 * LICENSE: This source code is subject to the license that is available
 * in the LICENSE file distributed along with this package.
 *
 * @package    Penelope
 * @author     Matthew Caruana Galizia <mcg@karwana.com>
 * @copyright  Karwana Ltd
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

namespace Karwana\Penelope;

class EdgeSchema extends ObjectSchema {

	private $relationships;

	protected $path_formats = array('collection' => '/%/%s/%s/', 'new' => '/%s/%s/%s/new', 'edit' => '/%s/%s/%s/%s/edit', 'object' => '/%s/%s');

	public function __construct($name, $slug, array $relationships, array $properties, array $options = null) {

		// Relationships format: array('person' => array('address', 'telephone_number'), 'telephone_number' => 'telecoms_company')
		$this->relationships = $relationships;
		parent::__construct($name, $slug, $properties, $options);
	}

	public function get(Neo4j\Client $client, $id, $fetch = true) {
		$edge = new Edge($edge_schema, $this->client, $id);

		// Preload data before returning.
		// NotFoundException will be thrown if:
		//  - the edge does not exist
		// SchemaException will be thrown if:
		//  - there's a mismatch between the requested edge and the given schema
		if ($fetch) {
			$edge->fetch();
		}

		return $edge;
	}

	public function canRelate($from_name, $to_name) {
		if ($this->canRelateFrom($from_name)) {
			return in_array($to_name, (array) $this->relationships[$from_name], true);
		}

		return false;
	}

	public function canRelateFrom($from_name) {
		return isset($this->relationships[$from_name]);
	}

	public function canRelateTo($to_name) {
		foreach ($this->relationships as $to_names) {
			if (in_array($to_name, (array) $to_names, true)) {
				return true;
			}
		}

		return false;
	}

	public function getNewPath() {
		return sprintf($this->getPathFormat('new'), ':node_slug', ':node_id',  $this->getSlug());
	}

	public function getEditPath() {
		return sprintf($this->getPathFormat('edit'), ':node_slug', ':node_id',  $this->getSlug(), ':edge_id');
	}

	public function getPath() {
		sprintf($node_schema->getPathFormat(), ':node_slug', ':node_id',  $this->getSlug(), ':edge_id');
	}

	public function getCollectionPath() {
		return sprintf($this->getPathFormat('collection'), ':node_slug', ':node_id', $this->getSlug());
	}
}
