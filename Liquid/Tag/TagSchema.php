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

use CodeMade\WuiBundle\Liquid\AbstractBlock;
use CodeMade\WuiBundle\Liquid\AbstractTag;
use CodeMade\WuiBundle\Liquid\Document;
use CodeMade\WuiBundle\Liquid\Context;
use CodeMade\WuiBundle\Liquid\Liquid;
use CodeMade\WuiBundle\Liquid\LiquidException;
use CodeMade\WuiBundle\Liquid\FileSystem;
use CodeMade\WuiBundle\Liquid\Regexp;
use CodeMade\WuiBundle\Liquid\Template;

/**
 * Includes another, partial, template
 *
 * Example:
 *
 *     {% section 'foo' %}
 *
 *     Will include the template called 'foo'
 *
 *     {% section 'foo' with 'bar' %}
 *
 *     Will include the template called 'foo', with a variable called foo that will have the value of 'bar'
 *
 *     {% section 'foo' for 'bar' %}
 *
 *     Will loop over all the values of bar, including the template foo, passing a variable called foo
 *     with each value of bar
 */
class TagSchema extends AbstractBlock
{
    public function render(Context &$context)
    {
        liquid::setSchema(parent::render($context));
        return '';
    }
}
