<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_ByteStream_ArrayByteStream implements Swift_InputByteStream, Swift_OutputByteStream
{
    
    private $_array = array();

    
    private $_arraySize = 0;

    
    private $_offset = 0;

    
    private $_mirrors = array();

    
    public function __construct($stack = null)
    {
        if (is_array($stack)) {
            $this->_array = $stack;
            $this->_arraySize = count($stack);
        } elseif (is_string($stack)) {
            $this->write($stack);
        } else {
            $this->_array = array();
        }
    }

    
    public function read($length)
    {
        if ($this->_offset == $this->_arraySize) {
            return false;
        }

        // Don't use array slice
        $end = $length + $this->_offset;
        $end = $this->_arraySize < $end ? $this->_arraySize : $end;
        $ret = '';
        for (; $this->_offset < $end; ++$this->_offset) {
            $ret .= $this->_array[$this->_offset];
        }

        return $ret;
    }

    
    public function write($bytes)
    {
        $to_add = str_split($bytes);
        foreach ($to_add as $value) {
            $this->_array[] = $value;
        }
        $this->_arraySize = count($this->_array);

        foreach ($this->_mirrors as $stream) {
            $stream->write($bytes);
        }
    }

    
    public function commit()
    {
    }

    
    public function bind(Swift_InputByteStream $is)
    {
        $this->_mirrors[] = $is;
    }

    
    public function unbind(Swift_InputByteStream $is)
    {
        foreach ($this->_mirrors as $k => $stream) {
            if ($is === $stream) {
                unset($this->_mirrors[$k]);
            }
        }
    }

    
    public function setReadPointer($byteOffset)
    {
        if ($byteOffset > $this->_arraySize) {
            $byteOffset = $this->_arraySize;
        } elseif ($byteOffset < 0) {
            $byteOffset = 0;
        }

        $this->_offset = $byteOffset;
    }

    
    public function flushBuffers()
    {
        $this->_offset = 0;
        $this->_array = array();
        $this->_arraySize = 0;

        foreach ($this->_mirrors as $stream) {
            $stream->flushBuffers();
        }
    }
}
