<?php
/*
 * This file is part of the PHP-MarkdownExtended package.
 *
 * (c) Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MarkdownExtended;

use \MarkdownExtended\API as MDE_API;
use \MarkdownExtended\Helper as MDE_Helper;
use \MarkdownExtended\Exception as MDE_Exception;

/**
 * Class defining a content object for the Parser
 * @package MarkdownExtended
 */
class Content
    implements MDE_API\ContentInterface
{
    /**
     * @var     mixed
     */
    protected $id;

    /**
     * @var     string
     */
    protected $filepath = '';

    /**
     * @var     \DateTime
     */
    protected $last_update = null;

    /**
     * @var     string
     */
    protected $source = '';

    /**
     * @var     string
     */
    protected $body = '';

    /**
     * @var     string
     */
    protected $charset = 'utf-8';

    /**
     * @var     string
     */
    protected $title;

    /**
     * @var     array
     */
    protected $metadata = array();

    /**
     * @var     string
     */
    protected $metadata_to_string = '';

    /**
     * @var     array
     */
    protected $notes = array();

    /**
     * @var     string
     */
    protected $notes_to_string = '';

    /**
     * @var     array
     */
    protected $footnotes = array();

    /**
     * @var     array
     */
    protected $glossaries = array();

    /**
     * @var     array
     */
    protected $citations = array();

    /**
     * @var     array
     */
    protected $menu = array();

    /**
     * @var     \MarkdownExtended\Util\RecursiveMenuIterator
     */
    protected $toc = null;

    /**
     * @var     string
     */
    protected $toc_to_string = '';

    /**
     * @var     array
     */
    protected $urls = array();

    /**
     * @var     array
     */
    protected $dom_ids = array();

    /**
     * @var  array
     */
    protected static $name_mapping = array(
        'note'      =>'notes',
        'footnote'  =>'footnotes',
        'glossary'  =>'glossaries',
        'citation'  =>'citations',
        'url'       =>'urls'
    );

// -------------------------
// Constructor
// -------------------------

    /**
     * @param   string  $source     A string implementing Markdown syntax
     * @param   string  $filepath   The path of a file where to get content to parse
     * @param   mixed   $id         The ID of the content item
     */
    public function __construct($source = null, $filepath = null, $id = null)
    {
        $this
            ->setSource($source)
            ->setFilepath($filepath)
            ->setId($id);
    }

// -------------------------
// Magic setter / getter
// -------------------------

    /**
     * Magic method to handle any `getXX()`, `setXX()` or `addXX()` method call on the object
     *
     * @throws  \MarkdownExtended\Exception\InvalidArgumentException if the method can't be found
     * @see     self::getVariable()
     * @see     self::setVariable()
     * @see     self::addVariable()
     */
    public function __call($name, array $arguments = null)
    {
        $method     = substr($name, 0, 3) . 'Variable';
        $variable   = MDE_Helper::fromCamelCase(substr($name, 3));
        array_unshift($arguments, $variable);
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arguments);
        } else {
            throw new MDE_Exception\InvalidArgumentException(
                sprintf('Call of an unknown method "%s" on a %s instance!', $name, get_class($this))
            );
        }
    }

    /**
     * Get an object property value
     *
     * This will return `null` if the variable does not exist, and `false` if it is empty.
     *
     * @param   string  $name
     * @return  mixed/null/false
     */
    protected function getVariable($name)
    {
        if (property_exists($this, $name)) {
            return !is_null($this->{$name}) ? $this->{$name} : false;
        }
        return null;
    }

    /**
     * Set an object property value
     *
     * @param   string  $name
     * @param   mixed   $value
     * @return  self
     */
    protected function setVariable($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }
        return $this;
    }

    /**
     * Extend an object property value
     *
     * @param   string  $name
     * @param   mixed   $value
     * @param   mixed   $index
     * @return  self
     */
    protected function addVariable($name, $value, $index = null)
    {
        if (array_key_exists($name, self::$name_mapping)) {
            $name = self::$name_mapping[$name];
        }
        if (property_exists($this, $name)) {
            if (is_array($this->{$name})) {
                if (!empty($index)) {
                    $this->{$name}[$index] = $value;
                } else {
                    $this->{$name}[] = $value;
                }
            } elseif (is_string($this->{$name})) {
                $this->{$name} .= $value;
            }
        }
        return $this;
    }

