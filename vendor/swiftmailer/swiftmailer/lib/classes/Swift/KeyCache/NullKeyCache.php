<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_KeyCache_NullKeyCache implements Swift_KeyCache
{
    
    public function setString($nsKey, $itemKey, $string, $mode)
    {
    }

    
    public function importFromByteStream($nsKey, $itemKey, Swift_OutputByteStream $os, $mode)
    {
    }

    
    public function getInputByteStream($nsKey, $itemKey, Swift_InputByteStream $writeThrough = null)
    {
    }

    
    public function getString($nsKey, $itemKey)
    {
    }

    
    public function exportToByteStream($nsKey, $itemKey, Swift_InputByteStream $is)
    {
    }

    
    public function hasKey($nsKey, $itemKey)
    {
        return false;
    }

    
    public function clearKey($nsKey, $itemKey)
    {
    }

    
    public function clearAll($nsKey)
    {
    }
}
