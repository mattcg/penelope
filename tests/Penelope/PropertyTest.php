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

use Karwana\Penelope\Property;
use Karwana\Penelope\PropertySchema;

class PropertyTest extends \PHPUnit_Framework_TestCase {

	public function propertyProvider() {
		$property_schema = new PropertySchema('name', 'text');
		$property = new Property($property_schema);

		return array(array($property));
	}

	public function multiValuePropertyProvider() {
		$property_schema = new PropertySchema('name', 'text', true);
		$property = new Property($property_schema);

		return array(array($property));
	}


	/**
	 * @dataProvider propertyProvider
	 */
	public function testGetSchema_returnsSchema($property) {
		$this->assertInstanceOf('Karwana\Penelope\PropertySchema', $property->getSchema());
	}


	/**
	 * @dataProvider propertyProvider
	 */
	public function testGetName_returnsName($property) {
		$this->assertEquals('name', $property->getName());
	}


	/**
	 * @dataProvider propertyProvider
	 */
	public function testSetValue_setsValue($property) {
		$this->assertFalse($property->hasValue());
		$this->assertNull($property->getValue());
		$this->assertNull($property->getSerializedValue());

		$property->setValue('Barack Obama');

		$this->assertTrue($property->hasValue());
		$this->assertEquals('Barack Obama', $property->getValue());
		$this->assertEquals('Barack Obama', $property->getSerializedValue());
	}


	/**
	 * @dataProvider multiValuePropertyProvider
	 */
	public function testSetValue_setsMultiValue($property) {
		$this->assertFalse($property->hasValue());
		$this->assertNull($property->getValue());
		$this->assertNull($property->getSerializedValue());

		$property->setValue(array('Barack Obama', 'Angela Merkel'));

		$this->assertTrue($property->hasValue());
		$this->assertEquals(array('Barack Obama', 'Angela Merkel'), $property->getValue());
		$this->assertEquals(array('Barack Obama', 'Angela Merkel'), $property->getSerializedValue());
	}


	/**
	 * @dataProvider propertyProvider
	 */
	public function testSetSerializedValue_setsSerializedValue($property) {
		$this->assertFalse($property->hasValue());
		$this->assertNull($property->getValue());
		$this->assertNull($property->getSerializedValue());

		$property->setSerializedValue('Barack Obama');

		$this->assertTrue($property->hasValue());
		$this->assertEquals('Barack Obama', $property->getValue());
		$this->assertEquals('Barack Obama', $property->getSerializedValue());
	}


	/**
	 * @dataProvider multiValuePropertyProvider
	 */
	public function testSetSerializedValue_setsSerializedMultiValue($property) {
		$this->assertFalse($property->hasValue());
		$this->assertNull($property->getValue());
		$this->assertNull($property->getSerializedValue());

		$property->setSerializedValue(array('Barack Obama', 'Angela Merkel'));

		$this->assertTrue($property->hasValue());
		$this->assertEquals(array('Barack Obama', 'Angela Merkel'), $property->getValue());
		$this->assertEquals(array('Barack Obama', 'Angela Merkel'), $property->getSerializedValue());
	}


	/**
	 * @dataProvider propertyProvider
	 */
	public function testClearValue_clearsValue($property) {
		$property->setValue('Barack Obama');

		$this->assertTrue($property->hasValue());
		$this->assertEquals('Barack Obama', $property->getValue());
		$this->assertEquals('Barack Obama', $property->getSerializedValue());

		$property->clearValue();

		$this->assertFalse($property->hasValue());
		$this->assertNull($property->getValue());
		$this->assertNull($property->getSerializedValue());
	}


	/**
	 * @dataProvider propertyProvider
	 */
	public function testSetValue_clearsValueWhenPassedNull($property) {
		$property->setValue('Barack Obama');

		$this->assertTrue($property->hasValue());
		$this->assertEquals('Barack Obama', $property->getValue());
		$this->assertEquals('Barack Obama', $property->getSerializedValue());

		$property->setValue(null);

		$this->assertFalse($property->hasValue());
		$this->assertNull($property->getValue());
		$this->assertNull($property->getSerializedValue());
	}


	/**
	 * @dataProvider propertyProvider
	 */
	public function testSetSerializedValue_clearsValueWhenPassedNull($property) {
		$property->setValue('Barack Obama');

		$this->assertTrue($property->hasValue());
		$this->assertEquals('Barack Obama', $property->getValue());
		$this->assertEquals('Barack Obama', $property->getSerializedValue());

		$property->setSerializedValue(null);

		$this->assertFalse($property->hasValue());
		$this->assertNull($property->getValue());
		$this->assertNull($property->getSerializedValue());
	}
}
