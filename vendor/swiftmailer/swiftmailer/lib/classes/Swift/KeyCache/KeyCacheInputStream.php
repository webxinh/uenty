<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_KeyCache_KeyCacheInputStream extends Swift_InputByteStream
{
    
    public function setKeyCache(Swift_KeyCache $keyCache);

    
    public function setNsKey($nsKey);

    
    public function setItemKey($itemKey);

    
    public function setWriteThroughStream(Swift_InputByteStream $is);

    
    public function __clone();
}
