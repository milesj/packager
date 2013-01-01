<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/cakephp/utility
 */

namespace Packager\Minifier;

use \CssMin;
use \RuntimeException;

/**
 * Uses the CssMin class to minify CSS files.
 *
 * @link http://code.google.com/p/cssmin/
 */
class CssMinifier implements Minifier {

	/**
	 * Filters to pass to CssMin.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_filters = array(
		'ImportImports' => false,
		'RemoveComments' => true,
		'RemoveEmptyRulesets' => true,
		'RemoveEmptyAtBlocks' => true,
		'ConvertLevel3AtKeyframes' => false,
		'ConvertLevel3Properties' => false,
		'Variables' => true,
		'RemoveLastDeclarationSemiColon' => false
	);

	/**
	 * Plugins to pass to CssMin.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_plugins = array(
		'Variables' => true,
		'ConvertFontWeight' => false,
		'ConvertHslColors' => true,
		'ConvertRgbColors' => true,
		'ConvertNamedColors' => false,
		'CompressColorValues' => false,
		'CompressUnitValues' => false,
		'CompressExpressionValues' => true
	);

	/**
	 * Store the filters and plugins for CssMin.
	 *
	 * @access public
	 * @param array $filters
	 * @param array $plugins
	 */
	public function __construct(array $filters = array(), array $plugins = array()) {
		$this->_filters = $filters + $this->_filters;
		$this->_plugins = $plugins + $this->_plugins;
	}

	/**
	 * Minify the file at the path.
	 *
	 * @access public
	 * @param string $path
	 * @return string
	 * @throws \RuntimeException
	 */
	public function minify($path) {
		if (!class_exists('CssMin')) {
			throw new RuntimeException('CssMin was not found within the include path');
		}

		return CssMin::minify(file_get_contents($path), $this->_filters, $this->_plugins);
	}

	/**
	 * The type of items this minifier runs against.
	 *
	 * @access public
	 * @return string
	 */
	public function type() {
		return 'css';
	}

}