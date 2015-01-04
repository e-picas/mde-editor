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
 * Process Markdown horizontal rules
 *
 * @package MarkdownExtended\Grammar\Filter
 */
class HorizontalRule
    extends Filter
{

    /**
     * @param   string  $text
     * @return  string
     */
    public function transform($text)
    {
        return preg_replace(
            '{
                ^[ ]{0,3}       # Leading space
                ([-*_])         # $1: First marker
                (?>             # Repeated marker group
                    [ ]{0,2}    # Zero, one, or two spaces.
                    \1          # Marker character
                ){2,}           # Group repeated at least twice
                [ ]*            # Tailing spaces
                $               # End of line.
            }mx',
            "\n" . parent::hashBlock(MarkdownExtended::get('OutputFormatBag')->buildTag('horizontal_rule')) . "\n",
            $text);
    }

}

// Endfile