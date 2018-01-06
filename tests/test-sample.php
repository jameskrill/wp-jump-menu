<?php
/**
 * Class SampleTest
 *
 * @package Wp_Jump_Menu
 */

use PHPUnit\Framework\TestCase as WP_UnitTestCase;

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}

	function test_sample_2() {
		$expectedValue = "This is a test.";
		$this->assertEquals($expectedValue, "This is a test");
	}
}
