<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Plugins_Reporter
{
    
    const RESULT_PASS = 0x01;

    
    const RESULT_FAIL = 0x10;

    
    public function notify(Swift_Mime_Message $message, $address, $result);
}
