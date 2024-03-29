<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace CodeMade\WuiBundle\Liquid;

/**
 * This implements an abstract file system which retrieves template files named in a manner similar to Rails partials,
 * ie. with the template name prefixed with an underscore. The extension ".liquid" is also added.
 *
 * For security reasons, template paths are only allowed to contain letters, numbers, and underscore.
 */
class LocalFileSystem implements FileSystem
{
	/**
	 * The root path
	 *
	 * @var string
	 */
	private $root;

	/**
	 * Constructor
	 *
	 * @param string $root The root path for templates
	 */
	public function __construct($root) {
		$this->root = $root;
	}

    /**
     * Retrieve a template file
     *
     * @param string $templatePath
     *
     * @param bool $section
     * @return string template content
     * @throws LiquidException
     */
	public function readTemplateFile($templatePath) {
		if (!($fullPath = $this->fullPath($templatePath))) {
			new LiquidException("No such template '$templatePath'");
		}

		return file_get_contents($fullPath);
	}

    /**
     * Resolves a given path to a full template file path, making sure it's valid
     *
     * @param string $templatePath
     *
     * @param bool $section
     * @return string
     * @throws LiquidException
     */
	public function fullPath($templatePath) {
        $nameRegex = Liquid::get('INCLUDE_ALLOW_EXT')
			? new Regexp('/^[^.\/][a-zA-Z0-9-_\.\/]+$/')
			: new Regexp('/^[^.\/][a-zA-Z0-9-_\/]+$/');

		if (!$nameRegex->match($templatePath)) {
			new LiquidException("Illegal template name '$templatePath'");
		}


		if (strpos($templatePath, '/') !== false) {
            $fullPath = Liquid::get('INCLUDE_ALLOW_EXT')
				? $this->root . dirname($templatePath) . '/' . basename($templatePath)
				: $this->root . dirname($templatePath) . '/' . Liquid::get('INCLUDE_PREFIX') . basename($templatePath) . '.' . Liquid::get('INCLUDE_SUFFIX');
		} else {
            $fullPath = Liquid::get('INCLUDE_ALLOW_EXT')
				? $this->root . $templatePath
				: $this->root . Liquid::get('INCLUDE_PREFIX') . $templatePath . '.' . Liquid::get('INCLUDE_SUFFIX');
		}


		$rootRegex = new Regexp('/' . preg_quote(realpath($this->root), '/') . '/');

		if (!$rootRegex->match(realpath($fullPath))) {
			new LiquidException("Illegal template '" . basename($fullPath) . "'");
		}

		return $fullPath;
	}
}
