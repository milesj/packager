<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/cakephp/utility
 */

namespace mjohnson\packager;

use mjohnson\packager\Minifier;
use \Exception;
use \ZipArchive;

/**
 * Parses a package.json manifest file that generates an item and dependency list.
 * This will be used in the packaging and minifying of items into a single file.
 *
 * @version	1.0.1
 * @package	mjohnson.packager
 */
class Packager {

	/**
	 * List of items from the manifest.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_items = array();

	/**
	 * The parsed package.json file.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_manifest = array();

	/**
	 * Loaded minifiers.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_minifiers = array();

	/**
	 * List of items to be packaged.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_package = array();

	/**
	 * Path to the package.json file.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_path;

	/**
	 * Parse the package.json manifest file and determine dependencies.
	 *
	 * @access public
	 * @param string $path
	 * @param string $type
	 * @throws \Exception
	 */
	public function __construct($path, $type = 'js') {
		$path = str_replace('\\', '/', $path);

		if (substr($path, -1) !== '/') {
			$path .= '/';
		}

		if (!file_exists($path . 'package.json')) {
			throw new Exception('package.json does not exist in source path.');
		}

		// Backwards support
		if ($type instanceof Minifier) {
			$this->addMinifier($type);

			$type = $type->type();
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
			'contents' => array(),
			'bundles' => array()
		), json_decode(file_get_contents($path . 'package.json'), true));

		// Update manifest
		$manifest['sourcePath'] = $path . $manifest['sourcePath'];

		// Build dependencies
		if ($manifest['contents']) {
			foreach ($manifest['contents'] as $key => $item) {
				$this->_items[$key] = array_merge(array(
					'title' => '',
					'description' => '',
					'path' => '',
					'type' => $type,
					'category' => 'library',
					'requires' => array(),
					'provides' => array()
				), $item);
			}
		}

