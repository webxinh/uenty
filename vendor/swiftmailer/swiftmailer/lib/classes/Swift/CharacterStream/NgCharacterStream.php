<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_CharacterStream_NgCharacterStream implements Swift_CharacterStream
{
    
    private $_charReader;

    
    private $_charReaderFactory;

    
    private $_charset;

    
    private $_datas = '';

    
    private $_datasSize = 0;

    
    private $_map;

    
    private $_mapType = 0;

    
    private $_charCount = 0;

    
    private $_currentPos = 0;

    
    public function __construct(Swift_CharacterReaderFactory $factory, $charset)
    {
        $this->setCharacterReaderFactory($factory);
        $this->setCharacterSet($charset);
    }

    /* -- Changing parameters of the stream -- */

    
    public function setCharacterSet($charset)
    {
        $this->_charset = $charset;
        $this->_charReader = null;
        $this->_mapType = 0;
    }

    
    public function setCharacterReaderFactory(Swift_CharacterReaderFactory $factory)
    {
        $this->_charReaderFactory = $factory;
    }

    
    public function flushContents()
    {
        $this->_datas = null;
        $this->_map = null;
        $this->_charCount = 0;
        $this->_currentPos = 0;
        $this->_datasSize = 0;
    }

    
    public function importByteStream(Swift_OutputByteStream $os)
    {
        $this->flushContents();
        $blocks = 512;
        $os->setReadPointer(0);
        while (false !== ($read = $os->read($blocks))) {
            $this->write($read);
        }
    }

    
    public function importString($string)
    {
        $this->flushContents();
        $this->write($string);
    }

    
    public function read($length)
    {
        if ($this->_currentPos >= $this->_charCount) {
            return false;
        }
        $ret = false;
        $length = $this->_currentPos + $length > $this->_charCount ? $this->_charCount - $this->_currentPos : $length;
        switch ($this->_mapType) {
            case Swift_CharacterReader::MAP_TYPE_FIXED_LEN:
                $len = $length * $this->_map;
                $ret = substr($this->_datas,
                        $this->_currentPos * $this->_map,
                        $len);
                $this->_currentPos += $length;
                break;

            case Swift_CharacterReader::MAP_TYPE_INVALID:
                $ret = '';
                for (; $this->_currentPos < $length; ++$this->_currentPos) {
                    if (isset($this->_map[$this->_currentPos])) {
                        $ret .= '?';
                    } else {
                        $ret .= $this->_datas[$this->_currentPos];
                    }
                }
                break;

            case Swift_CharacterReader::MAP_TYPE_POSITIONS:
                $end = $this->_currentPos + $length;
                $end = $end > $this->_charCount ? $this->_charCount : $end;
                $ret = '';
                $start = 0;
                if ($this->_currentPos > 0) {
                    $start = $this->_map['p'][$this->_currentPos - 1];
                }
                $to = $start;
                for (; $this->_currentPos < $end; ++$this->_currentPos) {
                    if (isset($this->_map['i'][$this->_currentPos])) {
                        $ret .= substr($this->_datas, $start, $to - $start).'?';
                        $start = $this->_map['p'][$this->_currentPos];
                    } else {
                        $to = $this->_map['p'][$this->_currentPos];
                    }
                }
                $ret .= substr($this->_datas, $start, $to - $start);
                break;
        }

        return $ret;
    }

    
    public function readBytes($length)
    {
        $read = $this->read($length);
        if ($read !== false) {
            $ret = array_map('ord', str_split($read, 1));

            return $ret;
        }

        return false;
    }

    
    public function setPointer($charOffset)
    {
        if ($this->_charCount < $charOffset) {
            $charOffset = $this->_charCount;
        }
        $this->_currentPos = $charOffset;
    }

    
    public function write($chars)
    {
        if (!isset($this->_charReader)) {
            $this->_charReader = $this->_charReaderFactory->getReaderFor(
                $this->_charset);
            $this->_map = array();
            $this->_mapType = $this->_charReader->getMapType();
        }
        $ignored = '';
        $this->_datas .= $chars;
        $this->_charCount += $this->_charReader->getCharPositions(substr($this->_datas, $this->_datasSize), $this->_datasSize, $this->_map, $ignored);
        if ($ignored !== false) {
            $this->_datasSize = strlen($this->_datas) - strlen($ignored);
        } else {
            $this->_datasSize = strlen($this->_datas);
        }
    }
}
