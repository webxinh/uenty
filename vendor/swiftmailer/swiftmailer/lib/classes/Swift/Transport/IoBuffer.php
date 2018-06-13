<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Transport_IoBuffer extends Swift_InputByteStream, Swift_OutputByteStream
{
    
    const TYPE_SOCKET = 0x0001;

    
    const TYPE_PROCESS = 0x0010;

    
    public function initialize(array $params);

    
    public function setParam($param, $value);

    
    public function terminate();

    
    public function setWriteTranslations(array $replacements);

    
    public function readLine($sequence);
}
