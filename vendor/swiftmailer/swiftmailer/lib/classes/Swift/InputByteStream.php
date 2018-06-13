<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_InputByteStream
{
    
    public function write($bytes);

    
    public function commit();

    
    public function bind(Swift_InputByteStream $is);

    
    public function unbind(Swift_InputByteStream $is);

    
    public function flushBuffers();
}
