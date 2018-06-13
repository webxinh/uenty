<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Mime_ParameterizedHeader extends Swift_Mime_Header
{
    
    public function setParameter($parameter, $value);

    
    public function getParameter($parameter);
}
