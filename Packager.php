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
 * Parses a package.json manifest file that generates a script and dependency list.
 * This will be used in the packaging and minifying of a scripts into a single file.
 *
 * @version	1.0.0
 * @package	mjohnson.packager
 */
class Packager {

	/**
	 * List of scripts from the manifest.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_scripts = array();

	/**
	 * The parsed package.json file.
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
	 * List of scripts to be packaged.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_package = array();

	/**
	 * Parse the package.json manifest file and determine dependencies.
	 *
	 * @access public
	 * @param string $path
	 * @param \mjohnson\packager\Minifier $minifier
	 * @throws \Exception
	 */
	public function __construct($path, Minifier $minifier = null) {
		$path = str_replace('\\', '/', $path);

		if (substr($path, -1) !== '/') {
			$path .= '/';
		}

		if (!file_exists($path . 'package.json')) {
			throw new Exception('package.json does not exist in source path.');
		}

		// Parse manifest
		$manifest = array_merge(array(
			'name' => '',
			'description' => '',
			'version' => '',
			'copyright' => date('Y'),
			'link' => '',
			'license' => '',
			'sourcePath' => '',
			'outputFile' => '',
			'authors' => array(),
			'includes' => array(),
			'scripts' => array(),
			'bundles' => array()
		), json_decode(file_get_contents($path . 'package.json'), true));

		// Update manifest
		$manifest['sourcePath'] = $path . $manifest['sourcePath'];

		if ($manifest['outputFile']) {
			$manifest['outputFile'] = $path . $manifest['outputFile'];
		}

		// Build dependencies
		if ($manifest['scripts']) {
			foreach ($manifest['scripts'] as $key => $script) {
				$this->_scripts[$key] = array_merge(array(
					'title' => '',
					'description' => '',
					'path' => '',
					'category' => 'library',
					'requires' => array(),
					'provides' => array()
				), $script);
			}
		}

		$this->_manifest = $manifest;
		$this->_minifier = $minifier;
	}

	/**
	 * Update the package with a new script dependency.
	 *
	 * @access public
	 * @param string $name
	 * @return \mjohnson\packager\Packager
	 */
	public function addItem($name) {
		if (isset($this->_package[$name])) {
			return $this;
		}

		$script = $this->getScript($name);

		$this->_package[$name] = $this->_manifest['sourcePath'] . $script['path'];

		return $this;
	}

	/**
	 * Return the parsed manifest.
	 *
	 * @access public
	 * @return array
	 */
	public function getManifest() {
		return $this->_manifest;
	}

	/**
	 * Return the current package contents.
	 *
	 * @access public
	 * @return array
	 */
	public function getPackage() {
		return $this->_package;
	}

	/**
	 * Get a scripts information, else throw an Exception.
	 *
	 * @access public
	 * @param string $name
	 * @return array
	 * @throws \Exception
	 */
	public function getScript($name) {
		if (isset($this->_scripts[$name])) {
			return $this->_scripts[$name];
		}

		throw new Exception(sprintf('Script %s does not exist.', $name));
	}

	/**
	 * Return the list of scripts from the manifest.
	 *
	 * @access public
	 * @return array
	 */
	public function getScripts() {
		return $this->_scripts;
	}

	/**
	 * Loop through the items and build a package list.
	 * Use the package to generate the output file.
	 *
	 * @access public
	 * @param array $items
	 * @return string
	 * @throws \Exception
	 */
	public function package(array $items = array()) {
		$manifest = $this->_manifest;

		if (!$items) {
			$items = array_keys($this->_scripts);

		} else if ($manifest['includes']) {
			$items = array_merge($manifest['includes'], $items);
		}

		// Generate package list
		foreach ($items as $item) {
			$script = $this->getScript($item);

			if ($script['requires']) {
				foreach ($script['requires'] as $req) {
					$this->addItem($req);
				}
			}

			$this->addItem($item);
		}

		// Minify and aggregate files
		$output = "/**\n";

		foreach (array('name', 'description', 'copyright', 'link', 'license', 'authors') as $key) {
			if (!($value = $manifest[$key])) {
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

		$output .= sprintf(" * @package\t\t%s\n", implode(', ', array_keys($this->_package)));
		$output .= " */\n\n";

		foreach ($this->_package as $path) {
			if (!file_exists($path)) {
				throw new Exception(sprintf('Script does not exist at path: %s', $path));
			}

			if ($this->_minifier) {
				$contents = $this->_minifier->minify($path);
			} else {
				$contents = file_get_contents($path);
			}

			$output .= "/* " . str_replace($manifest['sourcePath'], '', $path) . " */\n";
			$output .= trim($contents) . "\n\n";
		}

		$output = trim($output);

		// Write output file
		if ($outputFile = $manifest['outputFile']) {
			$outputFile = str_replace('{name}', str_replace(' ', '-', strtolower($manifest['name'])), $outputFile);
			$outputFile = str_replace('{version}', strtolower($manifest['version']), $outputFile);

			$outputFolder = dirname($outputFile);

			if (!file_exists($outputFolder)) {
				mkdir($outputFolder, 0777, true);
			}

			if (!file_put_contents($outputFile, $output)) {
				throw new Exception('Failed to package scripts to output file.');
			}
		}

		return $output;
	}

}