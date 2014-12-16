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

use Karwana\Penelope\ObjectCollection;

abstract class ObjectCollectionController extends ObjectController {

	protected function readPagedCollection(ObjectCollection $object_collection) {
		$request = $this->app->request;
		$object_schema = $object_collection->getSchema();

		// Check whether individual properties are being queried.
		// Example: ?countries_of_operation=USA&first_name=Arturo
		$properties = array();
		foreach ($request->get() as $name => $value) {
			if ($object_schema->hasProperty($name)) {
				$properties[$name] = $value;
			}
		}

		$page_size = 20;
		$page = (int) $request->get('p');
		if ($page < 1) {
			$page = 1;
		}

		$object_collection->setPage($page);
		$object_collection->setPageSize($page_size);
		$object_collection->setProperties($properties);

		$object_collection->fetch();

		$total = $object_collection->getTotalCount();

		// Return a 404 for invalid pages.
		if (count($object_collection) < 1 and $page > 1) {
			$this->app->notFound();
			$this->app->stop();
		}

		$view_data = array();
		$view_data['properties'] = $properties;

		if ($total and ($page_size * $page) < $total) {
			$view_data['next_page'] = $page + 1;
		} else {
			$view_data['next_page'] = 0;
		}

		if ($page > 1) {
			$view_data['prev_page'] = $page - 1;
		} else {
			$view_data['prev_page'] = 0;
		}

		return $view_data;
	}
}