		$this->_path = $path;
		$this->_manifest = $manifest;
	}

	/**
	 * Update the package with a new item dependency. If the item has requirements or providers, add them.
	 *
	 * @access public
	 * @param string $name
	 * @return \mjohnson\packager\Packager
	 */
	public function addItem($name) {
		if (isset($this->_package[$name])) {
			return $this;
		}

		$item = $this->getItem($name);
		$item['source'] = $this->_manifest['sourcePath'] . $item['path'];

		// Include required dependencies
		if ($item['requires']) {
			foreach ($item['requires'] as $req) {
				$this->addItem($req);
			}
		}

		$this->_package[$name] = $item;

		// Include provided dependencies
		if ($item['provides']) {
			foreach ($item['provides'] as $req) {
				$this->addItem($req);
			}
		}

		return $this;
	}

	/**
	 * Add a minifier for a specific type.
	 *
	 * @access public
	 * @param \mjohnson\packager\minifiers\Minifier $minifier
	 * @return \mjohnson\packager\Packager
	 */
	public function addMinifier(Minifier $minifier) {
		$this->_minifiers[$minifier->type()] = $minifier;

		return $this;
	}

	/**
	 * Get a items information, else throw an Exception.
	 *
	 * @access public
	 * @param string $name
	 * @return array
	 * @throws \Exception
	 */
	public function getItem($name) {
		if (isset($this->_items[$name])) {
			return $this->_items[$name];
		}

		throw new Exception(sprintf('Item %s does not exist.', $name));
	}

	/**
	 * Return the list of items from the manifest.
	 *
	 * @access public
	 * @return array
	 */
	public function getItems() {
		return $this->_items;
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
	 * Get a minifier, else throw an Exception.
	 *
	 * @access public
	 * @param string $type
	 * @return array
	 * @throws \Exception
	 */
	public function getMinifier($type) {
		if (isset($this->_minifiers[$type])) {
			return $this->_minifiers[$type];
		}

		throw new Exception(sprintf('Minifier for %s does not exist.', $type));
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
	 * Format a file name by replacing with the manifest name and version.
	 * Check that the parent folder exists and is writable.
	 *
	 * @access public
	 * @param string $file
	 * @return string
	 */
	public function prepareOutput($file) {
		$manifest = $this->getManifest();

		if (strpos($file, $this->_path) !== 0) {
			$file = $this->_path . $file;
		}

		// Format name
		$file = str_replace('{name}', preg_replace('/[^a-z0-9]/i', '-', strtolower($manifest['name'])), $file);
		$file = str_replace('{version}', strtolower($manifest['version']), $file);

		// Prepare folder
		$folder = dirname($file);

		if (!file_exists($folder)) {
			mkdir($folder, 0777, true);

		} else if (!is_writable($folder)) {
			chmod($folder, 0777);
		}

		return $file;
	}

	/**
	 * Loop through the items and build a package list.
	 * Use the package to generate the output file.
	 *
	 * @access public
	 * @param array $items
	 * @param array $options
	 * @return string
	 * @throws \Exception
	 */
	public function package(array $items = array(), array $options = array()) {
		$this->_package = array();

		$manifest = $this->_manifest;
		$output = '';

		// Merge options
		$options = $options + array(
			'outputFile' => $manifest['outputFile'],
			'prependPath' => true,
			'filterType' => false,
			'docBlocks' => true
		);

		// Update item list
		if (!$items) {
			$items = array_keys($this->_items);

		} else if ($manifest['includes']) {
			$items = array_merge($manifest['includes'], $items);
		}

		// Generate package list
		foreach ($items as $name) {
			$item = $this->getItem($name);

			// Filter out types that do not match
			if ($options['filterType'] && !in_array($item['type'], (array) $options['filterType'])) {
				continue;
			}

			$this->addItem($name);
		}

		// Minify and aggregate files
		if ($options['docBlocks']) {
			$output .= "/**\n";

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
		}

		foreach ($this->_package as $item) {
			$path = $item['source'];

			if (!file_exists($path)) {
				throw new Exception(sprintf('Item does not exist at path: %s', $path));
			}

			if ($minifier = $this->getMinifier($item['type'])) {
				$contents = $minifier->minify($path);
			} else {
				$contents = file_get_contents($path);
			}

			if ($options['docBlocks']) {
				$output .= "/* " . str_replace($manifest['sourcePath'], '', $path) . " */\n";
				$output .= trim($contents) . "\n\n";
			} else {
				$output .= trim($contents);
			}
		}

		$output = trim($output);

		// Write output file
		if ($outputFile = $options['outputFile']) {
			if ($options['prependPath']) {
				$outputFile = $this->_path . $outputFile;
			}

			$outputFile = $this->prepareOutput($outputFile);

			if (!file_put_contents($outputFile, $output)) {
				throw new Exception('Failed to package items to output file.');
			}
		}

		return $output;
	}

	/**
	 * Archive the list of files into a zip file. The output file is relative the the package root.
	 *
	 * @access public
	 * @param string $outputFile
	 * @param array $files
	 * @return boolean
	 * @throws \Exception
	 */
	public function archive($outputFile, array $files) {
		if (!class_exists('ZipArchive')) {
			throw new Exception('ZipArchive class is required for archiving.');
		}

		// Check output location
		if (substr($outputFile, -4) !== '.zip') {
			$outputFile .= '.zip';
		}

		$outputFile = $this->prepareOutput($outputFile);

		// Begin archiving
		$zip = new ZipArchive();

		if (($error = $zip->open($outputFile, ZIPARCHIVE::CREATE)) !== true) {
			throw new Exception($error);
		}

		foreach ($files as $file) {
			$path = $this->_path . $file;

			if (!file_exists($path)) {
				throw new Exception(sprintf('%s does not exist.', $file));

			} else if (!is_readable($path)) {
				throw new Exception(sprintf('%s is not readable.', $file));
			}

			$zip->addFile($path, basename($file));
		}

		return $zip->close();
	}

}