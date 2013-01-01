<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/cakephp/utility
 */

namespace Packager\Minifier;

/**
 * Interface for file minifiers.
 */
interface Minifier {

	/**
	 * Minify the file at the path.
	 *
	 * @access public
	 * @param string $path
	 * @return string
	 */
	public function minify($path);

	/**
	 * The type of items this minifier runs against.
	 *
	 * @access public
	 * @return string
	 */
	public function type();

}