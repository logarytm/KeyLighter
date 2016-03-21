<?php
/**
 * Highlighter
 *
 * Copyright (C) 2016, Some right reserved.
 *
 * @author Kacper "Kadet" Donat <kacper@kadet.net>
 *
 * Contact with author:
 * Xmpp: me@kadet.net
 * E-mail: contact@kadet.net
 *
 * From Kadet with love.
 */

namespace Kadet\Highlighter\Tests\Matchers;

use Kadet\Highlighter\Matcher\WordMatcher;
use Kadet\Highlighter\Tests\MatcherTestCase;

class WordMatcherTest extends MatcherTestCase
{
    public function testSimple()
    {
        $source  = 'first second seconder';
        $matcher = new WordMatcher(['first', 'second']);

        $this->assertTokens([
            ['start', 'pos' => 0],
            ['end', 'pos' => 5],
            ['start', 'pos' => 6],
            ['end', 'pos' => 12],
        ], $matcher->match($source, $this->getFactory()));
    }

    public function testNonSeparated()
    {
        $source  = 'first firster';
        $matcher = new WordMatcher(['first'], [
            'separated' => false
        ]);

        $this->assertTokens([
            ['start', 'pos' => 0],
            ['end', 'pos' => 5],
            ['start', 'pos' => 6],
            ['end', 'pos' => 11],
        ], $matcher->match($source, $this->getFactory()));
    }

    public function testCaseInsensitive()
    {
        $source  = 'first FIRST';
        $matcher = new WordMatcher(['first']);

        $this->assertTokens([
            ['start', 'pos' => 0],
            ['end', 'pos' => 5],
            ['start', 'pos' => 6],
            ['end', 'pos' => 11],
        ], $matcher->match($source, $this->getFactory()));
    }

    public function testCaseSensitive()
    {
        $source  = 'first FIRST';
        $matcher = new WordMatcher(['first'], [
            'case-sensitivity' => true
        ]);

        $this->assertTokens([
            ['start', 'pos' => 0],
            ['end', 'pos' => 5],
        ], $matcher->match($source, $this->getFactory()));
    }

    public function testNonEscaped()
    {
        $source  = 'first abcdef';
        $matcher = new WordMatcher(['\w+'], [
            'escape' => false
        ]);

        $this->assertTokens([
            ['start', 'pos' => 0],
            ['end', 'pos' => 5],
            ['start', 'pos' => 6],
            ['end', 'pos' => 12],
        ], $matcher->match($source, $this->getFactory()));
    }
}
