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

require_once __DIR__.'/../src/SplClassLoader.php';
$classLoader = new SplClassLoader('MarkdownExtended', __DIR__.'/../src');
$classLoader->register();
$classLoader_tests = new SplClassLoader('testsMarkdownExtended', __DIR__.'/../tests');
$classLoader_tests->register();
