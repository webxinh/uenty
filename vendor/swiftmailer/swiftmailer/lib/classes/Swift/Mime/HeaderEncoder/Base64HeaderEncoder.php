<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_HeaderEncoder_Base64HeaderEncoder extends Swift_Encoder_Base64Encoder implements Swift_Mime_HeaderEncoder
{
    
    public function getName()
    {
        return 'B';
    }

    
    public function encodeString($string, $firstLineOffset = 0, $maxLineLength = 0, $charset = 'utf-8')
    {
        if (strtolower($charset) === 'iso-2022-jp') {
            $old = mb_internal_encoding();
            mb_internal_encoding('utf-8');
            $newstring = mb_encode_mimeheader($string, $charset, $this->getName(), "\r\n");
            mb_internal_encoding($old);

            return $newstring;
        }

        return parent::encodeString($string, $firstLineOffset, $maxLineLength);
    }
}
