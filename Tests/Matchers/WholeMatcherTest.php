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

namespace Kadet\KeyLighter\Tests\Matchers;

require_once __DIR__.'/../MatcherTestCase.php';

use Kadet\Highlighter\Matcher\WholeMatcher;
use Kadet\KeyLighter\Tests\MatcherTestCase;

class WholeMatcherTest extends MatcherTestCase
{
    public function testWholeMatcher() {
        $matcher = new WholeMatcher();

        $this->assertTokens([
            ['start', 'pos' => 0],
            ['end', 'pos' => 10],
        ], $matcher->match('1234567890', $this->getFactory()));
    }
}
