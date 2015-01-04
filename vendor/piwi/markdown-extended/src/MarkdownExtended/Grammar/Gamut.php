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
use \MarkdownExtended\API as MDE_API;
use \MarkdownExtended\Helper as MDE_Helper;
use \MarkdownExtended\Exception as MDE_Exception;

/**
 * Central class to execute Filters and Tools methods on a content
 *
 * It can handle a list of gamuts, execute a specific method and run a single gamut.
 * @package MarkdownExtended\Grammar
 */
class Gamut
{

    /**
     * @var     array   Table of referenced aliases to execute gamuts
     */
    protected $gamut_aliases;

    /**
     * Set the gamuts aliases from config
     *
     * @param   array   $gamut_aliases
     */
    public function __construct(array $gamut_aliases = null)
    {
        if (!empty($gamut_aliases)) {
            $this->gamut_aliases = $gamut_aliases;
        }
    }

    /**
     * Run a table of gamuts by priority
     *
     * @param   array   $gamuts     The gamuts names to execute
     * @param   string  $text       The text for gamuts execution
     * @return  string
     */
    public function runGamuts(array $gamuts, $text = null)
    {
        if (!empty($gamuts)) {
            asort($gamuts);
            foreach ($gamuts as $method => $priority) {
                $text = self::runGamut($method, $text);
            }
        }
        return $text;
    }

    /**
     * Run a table of gamuts for a specific method by priority
     *
     * @param   array   $gamuts     The gamuts names to execute
     * @param   string  $method     The method name to execute in each gamut
     * @param   string  $text       The text for gamuts execution
     * @return  string
     * @throws  \MarkdownExtended\Exception\InvalidArgumentException if $method is not a string
     */
    public function runGamutsMethod(array $gamuts, $method, $text = null)
    {
        if (!is_string($method)) {
            throw new MDE_Exception\InvalidArgumentException(sprintf(
                "Gamuts method must be a string, <%s> given!", gettype($method)
            ));
        }
        if (!empty($gamuts)) {
            asort($gamuts);
            foreach ($gamuts as $_gmt => $priority) {
                $text = self::runGamut($_gmt, $text, $method, true);
            }
        }
        return $text;
    }

    /**
     * Run a single gamut
     *
     * @param   string  $gamut      The gamut name to execute
     * @param   string  $text       The text for gamuts execution
     * @param   string  $_method    The method name to execute in each gamut
     * @param   bool    $silent     Throw exceptions flag (default is `false`: exceptions are thrown)
     * @return  string
     * @throws  \MarkdownExtended\Exception\UnexpectedValueException if $gamut is not found
     * @throws  \MarkdownExtended\Exception\InvalidArgumentException if $gamut is not a string
     */
    public function runGamut($gamut, $text = null, $_method = null, $silent = false)
    {
        if (is_string($gamut)) {
            if (substr_count($gamut, ':')) {
                $text = $this->_doRunGamut($gamut, $text, $_method, $silent);

            } elseif (empty($_method)) {
                $md = MarkdownExtended::get('Parser');
                if (method_exists($md, $gamut)) {
                    $text = $md->{$gamut}($text);
                } else {
                    $gamuts = MarkdownExtended::getConfig($gamut);
                    if (!empty($gamuts) && is_array($gamuts)) {
                        $text = self::runGamuts($gamuts, $text);
                    } elseif (!$silent) {
                        throw new MDE_Exception\UnexpectedValueException(sprintf(
                            "Gamut not found: <%s>!", $gamut
                        ));
                    }
                }
            }
        } elseif (!$silent) {
            throw new MDE_Exception\InvalidArgumentException(sprintf(
                "Gamut name must be a string, <%s> given!", gettype($gamut)
            ));
        }
        return $text;
    }

    /**
     * Really run a single gamut
     *
     * @param   string  $gamut      The gamut name to execute
     * @param   string  $text       The text for gamuts execution
     * @param   string  $_method    The method name to execute in each gamut
     * @param   bool    $silent     Throw exceptions flag (default is `false`: exceptions are thrown)
     * @return  string
     * @throws  \MarkdownExtended\Exception\UnexpectedValueException if $gamut doesn't implement the required method
     * @throws  \MarkdownExtended\Exception\DomainException if $gamut doesn't implement interface `MarkdownExtended\Grammar\GamutInterface`
     * @throws  \MarkdownExtended\Exception\InvalidArgumentException if the gamut class name is not a string
     * @throws  \MarkdownExtended\Exception\InvalidArgumentException if gamut not defined
     */
    protected function _doRunGamut($gamut, $text = null, $_method = null, $silent = false)
    {
        @list($global_class, $class, $method) = explode(':', $gamut);

        $to_skip = MarkdownExtended::getConfig('skip_filters');
        if (!empty($class) && !empty($to_skip) && in_array($class, $to_skip)) {
            return $text;
        }

        if (!empty($global_class) && isset($this->gamut_aliases[$global_class])) {
            if (!empty($class)) {
                $_obj = MarkdownExtended::get($this->gamut_aliases[$global_class].'\\'.$class);
                try {
                    if (MDE_API::isValid($_obj, 'grammar\\'.$global_class)) {
                        if (!empty($_method)) $method = $_method;
                        if (empty($method)) $method = $_obj->getDefaultMethod();
                        if (method_exists($_obj, $method)) {
                            $text = $_obj->$method($text);
                        } elseif (!$silent) {
                            throw new MDE_Exception\UnexpectedValueException(sprintf(
                                "Method name in Gamut must exist in class <%s>!", $class
                            ));
                        }
                    }
                } catch (MDE_Exception\InvalidArgumentException $e) {
                    throw new MDE_Exception\DomainException(sprintf(
                        'Gamut class "%s" must implements interface "%s"!',
                        $class, MDE_API::GRAMMAR_GAMUT_INTERFACE
                    ));
                }
            } elseif (!$silent) {
                throw new MDE_Exception\UnexpectedValueException(sprintf(
                    "Class name in Gamut must be a string, <%s> given!", $class
                ));
            }
        } elseif (!$silent) {
            throw new MDE_Exception\UnexpectedValueException(sprintf(
                "Gamut name must begin by a gamut alias, <%s> given!", $global_class
            ));
        }
        return $text;
    }

}

// Endfile