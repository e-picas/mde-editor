<?php
/*
 * This file is part of the PHP-MarkdownExtended package.
 *
 * (c) Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MarkdownExtended\Grammar\Filter;

use MarkdownExtended\MarkdownExtended;
use MarkdownExtended\Grammar\Filter;
use MarkdownExtended\Helper as MDE_Helper;
use MarkdownExtended\Exception as MDE_Exception;

/**
 * Process Markdown hard breaks
 *
 * Hard breaks are written as one or more blank line(s).
 *
 * @package MarkdownExtended\Grammar\Filter
 */
class HardBreak
    extends Filter
{

    /**
     * @param   string  $text
     * @return  string
     */
    public function transform($text)
    {
        return preg_replace_callback('/ {2,}\n/', array($this, '_callback'), $text);
    }

    /**
     * @param   array   $matches    A set of results of the `transform()` function
     * @return  string
     */
    protected function _callback($matches)
    {
        return parent::hashPart(MarkdownExtended::get('OutputFormatBag')->buildTag('new_line')."\n");
    }

}

// Endfile