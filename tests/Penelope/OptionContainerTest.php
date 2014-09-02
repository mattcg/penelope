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

use Karwana\Penelope\OptionContainer;

class OptionContainerTest extends \PHPUnit_Framework_TestCase {

	public function testGetOptions_ReturnsAllOptions() {
		$options = array('opt_a' => 1, 'opt_b' => 2);
		$oc = new OptionContainer($options);
		$this->assertEquals($options, $oc->getOptions());
	}

	public function testGetOption_ReturnsOption() {
		$options = array('opt_a' => 1, 'opt_b' => 2);
		$oc = new OptionContainer($options);
		$this->assertEquals(2, $oc->getOption('opt_b'));
	}

	public function testGetOption_ReturnsNull() {
		$options = array('opt_a' => 1, 'opt_b' => 2);
		$oc = new OptionContainer($options);
		$this->assertNull($oc->getOption('opt_c'));
	}

	public function testHasOption_ReturnsBoolean() {
		$options = array('opt_a' => 1, 'opt_b' => 2);
		$oc = new OptionContainer($options);
		$this->assertTrue($oc->hasOption('opt_b'));
		$this->assertFalse($oc->hasOption('opt_c'));
	}
}
