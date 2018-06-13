<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_ContentEncoder_PlainContentEncoder implements Swift_Mime_ContentEncoder
{
    
    private $_name;

    
    private $_canonical;

    
    public function __construct($name, $canonical = false)
    {
        $this->_name = $name;
        $this->_canonical = $canonical;
    }

    
    public function encodeString($string, $firstLineOffset = 0, $maxLineLength = 0)
    {
        if ($this->_canonical) {
            $string = $this->_canonicalize($string);
        }

        return $this->_safeWordWrap($string, $maxLineLength, "\r\n");
    }

    
    public function encodeByteStream(Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0, $maxLineLength = 0)
    {
        $leftOver = '';
        while (false !== $bytes = $os->read(8192)) {
            $toencode = $leftOver.$bytes;
            if ($this->_canonical) {
                $toencode = $this->_canonicalize($toencode);
            }
            $wrapped = $this->_safeWordWrap($toencode, $maxLineLength, "\r\n");
            $lastLinePos = strrpos($wrapped, "\r\n");
            $leftOver = substr($wrapped, $lastLinePos);
            $wrapped = substr($wrapped, 0, $lastLinePos);

            $is->write($wrapped);
        }
        if (strlen($leftOver)) {
            $is->write($leftOver);
        }
    }

    
    public function getName()
    {
        return $this->_name;
    }

    
    public function charsetChanged($charset)
    {
    }

    
    private function _safeWordwrap($string, $length = 75, $le = "\r\n")
    {
        if (0 >= $length) {
            return $string;
        }

        $originalLines = explode($le, $string);

        $lines = array();
        $lineCount = 0;

        foreach ($originalLines as $originalLine) {
            $lines[] = '';
            $currentLine = &$lines[$lineCount++];

            //$chunks = preg_split('/(?<=[\ \t,\.!\?\-&\+\/])/', $originalLine);
            $chunks = preg_split('/(?<=\s)/', $originalLine);

            foreach ($chunks as $chunk) {
                if (0 != strlen($currentLine)
                    && strlen($currentLine.$chunk) > $length) {
                    $lines[] = '';
                    $currentLine = &$lines[$lineCount++];
                }
                $currentLine .= $chunk;
            }
        }

        return implode("\r\n", $lines);
    }

    
    private function _canonicalize($string)
    {
        return str_replace(
            array("\r\n", "\r", "\n"),
            array("\n", "\n", "\r\n"),
            $string
            );
    }
}
