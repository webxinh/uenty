<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Signers_HeaderSigner extends Swift_Signer, Swift_InputByteStream
{
    
    public function ignoreHeader($header_name);

    
    public function startBody();

    
    public function endBody();

    
    public function setHeaders(Swift_Mime_HeaderSet $headers);

    
    public function addSignature(Swift_Mime_HeaderSet $headers);

    
    public function getAlteredHeaders();
}
