<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Mime_HeaderFactory extends Swift_Mime_CharsetObserver
{
    
    public function createMailboxHeader($name, $addresses = null);

    
    public function createDateHeader($name, $timestamp = null);

    
    public function createTextHeader($name, $value = null);

    
    public function createParameterizedHeader($name, $value = null, $params = array());

    
    public function createIdHeader($name, $ids = null);

    
    public function createPathHeader($name, $path = null);
}