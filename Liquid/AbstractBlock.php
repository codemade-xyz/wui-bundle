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
 * Base class for blocks.
 */
class AbstractBlock extends AbstractTag
{
	/**
	 * @var AbstractTag[]
	 */
	protected $nodelist = array();

	/**
	 * @return array
	 */
	public function getNodelist() {
		return $this->nodelist;
	}

    /**
     * Parses the given tokens
     *
     * @param array $tokens
     *
     * @return void
     * @throws LiquidException
     * @throws \ReflectionException
     */
	public function parse(array &$tokens) {
		$startRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '/');
		$tagRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '\s*(\w+)\s*(.*)?' . Liquid::get('TAG_END') . '$/');
		$variableStartRegexp = new Regexp('/^' . Liquid::get('VARIABLE_START') . '/');

		$this->nodelist = array();

		if (!is_array($tokens)) {
			return;
		}

		$tags = Template::getTags();

		while (count($tokens)) {
			$token = array_shift($tokens);

			if ($startRegexp->match($token)) {
				if ($tagRegexp->match($token)) {
					// If we found the proper block delimiter just end parsing here and let the outer block proceed
					if ($tagRegexp->matches[1] == $this->blockDelimiter()) {
						$this->endTag();
						return;
					}

					$tagName = null;
					if (array_key_exists($tagRegexp->matches[1], $tags)) {
						$tagName = $tags[$tagRegexp->matches[1]];
					} else {
						$tagName = '\CodeMade\WuiBundle\Liquid\Tag\Tag' . ucwords($tagRegexp->matches[1]);
						$tagName = (class_exists($tagName) === true) ? $tagName : null;
					}

					if ($tagName !== null) {
						$this->nodelist[] = new $tagName($tagRegexp->matches[2], $tokens, $this->fileSystem);
						if ($tagRegexp->matches[1] == 'extends') {
							return;
						}
					} else {
						$this->unknownTag($tagRegexp->matches[1], $tagRegexp->matches[2], $tokens);
					}
				} else {
					new LiquidException("Tag $token was not properly terminated", 7); // harry
				}

			} elseif ($variableStartRegexp->match($token)) {
				$this->nodelist[] = $this->createVariable($token);

			} elseif ($token != '') {
				$this->nodelist[] = $token;
			}
		}

		$this->assertMissingDelimitation();
	}

	/**
	 * Render the block.
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	public function render(Context $context) {
		return $this->renderAll($this->nodelist, $context);
	}

	/**
	 * Renders all the given nodelist's nodes
	 *
	 * @param array $list
	 * @param Context $context
	 *
	 * @return string
	 */
	protected function renderAll(array $list, Context $context) {
		$result = '';

		foreach ($list as $token) {
			$result .= (is_object($token) && method_exists($token, 'render')) ? $token->render($context) : $token;

            if (isset($context->registers['break'])) {
                break;
            }
            if (isset($context->registers['continue'])) {
                break;
            }
		}

		return $result;
	}

	/**
	 * An action to execute when the end tag is reached
	 */
	protected function endTag() {
		// Do nothing by default
	}

    /**
     * Handler for unknown tags
     *
     * @param string $tag
     * @param string $params
     * @param array $tokens
     *
     * @throws LiquidException
     * @throws \ReflectionException
     */
	protected function unknownTag($tag, $params, array $tokens) {
		switch ($tag) {
			case 'else':
				new LiquidException($this->blockName() . " does not expect else tag", 7);
			case 'end':
				new LiquidException("'end' is not a valid delimiter for " . $this->blockName() . " tags. Use " . $this->blockDelimiter(), 7);
			default:
				new LiquidException("Unknown tag $tag", 7);
		}
	}

    /**
     * This method is called at the end of parsing, and will through an error unless
     * this method is subclassed, like it is for Document
     *
     * @throws LiquidException
     * @throws \ReflectionException
     */
	protected function assertMissingDelimitation() {
		new LiquidException($this->blockName() . " tag was never closed", 7);
	}

    /**
     * Returns the string that delimits the end of the block
     *
     * @return string
     * @throws \ReflectionException
     */
	protected function blockDelimiter() {
		return "end" . $this->blockName();
	}

    /**
     * Returns the name of the block
     *
     * @return string
     * @throws \ReflectionException
     */
	private function blockName() {
		$reflection = new \ReflectionClass($this);
		return str_replace('tag', '', strtolower($reflection->getShortName()));
	}

    /**
     * Create a variable for the given token
     *
     * @param string $token
     *
     * @return Variable
     * @throws LiquidException
     */
	private function createVariable($token) {
		$variableRegexp = new Regexp('/^' . Liquid::get('VARIABLE_START') . '(.*)' . Liquid::get('VARIABLE_END') . '$/');
		if ($variableRegexp->match($token)) {
			return new Variable($variableRegexp->matches[1]);
		}

		new LiquidException("Variable $token was not properly terminated", 7);
	}
}
