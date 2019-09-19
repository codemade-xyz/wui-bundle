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
 * LiquidException class.
 */
class LiquidException
{

    public function __construct($message = "", $code = 0, $previous = null)
    {
        Liquid::$config['error'][] = $message;
        $code = 0;

        /*if (Liquid::$project_env) {
            throw new LiquidExceptionInfo($message, $code, $previous);
        }*/

    }

}
