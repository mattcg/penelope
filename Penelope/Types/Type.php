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

use Karwana\Penelope\OptionContainer;
use Karwana\Penelope\Exceptions;

abstract class Type extends OptionContainer {

	protected $value;

	public function __construct($value = null, array $options = null) {
		parent::__construct($options);

		if (static::isEmpty($value)) {
			$this->value = null;
			return;
		}

		if (!static::isValid($value, $message)) {
			throw new Exceptions\TypeException($message);	
		}

		$this->value = $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function hasValue() {
		return !is_null($this->value);
	}

	public static function serialize($value) {
		if (static::isEmpty($value)) {
			return;
		}

		if (!is_scalar($value)) {
			$value = json_encode($value, JSON_BIGINT_AS_STRING);
			if (false === $value) {
				throw new Exceptions\TypeException(json_last_error_msg(), json_last_error());
			}
		}

		return $value;
	}

	public static function unserialize($value) {
		if (static::isEmpty($value)) {
			return;
		}

		if (!is_scalar($value)) {
			throw new Exceptions\TypeException('Cannot unserialize non-scalar value.');
		}

		return $value;
	}

	public static function isValid($value, &$message = null) {
		throw new \BadMethodCallException('Not implemented.');
	}

	public static function isEmpty($value) {
		if (is_null($value)) {
			return true;
		}

		if ('' === $value) {
			return true;
		}

		return false;
	}
}
