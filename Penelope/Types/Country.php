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

namespace Karwana\Penelope\Types;

use Alcohol\ISO3166;
use Exception;
use InvalidArgumentException;

class Country extends Type {

	public function __construct($value = null, array $options = null) {

		// Normalize code to uppercase.
		$value = strtoupper($value);
		parent::__construct($value, $options);
	}

	public static function getCountries() {
		$countries = array();
		foreach (ISO3166::getAll() as $country) {
			$countries[] = array('name' => $country['name'], 'code' => $country['alpha3']);
		}

		return $countries;
	}

	public static function getCountryName($code) {
		try {
			$country = ISO3166::getByAlpha3($code);
		} catch (Exception $e) {
			$country = null;
		}

		if (!$country) {
			throw new InvalidArgumentException('Invalid code "' . $code . '".');
		}

		return $country['name'];
	}

	public static function validate($value) {
		try {
			$valid = (bool) ISO3166::getByAlpha3($value);
		} catch (Exception $e) {
			$valid = false;
		}

		return $valid;
	}
}
