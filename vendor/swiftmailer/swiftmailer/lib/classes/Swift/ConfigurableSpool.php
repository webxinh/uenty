<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


abstract class Swift_ConfigurableSpool implements Swift_Spool
{
    
    private $_message_limit;

    
    private $_time_limit;

    
    public function setMessageLimit($limit)
    {
        $this->_message_limit = (int) $limit;
    }

    
    public function getMessageLimit()
    {
        return $this->_message_limit;
    }

    
    public function setTimeLimit($limit)
    {
        $this->_time_limit = (int) $limit;
    }

    
    public function getTimeLimit()
    {
        return $this->_time_limit;
    }
}
