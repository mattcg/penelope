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

use Karwana\Penelope\Exceptions;

class Country extends Type {

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
		} catch (\Exception $e) {
			$country = null;
		}

		if (!$country) {
			throw new Exceptions\TypeException('Invalid code "' . $code . '".');
		}

		return $country['name'];
	}

	public static function unserialize($value) {
		if (static::isEmpty($value)) {
			return;
		}

		// Normalize code to uppercase.
		if (is_string($value)) {
			$value = strtoupper($value);
		}

		return $value;
	}

	public static function isValid($value, array $options = null, &$message = null) {
		if (static::isEmpty($value)) {
			return true;
		}

		try {
			$valid = (bool) ISO3166::getByAlpha3($value);
		} catch (\Exception $e) {}

		if (!$valid) {
			$message = 'The specified country code does not exist.';
			return false;
		}

		return true;
	}
}
