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


use Kadet\Highlighter\KeyLighter;
use Kadet\Highlighter\Matcher\DelegateRegexMatcher;
use Kadet\Highlighter\Matcher\RegexMatcher;
use Kadet\Highlighter\Parser\Rule;
use Kadet\Highlighter\Parser\Token\LanguageToken;
use Kadet\Highlighter\Parser\Token\Token;
use Kadet\Highlighter\Parser\TokenFactoryInterface;
use Kadet\Highlighter\Parser\Validator\Validator;

class Markdown extends GreedyLanguage
{
    protected $_options = [
        'variables' => false,
    ];

    /**
     * Tokenization rules setup
     */
    public function setupRules()
    {
        $this->rules->validator = new Validator(['!format.block.code', '!format.monospace', '!keyword.escape', '!operator']);
        $this->rules->addMany([
            'format.header' => [
                new Rule(new RegexMatcher('/^(#+.+?)$/m')),
                new Rule(new RegexMatcher('/^(.+?)^(?:-+|=+)$/m'))
            ],
            'format.italics'   => new Rule(
                new RegexMatcher('/(?:^|[^*_])(?P<italics>(?P<i>[*_])(?>[^*_\n]|(?:(?P<b>[*_]{2})(?>[^*_\n]|(?&italics))*?\g{b}))+\g{i})/'), [
                    'italics' => Token::NAME
                ]
            ),
            'format.emphasis'  => new Rule(
                new RegexMatcher('/(?P<bold>(?P<b>\*\*|__)(?>[^*_\n]|(?:(?P<i>[*_]{2})(?>[^*_\n]|(?&bold))*?\g{i}))+\g{b})/', [
                    'bold' => Token::NAME
                ])
            ),
            'format.strike'    => new Rule(new RegexMatcher('/(~~.+?~~)/')),
            'format.monospace' => new Rule(new RegexMatcher('/[^`](`[^`]+?`)/')),

            'operator.list.ordered'   => new Rule(new RegexMatcher('/^\s*(\d+[.)])/m')),
            'operator.list.unordered' => new Rule(new RegexMatcher('/^\s*([-+*])/m'), [
                'context' => ['none'],
                'priority' => 1
            ]),

            'string.quote'       => new Rule(new RegexMatcher('/((?:^>.*?\n)+)/m')),
            'format.block.code'  => new Rule(
                new DelegateRegexMatcher(
                    '/^```(.*?)\r?\n(.*?)\r?\n^```/ms',
                    function($match, TokenFactoryInterface $factory) {
                        $lang = KeyLighter::get()->getLanguage($match[1][0]);
                        yield $factory->create(null, ['pos' => $match[0][1], 'length' => strlen($match[0][0])]);
                        yield $factory->create(
                            "language.{$lang->getIdentifier()}", [
                                'pos'           => $match[2][1],
                                'length'        => strlen($match[2][0]),
                                'postProcessed' => true,
                                'inject'        => $lang,
                                'class'         => LanguageToken::class,
                                'language'      => $this
                            ]
                        );
                    }
                ), [
                    'context'     => Validator::everywhere(),
                    'postProcess' => true,
                    'priority'    => 1000
                ]
            ),

            'keyword.escape' => new Rule(new RegexMatcher('/(\\\.)/')),

            'operator.horizontal' => new Rule(new RegexMatcher('/^(-+)\s*$/m')),
        ]);
    }

    /**
     * Unique language identifier, for example 'php'
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'markdown';
    }
}