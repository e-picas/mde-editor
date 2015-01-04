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
 * Process Markdown fenced code blocks
 *
 * Fenced code blocks may be written like:
 *
 *      ~~~~(language)
 *      my content ...
 *      ~~~~
 *
 * @package MarkdownExtended\Grammar\Filter
 */
class FencedCodeBlock
    extends Filter
{

    /**
     * @param   string  $text
     * @return  string
     */
    public function transform($text)
    {
        return preg_replace_callback('{
                (?:\n|\A)               # 1: Opening marker
                (
                    ~{3,}|`{3,}         # Marker: three tildes or backticks or more.
                )
                (\w+)?                  # 2: Language
                [ ]* \n                 # Whitespace and newline following marker.
                (                       # 3: Content
                    (?>
                        (?!\1 [ ]* \n)  # Not a closing marker.
                        .*\n+
                    )+
                )
                \1 [ ]* \n              # Closing marker
            }xm',
            array($this, '_callback'), $text);
    }

    /**
     * Process the fenced code blocks
     *
     * @param   array   $matches    Results form the `transform()` function
     * @return  string
     */
    protected function _callback($matches)
    {
        $language  = $matches[2];
        $codeblock = MDE_Helper::escapeCodeContent($matches[3]);
        $codeblock = preg_replace_callback('/^\n+/', array($this, '_newlines'), $codeblock);

        $attributes = array();
        if (!empty($language)) {
            $attribute = MarkdownExtended::getConfig('fcb_language_attribute');
            $attributes[$attribute] = MDE_Helper::fillPlaceholders(
                MarkdownExtended::getConfig('fcb_attribute_value_mask'), $language);
        }
        $codeblock = MarkdownExtended::get('OutputFormatBag')
            ->buildTag('preformatted', $codeblock, $attributes);
        return "\n\n" . parent::hashBlock($codeblock) . "\n\n";
    }

    /**
     * Process the fenced code blocks new lines
     *
     * @param   array   $matches
     * @return  string
     */
    protected function _newlines($matches)
    {
        return str_repeat(MarkdownExtended::get('OutputFormatBag')->buildTag('new_line'), strlen($matches[0]));
    }


}

// Endfile