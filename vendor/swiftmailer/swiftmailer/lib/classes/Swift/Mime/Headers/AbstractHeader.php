<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


abstract class Swift_Mime_Headers_AbstractHeader implements Swift_Mime_Header
{
    
    private $_name;

    
    private $_grammar;

    
    private $_encoder;

    
    private $_lineLength = 78;

    
    private $_lang;

    
    private $_charset = 'utf-8';

    
    private $_cachedValue = null;

    
    public function __construct(Swift_Mime_Grammar $grammar)
    {
        $this->setGrammar($grammar);
    }

    
    public function setCharset($charset)
    {
        $this->clearCachedValueIf($charset != $this->_charset);
        $this->_charset = $charset;
        if (isset($this->_encoder)) {
            $this->_encoder->charsetChanged($charset);
        }
    }

    
    public function getCharset()
    {
        return $this->_charset;
    }

    
    public function setLanguage($lang)
    {
        $this->clearCachedValueIf($this->_lang != $lang);
        $this->_lang = $lang;
    }

    
    public function getLanguage()
    {
        return $this->_lang;
    }

    
    public function setEncoder(Swift_Mime_HeaderEncoder $encoder)
    {
        $this->_encoder = $encoder;
        $this->setCachedValue(null);
    }

    
    public function getEncoder()
    {
        return $this->_encoder;
    }

    
    public function setGrammar(Swift_Mime_Grammar $grammar)
    {
        $this->_grammar = $grammar;
        $this->setCachedValue(null);
    }

    
    public function getGrammar()
    {
        return $this->_grammar;
    }

    
    public function getFieldName()
    {
        return $this->_name;
    }

    
    public function setMaxLineLength($lineLength)
    {
        $this->clearCachedValueIf($this->_lineLength != $lineLength);
        $this->_lineLength = $lineLength;
    }

    
    public function getMaxLineLength()
    {
        return $this->_lineLength;
    }

    
    public function toString()
    {
        return $this->_tokensToString($this->toTokens());
    }

    
    public function __toString()
    {
        return $this->toString();
    }