// -------------------------
// Classic setter / getter
// -------------------------

    /**
     * Define the content ID
     *
     * @param   mixed   $id
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;
        if (empty($this->id) || (!is_string($this->id) && !is_numeric($this->id))) {
            $this->id = uniqid();
        }
        return $this;
    }

    /**
     * Get the content ID
     *
     * @return  mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the file dirname if so
     *
     * @return  string/null
     */
    public function getDirname()
    {
        return !empty($this->filepath) ? dirname($this->filepath) : null;
    }

    /**
     * Define the last update datetime of this content
     *
     * @param   mixed     $date   timestamp / date / DateTime object
     * @return  self
     */
    public function setLastUpdate($date)
    {
        if (!is_object($date)) {
            if (is_int($date)) {
                $date = @\DateTime::createFromFormat('U', $date);
            } else {
                $date = @new \DateTime($date);
            }
        }
        if (!empty($date)) {
            $this->last_update = $date;
        }
        return $this;
    }

    /**
     * Retrieve the content last update
     *
     * @return  null/DateTime
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }

    /**
     * Get the last update of this content as string
     *
     * @return  string
     */
    public function getLastUpdateToString()
    {
        return !is_null($this->last_update) ? $this->last_update->format('r') : '';
    }

    /**
     * Get the original source content
     *
     * @return  string
     * @throws  \MarkdownExtended\Exception\InvalidArgumentException if the file can not be found or red
     */
    public function getSource()
    {
        if (!empty($this->source)) {
            return $this->source;
        }
        if (!empty($this->filepath)) {
            if (file_exists($this->filepath) && is_readable($this->filepath)) {
                $this->source = file_get_contents($this->filepath);
                $this->setLastUpdate(filemtime($this->filepath));
                if (empty($this->id)) {
                    $this->setId($this->filepath);
                }
                return $this->source;
            } else {
                throw new MDE_Exception\InvalidArgumentException(
                    sprintf('Source file "%s" not found or is not readable!', $this->filepath)
                );
            }
        }
        return '';
    }
    
// -------------------------
// Meta Data
// -------------------------

    /**
     * Get the content title
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->getMetadata('title');
    }

    /**
     * Get the meta-data stack or one entry
     *
     * @param   null/string
     * @return  string
     */
    public function getMetadata($index = null)
    {
        if (!is_null($index)) {
            return isset($this->metadata[$index]) ? $this->metadata[$index] : null;
        } else {
            return $this->metadata;
        }
    }

// -------------------------
// DOM IDs construction
// -------------------------

    /**
     * Verify if a reference is already defined in the DOM IDs register
     *
     * @param   string  $reference  The reference to search
     * @return  bool    True if the reference exists in the register, false otherwise
     */
    public function hasDomId($reference)
    {
        return isset($this->dom_ids[$reference]);
    }

    /**
     * Get a DOM unique ID 
     *
     * @param   string  $reference  A reference used to store the ID (and retrieve it - by default, a uniqid)
     * @param   null/string  $id
     * @return  string  The unique ID created or the existing one for the reference if so
     */
    public function getDomId($reference, $id = null)
    {
        return $this->hasDomId($reference) ?
            $this->dom_ids[$reference] : $this->setNewDomId(
                !empty($id) ? $id : $reference, $reference
            );
    }

    /**
     * Create and get a new DOM unique ID 
     *
     * @param   string      $id         A string that will be used to construct the ID
     * @param   string      $reference  A reference used to store the ID (and retrieve it - by default `$id`)
     * @param   bool        $return_array   Allow to return an array in case of existaing reference
     * @return  array/string    The unique ID created if the reference was empty
     *                          An array like (id=>XXX, reference=>YYY) if it was not
     */
    public function setNewDomId($id, $reference = null, $return_array = true)
    {
        $_reference = $reference;
        if (empty($_reference)) {
            $_reference = $id;
        }
        $new_id = $id;
        while (in_array($new_id, $this->dom_ids)) {
            $new_id = $id.'_'.uniqid();
        }
        if ($this->hasDomId($_reference)) {
            while (isset($this->dom_ids[$_reference])) {
                $_reference = $reference.'_'.uniqid();
            }
            $return = true===$return_array ? array(
                'id'=>$new_id, 'reference'=>$_reference
            ) : $new_id;
        } else {
            $return = $new_id;
        }
        $this->dom_ids[$_reference] = $new_id;
        return $return;
    }

}

// Endfile
