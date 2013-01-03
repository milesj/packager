<?php
/**
 * @copyright	Copyright 2006-2013, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/packager
 */

namespace Packager\Minifier;

use Packager\Minifier\CssMinifier;

class CssMinifierTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Test that minify() compresses the CSS file.
	 */
	public function testMinify() {
		$object = new CssMinifier();

		$this->assertEquals('body{background:#000;color:#fff}.class{margin:0}#id{padding:0;border:1px solid red}', $object->minify(TEST_DIR . '/project/css/test.css'));
	}

}