    // -- Points of extension

    
    protected function setFieldName($name)
    {
        $this->_name = $name;
    }

    
    protected function createPhrase(Swift_Mime_Header $header, $string, $charset, Swift_Mime_HeaderEncoder $encoder = null, $shorten = false)
    {
        // Treat token as exactly what was given
        $phraseStr = $string;
        // If it's not valid
        if (!preg_match('/^'.$this->getGrammar()->getDefinition('phrase').'$/D', $phraseStr)) {
            // .. but it is just ascii text, try escaping some characters
            // and make it a quoted-string
            if (preg_match('/^'.$this->getGrammar()->getDefinition('text').'*$/D', $phraseStr)) {
                $phraseStr = $this->getGrammar()->escapeSpecials(
                    $phraseStr, array('"'), $this->getGrammar()->getSpecials()
                    );
                $phraseStr = '"'.$phraseStr.'"';
            } else {
                // ... otherwise it needs encoding
                // Determine space remaining on line if first line
                if ($shorten) {
                    $usedLength = strlen($header->getFieldName().': ');
                } else {
                    $usedLength = 0;
                }
                $phraseStr = $this->encodeWords($header, $string, $usedLength);
            }
        }

        return $phraseStr;
    }

    
    protected function encodeWords(Swift_Mime_Header $header, $input, $usedLength = -1)
    {
        $value = '';

        $tokens = $this->getEncodableWordTokens($input);

        foreach ($tokens as $token) {
            // See RFC 2822, Sect 2.2 (really 2.2 ??)
            if ($this->tokenNeedsEncoding($token)) {
                // Don't encode starting WSP
                $firstChar = substr($token, 0, 1);
                switch ($firstChar) {
                    case ' ':
                    case "\t":
                        $value .= $firstChar;
                        $token = substr($token, 1);
                }

                if (-1 == $usedLength) {
                    $usedLength = strlen($header->getFieldName().': ') + strlen($value);
                }
                $value .= $this->getTokenAsEncodedWord($token, $usedLength);

                $header->setMaxLineLength(76); // Forcefully override
            } else {
                $value .= $token;
            }
        }

        return $value;
    }

    
    protected function tokenNeedsEncoding($token)
    {
        return preg_match('~[\x00-\x08\x10-\x19\x7F-\xFF\r\n]~', $token);
    }

    
    protected function getEncodableWordTokens($string)
    {
        $tokens = array();

        $encodedToken = '';
        // Split at all whitespace boundaries
        foreach (preg_split('~(?=[\t ])~', $string) as $token) {
            if ($this->tokenNeedsEncoding($token)) {
                $encodedToken .= $token;
            } else {
                if (strlen($encodedToken) > 0) {
                    $tokens[] = $encodedToken;
                    $encodedToken = '';
                }
                $tokens[] = $token;
            }
        }
        if (strlen($encodedToken)) {
            $tokens[] = $encodedToken;
        }

        return $tokens;
    }

    
    protected function getTokenAsEncodedWord($token, $firstLineOffset = 0)
    {
        // Adjust $firstLineOffset to account for space needed for syntax
        $charsetDecl = $this->_charset;
        if (isset($this->_lang)) {
            $charsetDecl .= '*'.$this->_lang;
        }
        $encodingWrapperLength = strlen(
            '=?'.$charsetDecl.'?'.$this->_encoder->getName().'??='
            );

        if ($firstLineOffset >= 75) {
            //Does this logic need to be here?
            $firstLineOffset = 0;
        }

        $encodedTextLines = explode("\r\n",
            $this->_encoder->encodeString(
                $token, $firstLineOffset, 75 - $encodingWrapperLength, $this->_charset
                )
        );

        if (strtolower($this->_charset) !== 'iso-2022-jp') {
            // special encoding for iso-2022-jp using mb_encode_mimeheader
            foreach ($encodedTextLines as $lineNum => $line) {
                $encodedTextLines[$lineNum] = '=?'.$charsetDecl.
                    '?'.$this->_encoder->getName().
                    '?'.$line.'?=';
            }
        }

        return implode("\r\n ", $encodedTextLines);
    }

    
    protected function generateTokenLines($token)
    {
        return preg_split('~(\r\n)~', $token, -1, PREG_SPLIT_DELIM_CAPTURE);
    }

    
    protected function setCachedValue($value)
    {
        $this->_cachedValue = $value;
    }

    
    protected function getCachedValue()
    {
        return $this->_cachedValue;
    }

    
    protected function clearCachedValueIf($condition)
    {
        if ($condition) {
            $this->setCachedValue(null);
        }
    }

    
    protected function toTokens($string = null)
    {
        if (is_null($string)) {
            $string = $this->getFieldBody();
        }

        $tokens = array();

        // Generate atoms; split at all invisible boundaries followed by WSP
        foreach (preg_split('~(?=[ \t])~', $string) as $token) {
            $newTokens = $this->generateTokenLines($token);
            foreach ($newTokens as $newToken) {
                $tokens[] = $newToken;
            }
        }

        return $tokens;
    }

    
    private function _tokensToString(array $tokens)
    {
        $lineCount = 0;
        $headerLines = array();
        $headerLines[] = $this->_name.': ';
        $currentLine = &$headerLines[$lineCount++];

        // Build all tokens back into compliant header
        foreach ($tokens as $i => $token) {
            // Line longer than specified maximum or token was just a new line
            if (("\r\n" == $token) ||
                ($i > 0 && strlen($currentLine.$token) > $this->_lineLength)
                && 0 < strlen($currentLine)) {
                $headerLines[] = '';
                $currentLine = &$headerLines[$lineCount++];
            }

            // Append token to the line
            if ("\r\n" != $token) {
                $currentLine .= $token;
            }
        }

        // Implode with FWS (RFC 2822, 2.2.3)
        return implode("\r\n", $headerLines)."\r\n";
    }
}
