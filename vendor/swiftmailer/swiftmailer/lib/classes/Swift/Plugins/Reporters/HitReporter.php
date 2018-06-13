<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Plugins_Reporters_HitReporter implements Swift_Plugins_Reporter
{
    
    private $_failures = array();

    private $_failures_cache = array();

    
    public function notify(Swift_Mime_Message $message, $address, $result)
    {
        if (self::RESULT_FAIL == $result && !isset($this->_failures_cache[$address])) {
            $this->_failures[] = $address;
            $this->_failures_cache[$address] = true;
        }
    }

    
    public function getFailedRecipients()
    {
        return $this->_failures;
    }

    
    public function clear()
    {
        $this->_failures = $this->_failures_cache = array();
    }
}
