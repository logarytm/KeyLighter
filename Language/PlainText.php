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

class PlainText extends Language
{

    /**
     * Tokenization rules
     */
    public function setupRules() { }

    /** {@inheritdoc} */
    public function getIdentifier()
    {
        return 'plaintext';
    }
}
