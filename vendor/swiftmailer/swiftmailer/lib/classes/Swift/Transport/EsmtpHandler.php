<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Transport_EsmtpHandler
{
    
    public function getHandledKeyword();

    
    public function setKeywordParams(array $parameters);

    
    public function afterEhlo(Swift_Transport_SmtpAgent $agent);

    
    public function getMailParams();

    
    public function getRcptParams();

    
    public function onCommand(Swift_Transport_SmtpAgent $agent, $command, $codes = array(), &$failedRecipients = null, &$stop = false);

    
    public function getPriorityOver($esmtpKeyword);

    
    public function exposeMixinMethods();

    
    public function resetState();
}
