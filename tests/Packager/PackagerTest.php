<?php
/**
 * @copyright	Copyright 2006-2013, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/packager
 */

namespace Packager;

use Packager\Minifier\CssMinifier;
use \Exception;

class PackagerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Packager instance.
	 *
	 * @access protected
	 * @var \Packager\Packager
	 */
	protected $object;

	/**
	 * Path to source files.
	 *
	 * @access protected
	 * @var string
	 */
	protected $sourcePath;

	/**
	 * List of content items.
	 *
	 * @access protected
	 * @var array
	 */
	protected $items = array(
		'js/a' => array(
			'title' => 'A',
			'description' => 'Common Javascript.',
			'path' => 'js/a.js',
			'type' => 'js',
			'category' => 'library',
			'requires' => array(),
			'provides' => array()
		),
		'js/b' => array(
			'title' => 'B',
			'description' => 'Modular Javascript.',
			'path' => 'js/b.js',
			'type' => 'js',
			'category' => 'library',
			'requires' => array(),
			'provides' => array()
		),
		'js/c' => array(
			'title' => 'C',
			'description' => 'Explicit Javascript.',
			'path' => 'js/c.js',
			'type' => 'js',
			'category' => 'library',
			'requires' => array('js/b'),
			'provides' => array()
		),
		'css' => array(
			'title' => 'CSS',
			'description' => 'Common stylesheet.',
			'path' => 'css/style.css',
			'type' => 'css',
			'category' => 'library',
			'requires' => array(),
			'provides' => array()
		),
		'css/mobile' => array(
			'title' => 'Mobile CSS',
			'description' => 'Mobile stylesheet.',
			'path' => 'css/mobile.css',
			'type' => 'css',
			'category' => 'library',
			'requires' => array(),
			'provides' => array('css')
		)
	);

	/**
	 * Store the instance.
	 */
	protected function setUp() {
		$this->object = new Packager(TEST_DIR . '/project/');
		$this->sourcePath = str_replace('\\', '/', TEST_DIR) . '/project/';
	}

	/**
	 * Test that addItem() adds an item to the package while getPackage() returns the list.
	 */
	public function testAddItemGetPackage() {
		$this->object->addItem('css');

		$expected = array(
			'css' => array(
				'title' => 'CSS',
				'description' => 'Common stylesheet.',
				'path' => 'css/style.css',
				'type' => 'css',
				'category' => 'library',
				'requires' => array(),
				'provides' => array(),
				'source' => $this->sourcePath . 'css/style.css'
			)
		);

		// Should only include CSS
		$this->assertEquals($expected, $this->object->getPackage());
	}

	/**
	 * Test that item requirements are included first.
	 */
	public function testItemRequirements() {
		$this->object->addItem('js/c');

		$expected = array(
			'js/b' => array(
				'title' => 'B',
				'description' => 'Modular Javascript.',
				'path' => 'js/b.js',
				'type' => 'js',
				'category' => 'library',
				'requires' => array(),
				'provides' => array(),
				'source' => $this->sourcePath . 'js/b.js'
			),
			'js/c' => array(
				'title' => 'C',
				'description' => 'Explicit Javascript.',
				'path' => 'js/c.js',
				'type' => 'js',
				'category' => 'library',
				'requires' => array('js/b'),
				'provides' => array(),
				'source' => $this->sourcePath . 'js/c.js'
			)
		);

		// C should also include B as a requirement
		$this->assertEquals($expected, $this->object->getPackage());
	}

	/**
	 * Test that item provides are included after.
	 */
	public function testItemProvides() {
		$this->object->addItem('css/mobile');

		$expected = array(
			'css/mobile' => array(
				'title' => 'Mobile CSS',
				'description' => 'Mobile stylesheet.',
				'path' => 'css/mobile.css',
				'type' => 'css',
				'category' => 'library',
				'requires' => array(),
				'provides' => array('css'),
				'source' => $this->sourcePath . 'css/mobile.css'
			),
			'css' => array(
				'title' => 'CSS',
				'description' => 'Common stylesheet.',
				'path' => 'css/style.css',
				'type' => 'css',
				'category' => 'library',
				'requires' => array(),
				'provides' => array(),
				'source' => $this->sourcePath . 'css/style.css'
			)
		);

		// css/mobile provides css
		$this->assertEquals($expected, $this->object->getPackage());
	}

	/**
	 * Test that addMinifier() sets a Minifier and getMinifier() returns it.
	 */
	public function testAddGetMinifier() {
		try {
			$this->object->getMinifier('css');
			$this->assertTrue(false);

		} catch (Exception $e) {
			$this->assertTrue(true);
		}

		$this->object->addMinifier(new CssMinifier());
		$this->assertInstanceOf('\Packager\Minifier\Minifier', $this->object->getMinifier('css'));
	}

	/**
	 * Test that formatName() inserts the package name and version.
	 */
	public function testFormatName() {
		$this->assertEquals('packager', $this->object->formatName('packager'));
		$this->assertEquals('packager-1.0.0', $this->object->formatName('packager-{version}'));
		$this->assertEquals('packager-1.0.0', $this->object->formatName('{name}-{version}'));
	}

	/**
	 * Test that getItem() returns a single item from the package contents.
	 */
	public function testGetItem() {
		$this->assertEquals(array(
			'title' => 'C',
			'description' => 'Explicit Javascript.',
			'path' => 'js/c.js',
			'type' => 'js',
			'category' => 'library',
			'requires' => array('js/b'),
			'provides' => array()
		), $this->object->getItem('js/c'));

		try {
			$this->object->getItem('foobar');
			$this->assertTrue(false);

		} catch (Exception $e) {
			$this->assertTrue(true);
		}
	}

	/**
	 * Test that getItems() returns the package contents.
	 */
	public function testGetItems() {
		$this->assertEquals($this->items, $this->object->getItems());
	}

	/**
	 * Test that getManifest() returns the package manifest.
	 */
	public function testGetManifest() {
		$this->assertEquals(array(
			'name' => 'Packager',
			'description' => 'Example packager project.',
			'version' => '1.0.0',
			'copyright' => 2013,
			'link' => 'http://milesj.me/code/php/packager',
			'license' => 'MIT',
			'sourcePath' => $this->sourcePath,
			'outputFile' => '',
			'authors' => array(
				array(
					'name' => 'Miles Johnson',
					'link' => 'http://milesj.me'
				)
			),
			'includes' => array('js/a'),
			'contents' => $this->items
		), $this->object->getManifest());
	}

	/**
	 * Test that package() combines multiple items into a single output.
	 */
	public function testPackage() {
		// A is automatically included via "includes" in the manifest
		$this->assertEquals('var A = {};var B = {};', $this->object->package(array('js/b'), array('docBlocks' => false)));

		// And B will be included before C
		$this->assertEquals('var A = {};var B = {};var C = {};', $this->object->package(array('js/c', 'js/a'), array('docBlocks' => false)));
	}

	/**
	 * Test that package() will combine all items.
	 */
	public function testPackageAll() {
		// Type filtering should be used if more than 1 type is being used
		$this->assertEquals('var A = {};var B = {};var C = {};.style {}.mobile {}', $this->object->package(array(), array('docBlocks' => false)));
	}

	/**
	 * Test that package() will filter items by type.
	 */
	public function testPackageFilterType() {
		$this->assertEquals('var A = {};var B = {};var C = {};', $this->object->package(array(), array('filterType' => 'js', 'docBlocks' => false)));
		$this->assertEquals('.style {}.mobile {}', $this->object->package(array(), array('filterType' => 'css', 'docBlocks' => false)));
	}

	/**
	 * Test that package() will filter items by type.
	 */
	public function testPackageDocBlocks() {
		$expected = <<<EXP
/**
 * Packager
 * Example packager project.
 *
 * @copyright	2013
 * @link		http://milesj.me/code/php/packager
 * @license		MIT
 * @authors		Miles Johnson
 * @package		js/a, js/b, js/c
 */

/* js/a.js */
var A = {};

/* js/b.js */
var B = {};

/* js/c.js */
var C = {};
EXP;

		$this->assertEquals(str_replace("\r", "", $expected), $this->object->package(array(), array('filterType' => 'js')));

		$expected = <<<EXP
/**
 * Packager
 * Example packager project.
 *
 * @copyright	2013
 * @link		http://milesj.me/code/php/packager
 * @license		MIT
 * @authors		Miles Johnson
 * @package		css, css/mobile
 */

/* css/style.css */
.style {}

/* css/mobile.css */
.mobile {}
EXP;

		$this->assertEquals(str_replace("\r", "", $expected), $this->object->package(array(), array('filterType' => 'css')));
	}

}