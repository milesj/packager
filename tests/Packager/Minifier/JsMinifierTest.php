<?php
/**
 * @copyright	Copyright 2006-2013, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/packager
 */

namespace Packager\Minifier;

use Packager\Minifier\JsMinifier;

class JsMinifierTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Test that minify() compresses the JS file.
	 */
	public function testMinify() {
		$object = new JsMinifier();

		$this->assertEquals('var Test={list:[],map:{},func:function(arg){console.log(arg);}};', $object->minify(TEST_DIR . '/project/js/test.js'));
	}

}