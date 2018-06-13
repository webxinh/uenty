<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Signers_BodySigner extends Swift_Signer
{
    
    public function signMessage(Swift_Message $message);

    
    public function getAlteredHeaders();
}
