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

use Karwana\Penelope\OptionContainer;

class OptionContainerTest extends \PHPUnit_Framework_TestCase {

	public function optionContainerProvider() {
		$options = array('opt_a' => 1, 'opt_b' => 2);
		return array(array(new OptionContainer($options)));
	}

	public function testGetOptions_ReturnsAllOptions() {
		$options = array('opt_a' => 1, 'opt_b' => 2);
		$oc = new OptionContainer($options);
		$this->assertEquals($options, $oc->getOptions());
	}


	/**
	 * @dataProvider optionContainerProvider
	 */
	public function testGetOption_ReturnsOption($oc) {
		$this->assertEquals(2, $oc->getOption('opt_b'));
	}


	/**
	 * @dataProvider optionContainerProvider
	 */
	public function testGetOption_ReturnsNull($oc) {
		$this->assertNull($oc->getOption('opt_c'));
	}


	/**
	 * @dataProvider optionContainerProvider
	 */
	public function testHasOption_ReturnsBoolean($oc) {
		$this->assertTrue($oc->hasOption('opt_b'));
		$this->assertFalse($oc->hasOption('opt_c'));
	}


	/**
	 * @dataProvider optionContainerProvider
	 */
	public function testSetOption_setsOption($oc) {
		$oc->setOption('opt_c', 3);
		$this->assertEquals(3, $oc->getOption('opt_c'));
	}


	/**
	 * @dataProvider optionContainerProvider
	 */
	public function testClearOption_clearsOption($oc) {
		$oc->clearOption('opt_a');
		$this->assertNull($oc->getOption('opt_a'));
	}
}
