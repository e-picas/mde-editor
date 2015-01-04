<?php
/*
 * This file is part of the PHP-MarkdownExtended package.
 *
 * (c) Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MarkdownExtended\Grammar;

use \MarkdownExtended\MarkdownExtended;

/**
 * The base class for all Filters and Tools
 * @package MarkdownExtended\Grammar
 */
abstract class AbstractGamut
{

    /**
     * Run a gamut stack from a filter or tool
     *
     * @param   string  $gamut  The name of a single Gamut or a Gamuts stack
     * @param   string  $text
     * @return  string
     */
    public function runGamut($gamut, $text)
    {
        return MarkdownExtended::get('Grammar\Gamut')->runGamut($gamut, $text);
    }

// ----------------------------------
// HASHES
// ----------------------------------

    /**
     * @var array
     */
    protected static $html_hashes = array();

    /**
     * Reset the hash table
     */
    public function resetHash()
    {
        self::$html_hashes = array();
    }

    /**
     * Reference a new hash
     *
     * @param   string  $key
     * @param   string  $text
     */
    public function setHash($key, $text)
    {
        self::$html_hashes[$key] = $text;
    }

    /**
     * Retrieve a hash by its key
     *
     * @param   string  $key
     */
    public function getHash($key)
    {
        return self::$html_hashes[$key];
    }

}

// Endfile