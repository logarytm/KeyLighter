<?php
/**
 * Highlighter
 *
 * Copyright (C) 2015, Some right reserved.
 *
 * @author Kacper "Kadet" Donat <kadet1090@gmail.com>
 *
 * Contact with author:
 * Xmpp: kadet@jid.pl
 * E-mail: kadet1090@gmail.com
 *
 * From Kadet with love.
 */

namespace Kadet\Highlighter\Language;

use Kadet\Highlighter\Matcher\WholeMatcher;
use Kadet\Highlighter\Parser\LanguageToken;
use Kadet\Highlighter\Parser\MetaToken;
use Kadet\Highlighter\Parser\Rule;
use Kadet\Highlighter\Parser\Token;
use Kadet\Highlighter\Parser\TokenFactory;
use Kadet\Highlighter\Parser\TokenIterator;
use Kadet\Highlighter\Parser\TokenList;
use Kadet\Highlighter\Utils\ArrayHelper;

/**
 * Class Language
 *
 * @package Kadet\Highlighter\Language
 */
abstract class Language
{
    /**
     * @var array
     */
    protected $_options = [];
    /**
     * Tokenizer rules
     *
     * @var Rule[]
     */
    private $_rules;

    /**
     * Language constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->_options = array_merge(
            [
                'embedded' => [],
            ], $this->_options, $options
        );

        $this->_rules = $this->getRules();
    }

    /**
     * Tokenization rules definition
     *
     * @return Rule[]|Rule[][]
     */
    abstract public function getRules();

    /**
     * Parses source and removes wrong tokens.
     *
     * @param TokenIterator|string $tokens
     *
     * @param array                $additional
     * @param bool                 $embedded
     *
     * @return TokenIterator
     */
    public function parse($tokens = null, $additional = [], $embedded = false)
    {
        if (is_string($tokens)) {
            $tokens = $this->tokenize($tokens, $additional, $embedded);
        } elseif (!$tokens instanceof TokenIterator) {
            // Todo: Own Exceptions
            throw new \InvalidArgumentException('$tokens must be string or TokenIterator');
        }

        $start = $tokens->current();
        $context = [];

        /** @var Token[] $result */
        $result = [$start];

        /** @var Token $token */
        for ($tokens->next(); $tokens->valid(); $tokens->next()) {
            $token = $tokens->current();

            if (!$token->isValid($this, $context)) {
                continue;
            }

            if ($token->isStart()) {
                if ($token instanceof LanguageToken) {
                    /** @var LanguageToken $token */
                    $result = array_merge(
                        $result,
                        $token->getInjected()->parse($tokens)->getTokens()
                    );
                } else {
                    if(!$token instanceof MetaToken) {
                        $result[] = $token;
                    }
                    $context[$tokens->key()] = $token->name;
                }
            } else {
                $start = $token->getStart();

                /** @noinspection PhpUndefinedMethodInspection bug */
                if ($token instanceof LanguageToken && $token->getLanguage() === $this) {
                    $result[0]->setEnd($token);

                    if ($result[0]->postProcess) {
                        $source = substr($tokens->getSource(), $result[0]->pos, $result[0]->getLength());

                        $tokens = $this->tokenize($source, $result, $result[0]->pos, true);
                        $result = $this->parse($tokens)->getTokens();
                    }

                    # closing unclosed tokens
                    foreach (array_reverse($context) as $hash => $name) {
                        $end = new Token([$name, 'pos' => $token->pos]);
                        $tokens[$hash]->setEnd($end);
                        $result[] = $end;
                    }

                    $result[] = $token;
                    break;
                } else {
                    if ($start) {
                        unset($context[spl_object_hash($start)]);
                    } else {
                        /** @noinspection PhpUnusedParameterInspection */
                        $start = ArrayHelper::find(
                            array_reverse($context), function ($k, $v) use ($token) {
                            return $v === $token->name;
                        });

                        if ($start !== false) {
                            $token->setStart($tokens[$start]);
                            unset($context[$start]);
                            $start = $tokens[$start];
                        }
                    }

                    if(!$start instanceof MetaToken) {
                        $result[] = $token;
                    }
                }
            }
        }

        return new TokenIterator($result, $tokens->getSource());
    }

    public function tokenize($source, $additional = [], $offset = 0, $embedded = false)
    {
        $iterator = new TokenIterator(
            $this->_tokens($source, $offset, $additional, $embedded)->sort()->toArray(), $source
        );

        return $iterator;
    }

    /**
     * Tokenize source
     *
     * @param       $source
     *
     * @param int   $offset
     * @param array $additional
     *
     * @param bool  $embedded
     *
     * @return TokenList
     */
    private function _tokens($source, $offset = 0, $additional = [], $embedded = false)
    {
        $result = new TokenList();

        /** @var Language $language */
        foreach ($this->_rules($embedded) as $rule) {
            $rule->factory->setOffset($offset);
            foreach ($rule->match($source) as $token) {
                $result->add($token);
            }
        }

        return $result->batch($additional);
    }

    /**
     * @param bool $embedded
     *
     * @return Rule[]
     */
    private function _rules($embedded = false)
    {
        $all = $this->_rules;
        if (!$embedded) {
            $all['language.' . $this->getIdentifier()] = $this->getOpenClose();
        }

        // why this code sucks so much? Because RecursiveIterator performance such a lot more.
        foreach ($all as $name => $rules) {
            if (!is_array($rules)) {
                $rules = [$rules];
            }

            /** @var Rule $rule */
            foreach ($rules as $rule) {
                if ($rule->language === false) {
                    $rule->language = $this;
                }

                $rule->factory->setBase($name);

                yield $rule;
            }
        }

        foreach ($this->getEmbedded() as $language) {
            foreach ($language->_rules() as $rule) {
                yield $rule;
            }
        }
    }

    /**
     * Unique language identifier, for example 'php'
     *
     * @return string
     */
    abstract public function getIdentifier();

    /**
     * Language range Rule(s)
     *
     * @return Rule|Rule[]
     */
    public function getOpenClose()
    {
        return new Rule(
            new WholeMatcher(), [
                'priority' => 1000,
                'factory'  => new TokenFactory(LanguageToken::class),
                'inject'   => $this,
                'language' => null,
                'context'  => Rule::everywhere(),
            ]
        );
    }

    /**
     * @return Language[]
     */
    public function getEmbedded()
    {
        return $this->_options['embedded'];
    }

    /**
     * @param Language $lang
     */
    public function embed(Language $lang)
    {
        $this->_options['embedded'][] = $lang;
    }

    public function __get($name)
    {
        return isset($this->_options[$name]) ? $this->_options[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->_options[$name] = $value;
    }
}
