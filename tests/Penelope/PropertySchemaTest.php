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

use Karwana\Penelope\PropertySchema;

class PropertySchemaTest extends \PHPUnit_Framework_TestCase {

	public function testConstructor_throwsExceptionIfNameIsEmpty() {
		$this->setExpectedException('InvalidArgumentException', 'Property name can not be empty.');
		new PropertySchema('', 'text');
	}

	public function testConstructor_throwsExceptionForInvalidType() {
		$this->setExpectedException('InvalidArgumentException', 'Unknown type "bogus".');
		new PropertySchema('test', 'bogus');
	}

	public function testGetName_returnsName() {
		$property_schema = new PropertySchema('test', 'text');
		$this->assertEquals('test', $property_schema->getName());
	}

	public function testGetType_returnsType() {
		$property_schema = new PropertySchema('test', 'text');
		$this->assertEquals('text', $property_schema->getType());
	}

	public function testGetTypeClass_returnsTypeClass() {
		$property_schema = new PropertySchema('test', 'text');
		$this->assertEquals('Karwana\\Penelope\\Types\\Text', $property_schema->getTypeClass());
	}

	public function testIsMultiValue_returnsTrueWhenSet() {
		$property_schema = new PropertySchema('test', 'text', true);
		$this->assertTrue($property_schema->isMultiValue());
	}
}
