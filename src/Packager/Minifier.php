<?php
/**
 * @copyright	Copyright 2006-2013, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/packager
 */

namespace Packager;

/**
 * Interface for file minifiers.
 */
interface Minifier {

	/**
	 * Minify the file content.
	 *
	 * @access public
	 * @param string $content
	 * @return string
	 */
	public function minify($content);

	/**
	 * The type of items this minifier runs against.
	 *
	 * @access public
	 * @return string
	 */
	public function type();

}