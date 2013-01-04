<?php
/**
 * @copyright	Copyright 2006-2013, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/packager
 */

namespace Packager\Minifier;

use Packager\Minifier;
use \CssMin;

/**
 * Uses the CssMin class to minify CSS files.
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
	 * Minify the file content.
	 *
	 * @access public
	 * @param string $content
	 * @return string
	 */
	public function minify($content) {
		return CssMin::minify($content, $this->_filters, $this->_plugins);
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