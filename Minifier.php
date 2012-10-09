<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/cakephp/utility
 */

namespace mjohnson\packager;

/**
 * Interface for script Minification.
 *
 * @package	mjohnson.packager
 * @version	1.0.0
 */
interface Minifier {

	/**
	 * Minify the script at the path.
	 *
	 * @access public
	 * @param string $path
	 * @return string
	 */
	public function minify($path);

}