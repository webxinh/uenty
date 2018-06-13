<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_KeyCache_ArrayKeyCache implements Swift_KeyCache
{
    
    private $_contents = array();

    
    private $_stream;

    
    public function __construct(Swift_KeyCache_KeyCacheInputStream $stream)
    {
        $this->_stream = $stream;
    }

    
    public function setString($nsKey, $itemKey, $string, $mode)
    {
        $this->_prepareCache($nsKey);
        switch ($mode) {
            case self::MODE_WRITE:
                $this->_contents[$nsKey][$itemKey] = $string;
                break;
            case self::MODE_APPEND:
                if (!$this->hasKey($nsKey, $itemKey)) {
                    $this->_contents[$nsKey][$itemKey] = '';
                }
                $this->_contents[$nsKey][$itemKey] .= $string;
                break;
            default:
                throw new Swift_SwiftException(
                    'Invalid mode ['.$mode.'] used to set nsKey='.
                    $nsKey.', itemKey='.$itemKey
                    );
        }
    }

    
    public function importFromByteStream($nsKey, $itemKey, Swift_OutputByteStream $os, $mode)
    {
        $this->_prepareCache($nsKey);
        switch ($mode) {
            case self::MODE_WRITE:
                $this->clearKey($nsKey, $itemKey);
            case self::MODE_APPEND:
                if (!$this->hasKey($nsKey, $itemKey)) {
                    $this->_contents[$nsKey][$itemKey] = '';
                }
                while (false !== $bytes = $os->read(8192)) {
                    $this->_contents[$nsKey][$itemKey] .= $bytes;
                }
                break;
            default:
                throw new Swift_SwiftException(
                    'Invalid mode ['.$mode.'] used to set nsKey='.
                    $nsKey.', itemKey='.$itemKey
                    );
        }
    }

    
    public function getInputByteStream($nsKey, $itemKey, Swift_InputByteStream $writeThrough = null)
    {
        $is = clone $this->_stream;
        $is->setKeyCache($this);
        $is->setNsKey($nsKey);
        $is->setItemKey($itemKey);
        if (isset($writeThrough)) {
            $is->setWriteThroughStream($writeThrough);
        }

        return $is;
    }

    
    public function getString($nsKey, $itemKey)
    {
        $this->_prepareCache($nsKey);
        if ($this->hasKey($nsKey, $itemKey)) {
            return $this->_contents[$nsKey][$itemKey];
        }
    }

    
    public function exportToByteStream($nsKey, $itemKey, Swift_InputByteStream $is)
    {
        $this->_prepareCache($nsKey);
        $is->write($this->getString($nsKey, $itemKey));
    }

    
    public function hasKey($nsKey, $itemKey)
    {
        $this->_prepareCache($nsKey);

        return array_key_exists($itemKey, $this->_contents[$nsKey]);
    }

    
    public function clearKey($nsKey, $itemKey)
    {
        unset($this->_contents[$nsKey][$itemKey]);
    }

    
    public function clearAll($nsKey)
    {
        unset($this->_contents[$nsKey]);
    }

    
    private function _prepareCache($nsKey)
    {
        if (!array_key_exists($nsKey, $this->_contents)) {
            $this->_contents[$nsKey] = array();
        }
    }
}
