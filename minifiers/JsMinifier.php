<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/cakephp/utility
 */

namespace mjohnson\packager\minifiers;

use mjohnson\packager\Minifier;
use \Exception;

/**
 * Uses the JSMin class to minify Javascript files.
 *
 * @version	1.0.2
 * @package	mjohnson.packager.minifiers
 * @link	https://github.com/rgrove/jsmin-php/
 */
class JsMinifier implements Minifier {

	/**
	 * Minify the file at the path.
	 *
	 * @access public
	 * @param string $path
	 * @return string
	 * @throws \Exception
	 */
	public function minify($path) {
		if (!class_exists('JSMin')) {
			throw new Exception('JSMin was not found within the include path.');
		}

		return \JSMin::minify(file_get_contents($path));
	}

	/**
	 * The type of items this minifier runs against.
	 *
	 * @access public
	 * @return string
	 */
	public function type() {
		return 'js';
	}

}