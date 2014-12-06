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

use Karwana\Penelope\Types\Datetime;

class DatetimeTest extends \PHPUnit_Framework_TestCase {

	public function testUnserialize_returnsNullForEmptyValue() {
		$this->assertNull(Datetime::unserialize(null));
		$this->assertNull(Datetime::unserialize(''));
	}

	public function testUnserialize_throwsExceptionForInvalidDatetime() {
		$this->setExpectedException('Karwana\Penelope\Exceptions\TypeException', 'Unable to convert "0" to a valid time.');
		Datetime::unserialize('0');
	}

	public function testUnserialize_handlesIntegers() {
		$this->assertEquals(1399161600, Datetime::unserialize(1399161600));
	}

	public function testUnserialize_handlesDates() {
		$this->assertEquals(-904348800, Datetime::unserialize('05/06/1941'));
		$this->assertEquals(1399161600, Datetime::unserialize('4 May 2014'));
	}

	public function testIsValid_returnsTrueForEmpty() {
		$this->assertTrue(Datetime::isValid(null));
		$this->assertTrue(Datetime::isValid(0));
		$this->assertTrue(Datetime::isValid(''));
	}

	public function testIsValid_returnsFalseForNonEmptyNonInteger() {
		$this->assertFalse(Datetime::isValid('1399161600', null, $message));
		$this->assertEquals('Invalid type received.', $message);
	}
}
