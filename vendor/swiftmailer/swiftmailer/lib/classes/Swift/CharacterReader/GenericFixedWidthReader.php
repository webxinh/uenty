<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_CharacterReader_GenericFixedWidthReader implements Swift_CharacterReader
{
    
    private $_width;

    
    public function __construct($width)
    {
        $this->_width = $width;
    }

    
    public function getCharPositions($string, $startOffset, &$currentMap, &$ignoredChars)
    {
        $strlen = strlen($string);
        // % and / are CPU intensive, so, maybe find a better way
        $ignored = $strlen % $this->_width;
        $ignoredChars = $ignored ? substr($string, -$ignored) : '';
        $currentMap = $this->_width;

        return ($strlen - $ignored) / $this->_width;
    }

    
    public function getMapType()
    {
        return self::MAP_TYPE_FIXED_LEN;
    }

    
    public function validateByteSequence($bytes, $size)
    {
        $needed = $this->_width - $size;

        return $needed > -1 ? $needed : -1;
    }

    
    public function getInitialByteSize()
    {
        return $this->_width;
    }
}
