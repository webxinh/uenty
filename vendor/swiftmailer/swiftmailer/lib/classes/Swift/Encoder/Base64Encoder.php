<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Encoder_Base64Encoder implements Swift_Encoder
{
    
    public function encodeString($string, $firstLineOffset = 0, $maxLineLength = 0)
    {
        if (0 >= $maxLineLength || 76 < $maxLineLength) {
            $maxLineLength = 76;
        }

        $encodedString = base64_encode($string);
        $firstLine = '';

        if (0 != $firstLineOffset) {
            $firstLine = substr(
                $encodedString, 0, $maxLineLength - $firstLineOffset
                )."\r\n";
            $encodedString = substr(
                $encodedString, $maxLineLength - $firstLineOffset
                );
        }

        return $firstLine.trim(chunk_split($encodedString, $maxLineLength, "\r\n"));
    }

    
    public function charsetChanged($charset)
    {
    }
}
