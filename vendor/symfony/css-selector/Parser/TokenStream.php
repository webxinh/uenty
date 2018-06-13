<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\Parser;

use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;


class TokenStream
{
    
    private $tokens = array();

    
    private $frozen = false;

    
    private $used = array();

    
    private $cursor = 0;

    
    private $peeked = null;

    
    private $peeking = false;

    
    public function push(Token $token)
    {
        $this->tokens[] = $token;

        return $this;
    }

    
    public function freeze()
    {
        $this->frozen = true;

        return $this;
    }

    
    public function getNext()
    {
        if ($this->peeking) {
            $this->peeking = false;
            $this->used[] = $this->peeked;

            return $this->peeked;
        }

        if (!isset($this->tokens[$this->cursor])) {
            throw new InternalErrorException('Unexpected token stream end.');
        }

        return $this->tokens[$this->cursor++];
    }

    
    public function getPeek()
    {
        if (!$this->peeking) {
            $this->peeked = $this->getNext();
            $this->peeking = true;
        }

        return $this->peeked;
    }

    
    public function getUsed()
    {
        return $this->used;
    }

    
    public function getNextIdentifier()
    {
        $next = $this->getNext();

        if (!$next->isIdentifier()) {
            throw SyntaxErrorException::unexpectedToken('identifier', $next);
        }

        return $next->getValue();
    }

    
    public function getNextIdentifierOrStar()
    {
        $next = $this->getNext();

        if ($next->isIdentifier()) {
            return $next->getValue();
        }

        if ($next->isDelimiter(array('*'))) {
            return;
        }

        throw SyntaxErrorException::unexpectedToken('identifier or "*"', $next);
    }

    
    public function skipWhitespace()
    {
        $peek = $this->getPeek();

        if ($peek->isWhitespace()) {
            $this->getNext();
        }
    }
}
