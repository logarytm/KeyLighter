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

namespace Kadet\Highlighter\Language;


use Kadet\Highlighter\Matcher\RegexMatcher;
use Kadet\Highlighter\Matcher\WordMatcher;
use Kadet\Highlighter\Parser\Rule;
use Kadet\Highlighter\Parser\Token\Token;

class CSharp extends C
{
    public function setupRules()
    {
        parent::setupRules();

        $this->rules->rule('preprocessor')->setMatcher(new RegexMatcher('/^\s*(#)/m'));
        $this->rules->rule('call.preprocessor')->setMatcher(new RegexMatcher('/^\s*#(\w+)/m'));

        $this->rules->remove('operator'); // & and *
        $this->rules->remove('symbol.type', 0); // symbol.type[0] stands for universal type matching in arguments

        $this->rules->rule('keyword')->setMatcher(new WordMatcher([
            'abstract', 'as', 'base', 'break', 'case', 'catch', 'char', 'checked', 'class', 'const', 'continue',
            'default', 'delegate', 'do', 'else', 'enum', 'event', 'explicit', 'extern', 'finally', 'fixed', 'for',
            'foreach', 'goto', 'if', 'implicit', 'in', 'interface', 'internal', 'is', 'lock', 'namespace', 'new',
            'object', 'operator', 'out', 'override', 'partial', 'params', 'private', 'protected', 'public', 'readonly', 'ref',
            'return', 'sealed', 'short', 'sizeof', 'stackalloc', 'static', 'string', 'struct', 'switch', 'throw', 'try',
            'typeof', 'unchecked', 'unsafe', 'using', 'virtual', 'volatile', 'var', 'while', 'yield',
            '__makeref', '__reftype', '__refvalue', '__arglist', 'get', 'set'
        ]));

        $this->rules->addMany([
            'symbol.class'          => new Rule(new RegexMatcher('/(\w+)(?:\s+|\s*[*&]\s*)\w+\s*[={}();,]/')),
            'symbol.class.template' => new Rule(new RegexMatcher('/(\w+)\s*<.*?>/')),
            'variable.special'      => new Rule(new RegexMatcher('/\b(this)\b/')),
            'constant.special'      => new Rule(new WordMatcher(['true', 'false', 'null'])),
            'operator'              => new Rule(new RegexMatcher('/([!+-\/*&|^<>=]{1,2}=?)/')),
            'operator.scope'        => new Rule(new RegexMatcher('/\w(\??\.)\w/')),

            'annotation' => new Rule(
                new RegexMatcher('/\[([\w\.]+)\s*(?P<arguments>\((?>[^()]+|(?&arguments))*\))\s*\]/si', [
                    1           => Token::NAME,
                    'arguments' => '$.arguments'
                ])
            ),
        ]);
    }

    public function getIdentifier()
    {
        return 'csharp';
    }
}
