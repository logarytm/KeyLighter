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

namespace Kadet\Highlighter\Parser\Token;


use Kadet\Highlighter\Language\Language;
use Kadet\Highlighter\Parser\Result;
use Kadet\Highlighter\Parser\TokenIterator;

/**
 * Class TerminatorToken
 *
 * @package Kadet\Highlighter\Parser\Token
 *
 * @property array $closes
 */
class TerminatorToken extends MetaToken
{
    protected function processStart(array &$context, Language $language, Result $result, TokenIterator $tokens)
    {
        return true; // That type of token makes no sense as start, just omit it.
    }

    protected function processEnd(array &$context, Language $language, Result $result, TokenIterator $tokens)
    {
        foreach(array_filter($context, function ($name) {
            return in_array($name, $this->closes);
        }) as $hash => $name) {
            $end = new Token([$name, 'pos' => $this->pos]);
            $tokens[$hash]->setEnd($end);
            $result->append($end);

            unset($context[$hash]);
        }

        return true;
    }

}