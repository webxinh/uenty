<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_CharacterStream
{
    
    public function setCharacterSet($charset);

    
    public function setCharacterReaderFactory(Swift_CharacterReaderFactory $factory);

    
    public function importByteStream(Swift_OutputByteStream $os);

    
    public function importString($string);

    
    public function read($length);

    
    public function readBytes($length);

    
    public function write($chars);

    
    public function setPointer($charOffset);

    
    public function flushContents();
}
