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
	 * Minify the file content.
	 *
	 * @access public
	 * @param string $content
	 * @return string
	 */
	public function minify($content) {
		return Minify::minify($content);
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