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
 * Process Markdown spans
 *
 * @package MarkdownExtended\Grammar\Filter
 */
class Span
    extends Filter
{

    /**
     * Take the string $str and parse it into tokens, hashing embedded HTML,
     * escaped characters and handling code and maths spans.
     *
     * @param   string  $str
     * @return  string
     */
    function transform($str)
    {
        $output = '';
        $span_re = '{
                (
                    \\\\'.MarkdownExtended::getConfig('escape_chars_re').'
                |
                    (?<![`\\\\])
                    `+                        # code span marker
                |
                    \\ \(                     # inline math
            '.( MarkdownExtended::getConfig('no_markup') ? '' : '
                |
                    <!--    .*?     -->       # comment
                |
                    <\?.*?\?> | <%.*?%>       # processing instruction
                |
                    <[/!$]?[-a-zA-Z0-9:_]+    # regular tags
                    (?>
                        \s
                        (?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
                    )?
                    >
            ').'
                )
                }xs';

        while (1) {

            // Each loop iteration search for either the next tag, the next
            // opening code span marker, or the next escaped character.
            // Each token is then passed to handleSpanToken.
            $parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE);

            // Create token from text preceding tag.
            if ($parts[0] != "") {
                $output .= $parts[0];
            }

            // Check if we reach the end.
            if (isset($parts[1])) {
                $output .= self::handleSpanToken($parts[1], $parts[2]);
                $str = $parts[2];
            } else {
                break;
            }
        }

        return $output;
    }

    /**
     * Handle $token provided by parseSpan by determining its nature and
     * returning the corresponding value that should replace it.
     *
     * @param   string  $token
     * @param   string  $str
     * @return  string
     */
    function handleSpanToken($token, &$str)
    {
        switch ($token{0}) {
            case "\\":
                if ($token{1} == "(") {
                    $texend = strpos($str, '\\)');
                    if ($texend) {
                        $eqn = substr($str, 0, $texend);
                        $str = substr($str, $texend+2);
                        $texspan = parent::runGamut('filter:Maths:span', $eqn);
                        return parent::hashPart($texspan);
                    } else {
                        return $str;
                    }
                } else {
                    return parent::hashPart("&#". ord($token{1}). ";");
                }
            case "`":
                // Search for end marker in remaining text.
                if (preg_match('/^(.*?[^`])'.preg_quote($token).'(?!`)(.*)$/sm',
                    $str, $matches)
                ) {
                    $str = $matches[2];
                    $codespan = parent::runGamut('filter:CodeBlock:span', $matches[1]);
                    return parent::hashPart($codespan);
                }
                return $token; // return as text since no ending marker found.
            default:
                return parent::hashPart($token);
        }
    }

}

// Endfile