<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Transport_SimpleMailInvoker implements Swift_Transport_MailInvoker
{
    
    public function mail($to, $subject, $body, $headers = null, $extraParams = null)
    {
        if (!ini_get('safe_mode')) {
            return @mail($to, $subject, $body, $headers, $extraParams);
        }

        return @mail($to, $subject, $body, $headers);
    }
}
