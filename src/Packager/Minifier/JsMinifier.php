<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/cakephp/utility
 */

namespace Packager\Minifier;

use \JSMin;
use \RuntimeException;

/**
 * Uses the JSMin class to minify Javascript files.
 *
 * @link https://github.com/rgrove/jsmin-php/
 */
class JsMinifier implements Minifier {

	/**
	 * Minify the file at the path.
	 *
	 * @access public
	 * @param string $path
	 * @return string
	 * @throws \RuntimeException
	 */
	public function minify($path) {
		if (!class_exists('JSMin')) {
			throw new RuntimeException('JSMin was not found within the include path');
		}

		return JSMin::minify(file_get_contents($path));
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