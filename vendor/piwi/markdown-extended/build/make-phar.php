#!/usr/bin/env php
<?php
/**
 * PHP Markdown Extended
 * Copyright (c) 2008-2014 Pierre Cassat
 *
 * original MultiMarkdown
 * Copyright (c) 2005-2009 Fletcher T. Penney
 * <http://fletcherpenney.net/>
 *
 * original PHP Markdown & Extra
 * Copyright (c) 2004-2012 Michel Fortin  
 * <http://michelf.com/projects/php-markdown/>
 *
 * original Markdown
 * Copyright (c) 2004-2006 John Gruber  
 * <http://daringfireball.net/projects/markdown/>
 */

// show errors at least initially
@ini_set('display_errors','1'); @error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

// namespaces loader
require __DIR__.'/../src/bootstrap.php';

// phar compiler
use MarkdownExtended\Util\Compiler;

// silent errors
@error_reporting(-1);

// phar compilation
try {
    $compiler = new Compiler();
    $logs = $compiler->compile();
    echo "> ok, phar generated with files:".PHP_EOL;
    var_export($logs);
    echo PHP_EOL;
    exit(0);
} catch (\Exception $e) {
    echo 'Failed to compile phar: ['.get_class($e).'] '
        .$e->getMessage().' at '.$e->getFile().':'.$e->getLine();
    exit(1);
}

// Endfile