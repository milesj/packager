<?php
/**
 * @copyright	Copyright 2006-2013, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/packager
 */

namespace Packager\Minifier;

use Packager\Minifier;
use JsMin\Minify;

/**
 * Uses the JSMin class to minify Javascript files.
 */
class JsMinifier implements Minifier {

	/**
	 * Minify the file at the path.
	 *
	 * @access public
	 * @param string $path
	 * @return string
	 */
	public function minify($path) {
		return Minify::minify(file_get_contents($path));
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