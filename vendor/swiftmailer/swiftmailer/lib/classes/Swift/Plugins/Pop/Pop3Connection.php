<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Plugins_Pop_Pop3Connection
{
    
    public function connect();

    
    public function disconnect();
}
