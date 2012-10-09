<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/cakephp/utility
 */

namespace mjohnson\packager;

use mjohnson\packager\Minifier;
use \Exception;

/**
 * Parses a package.json file that generates a script and dependency list.
 * This will be used in the packaging and minifying of a manifest.
 *
 * @package	mjohnson.packager
 * @version	1.0.0
 */
class Packager {

	/**
	 * Bundles to aggregate.
	 *
	 * @access public
	 * @var array
	 */
	protected $_bundles = array();

	/**
	 * List of scripts within the source path.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_scripts = array();

	/**
	 * List of scripts to minify and aggregate into the output file.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_manifest = array();

	/**
	 * Minifier instance.
	 *
	 * @access protected
	 * @var \mjohnson\packager\Minifier
	 */
	protected $_minifier;

	/**
	 * The parsed package.json file.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_package;

	/**
	 * Parse the package.json file and determine dependencies.
	 *
	 * @access public
	 * @param string $path
	 * @param \mjohnson\packager\Minifier $minifier
	 * @throws \Exception
	 */
	public function __construct($path, Minifier $minifier = null) {
		if (substr($path, -1) !== '/') {
			$path .= '/';
		}

		$path = str_replace('\\', '/', $path);

		if (!file_exists($path . 'package.json')) {
			throw new Exception('package.json does not exist in source path.');
		}

		// Parse package
		$package = array_merge(array(
			'name' => '',
			'description' => '',
			'copyright' => date('Y'),
			'link' => '',
			'license' => '',
			'sourcePath' => '',
			'outputFile' => '',
			'authors' => array(),
			'scripts' => array(),
			'bundles' => array()
		), json_decode(file_get_contents($path . 'package.json'), true));

		// Build dependencies
		$bundles = array();
		$scripts = array();

		$package['sourcePath'] = $path . $package['sourcePath'];

		if ($package['outputFile']) {
			$package['outputFile'] = $path . $package['outputFile'];
		}

		if ($package['scripts']) {
			foreach ($package['scripts'] as $script) {
				$scripts[$script['name']] = array_merge(array(
					'name' => '',
					'path' => '',
					'requires' => array(),
					'provides' => array()
				), $script);
			}
		}

		$this->_package = $package;
		$this->_bundles = $bundles;
		$this->_scripts = $scripts;

		if ($minifier) {
			$this->_minifier = $minifier;
		}
	}

	/**
	 * Loop through the items and build a manifest.
	 * Use the manifest to generate the output file.
	 *
	 * @access public
	 * @param array $items
	 * @return string
	 * @throws \Exception
	 */
	public function package(array $items) {
		foreach ($items as $item) {
			$script = $this->_getScript($item);

			if ($script['requires']) {
				foreach ($script['requires'] as $req) {
					$this->_updateManifest($req);
				}
			}

			$this->_updateManifest($item);
		}

		// Minify and aggregate files
		$output = "/**\n";

		foreach (array('name', 'description', 'copyright', 'link', 'license', 'authors') as $key) {
			if (!($value = $this->_package[$key])) {
				continue;
			}

			switch ($key) {
				case 'name':
				case 'description':
					$output .= sprintf(" * %s\n", $value);
				break;
				case 'authors':
					$authors = array();

					foreach ($value as $author) {
						$string = $author['name'];

						if (isset($author['homepage'])) {
							$string .= ' <' . $author['homepage'] . '>';
						}

						$authors[] = $string;
					}

					$output .= sprintf(" * @%s\t\t%s\n", $key, implode(', ', $authors));
				break;
				default:
					$tabs = "\t\t";

					if ($key === 'copyright') {
						$output .= " *\n";
						$tabs = "\t";
					}

					$output .= sprintf(" * @%s%s%s\n", $key, $tabs, $value);
				break;
			}
		}

		$output .= sprintf(" * @package\t\t%s\n", implode(', ', array_keys($this->_manifest)));
		$output .= " */\n\n";

		foreach ($this->_manifest as $path) {
			if (!file_exists($path)) {
				throw new Exception(sprintf('Script does not exist at path: %s', $path));
			}

			if ($this->_minifier) {
				$contents = $this->_minifier->minify($path);
			} else {
				$contents = file_get_contents($path);
			}

			$output .= "/* " . str_replace($this->_package['sourcePath'], '', $path) . " */\n";
			$output .= trim($contents) . "\n\n";
		}

		// Write output file
		if ($outputFile = $this->_package['outputFile']) {
			$outputFolder = dirname($outputFile);

			if (!file_exists($outputFolder)) {
				mkdir($outputFolder, 0777, true);
			}

			if (!file_put_contents($outputFile, $output)) {
				throw new Exception('Failed to package manifest to output file.');
			}
		}

		return $output;
	}

	/**
	 * Get a scripts information, else throw an Exception.
	 *
	 * @access public
	 * @param string $name
	 * @return array
	 * @throws \Exception
	 */
	public function _getScript($name) {
		if (isset($this->_scripts[$name])) {
			return $this->_scripts[$name];
		}

		throw new Exception(sprintf('Script %s does not exist.', $name));
	}

	/**
	 * Update the manifest with a new script dependency.
	 *
	 * @access public
	 * @param string $name
	 * @return \mjohnson\packager\Packager
	 */
	public function _updateManifest($name) {
		if (isset($this->_manifest[$name])) {
			return $this;
		}

		$script = $this->_getScript($name);

		$this->_manifest[$name] = $this->_package['sourcePath'] . $script['path'];

		return $this;
	}

}