<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_RfcComplianceException extends Swift_SwiftException
{
    
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
