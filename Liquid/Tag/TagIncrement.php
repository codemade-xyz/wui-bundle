<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace CodeMade\WuiBundle\Liquid\Tag;

use CodeMade\WuiBundle\Liquid\AbstractTag;
use CodeMade\WuiBundle\Liquid\Liquid;
use CodeMade\WuiBundle\Liquid\Context;
use CodeMade\WuiBundle\Liquid\LiquidException;
use CodeMade\WuiBundle\Liquid\FileSystem;
use CodeMade\WuiBundle\Liquid\Regexp;

/**
 * Used to increment a counter into a template
 *
 * Example:
 *
 *     {% increment value %}
 *
 * @author Viorel Dram
 */
class TagIncrement extends AbstractTag
{
	/**
	 * Name of the variable to increment
	 *
	 * @var string
	 */
	private $toIncrement;

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param FileSystem $fileSystem
	 *
	 * @throws \Liquid\LiquidException
	 */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null) {
		$syntax = new Regexp('/(' . Liquid::get('VARIABLE_NAME') . ')/');

		if ($syntax->match($markup)) {
			$this->toIncrement = $syntax->matches[0];
		} else {
			throw new LiquidException("Syntax Error in 'increment' - Valid syntax: increment [var]");
		}
	}

	/**
	 * Renders the tag
	 *
	 * @param Context $context
	 *
	 * @return string|void
	 */
	public function render(Context $context) {
		// If the value is not set in the environment check to see if it
		// exists in the context, and if not set it to -1
		if (!isset($context->environments[0][$this->toIncrement])) {
			// check for a context value
			$from_context = $context->get($this->toIncrement);

			// we already have a value in the context
			$context->environments[0][$this->toIncrement] = (null !== $from_context) ? $from_context : -1;
		}

		// Increment the value
		$context->environments[0][$this->toIncrement]++;
	}
}
