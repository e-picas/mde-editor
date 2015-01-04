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
 * Process Markdown headers
 *
 * Setext-style headers:
 *
 *    Header 1  {#header1}
 *    ========
 *  
 *    Header 2  {#header2}
 *    --------
 *
 * ATX-style headers:
 *
 *  # Header 1        {#header1}
 *  ## Header 2       {#header2}
 *  ## Header 2 with closing hashes ##  {#header3}
 *  ...
 *  ###### Header 6   {#header2}
 *
 * @package MarkdownExtended\Grammar\Filter
 */
class Header
    extends Filter
{

    /**
     * Redefined to add id attribute support.
     *
     * @param   string  $text
     * @return  string
     */
    public function transform($text)
    {
        // Setext-style headers:
        $text = preg_replace_callback(
            '{
                (^.+?)                                  # $1: Header text
                (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})?        # $2: Id attribute
                [ ]*\n(=+|-+)[ ]*\n+                    # $3: Header footer
            }mx',
            array($this, '_setext_callback'), $text);

        // atx-style headers:
        $text = preg_replace_callback('{
                ^(\#{1,6})                              # $1 = string of #\'s
                [ ]*
                (.+?)                                   # $2 = Header text
                [ ]*
                \#*                                     # optional closing #\'s (not counted)
                (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})?        # id attribute
                [ ]*
                \n+
            }xm',
            array($this, '_atx_callback'), $text);

        return $text;
    }

    /**
     * Process setext-style headers
     *
     * @param   array   $matches    The results from the `transform()` function
     * @return  string
     */
    protected function _setext_callback($matches)
    {
        if ($matches[3] == '-' && preg_match('{^- }', $matches[1])) {
            return $matches[0];
        }
        $level = ($matches[3]{0} == '=' ? 1 : 2)  + $this->_getRebasedHeaderLevel();
        return "\n".str_pad('#', $level, '#').' '.$matches[1].' '
            .(!empty($matches[2]) ? '{#'.$matches[2].'}' : '')."\n";
/*
        $id  = MarkdownExtended::getContent()->setNewDomId($matches[2], null, false);
        $title = parent::runGamut('span_gamut', $matches[1]);
        MarkdownExtended::getContent()
            ->addMenu(array('level'=>$level,'text'=>$title), $id);
        $block = MarkdownExtended::get('OutputFormatBag')
            ->buildTag('title', $title, array(
                'level'=>$level,
                'id'=>$id
            ));

        $this->_setContentTitle($title);

        return "\n" . parent::hashBlock($block) . "\n\n";
*/
    }

    /**
     * Process ATX-style headers
     *
     * @param   array   $matches    The results from the `transform()` function
     * @return  string
     */
    protected function _atx_callback($matches)
    {
        $level = strlen($matches[1]) + $this->_getRebasedHeaderLevel();
        $id  = !empty($matches[3]) ?
            $matches[3]
            :
            MDE_Helper::header2Label($matches[2]);
        $id  = MarkdownExtended::getContent()->setNewDomId($id, null, false);
        $title = parent::runGamut('span_gamut', $matches[2]);
        MarkdownExtended::getContent()
            ->addMenu(array('level'=>$level,'text'=>parent::unhash($title)), $id);
        $block = MarkdownExtended::get('OutputFormatBag')
            ->buildTag('title', $title, array(
                'level'=>$level,
                'id'=>$id
            ));

        $this->_setContentTitle($title);

        return "\n" . parent::hashBlock($block) . "\n\n";
    }

    /**
     * Rebase a header level according to the `baseheaderlevel` config value
     */
    protected function _getRebasedHeaderLevel()
    {
        $base_level = MarkdownExtended::getVar('baseheaderlevel');
        return !empty($base_level) ? $base_level-1 : 0;
    }

    /**
     * Set the page content if it is not set yet
     */
    protected function _setContentTitle($string)
    {
        $old = MarkdownExtended::getContent()->getTitle();
        if (empty($old)) {
            $meta = MarkdownExtended::getContent()->getMetadata();
            $meta['title'] = $string;
            MarkdownExtended::getContent()->setMetadata($meta);
        }
    }

}

// Endfile