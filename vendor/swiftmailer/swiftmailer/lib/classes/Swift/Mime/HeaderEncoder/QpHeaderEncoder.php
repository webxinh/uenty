<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_HeaderEncoder_QpHeaderEncoder extends Swift_Encoder_QpEncoder implements Swift_Mime_HeaderEncoder
{
    
    public function __construct(Swift_CharacterStream $charStream)
    {
        parent::__construct($charStream);
    }

    protected function initSafeMap()
    {
        foreach (array_merge(
            range(0x61, 0x7A), range(0x41, 0x5A),
            range(0x30, 0x39), array(0x20, 0x21, 0x2A, 0x2B, 0x2D, 0x2F)
        ) as $byte) {
            $this->_safeMap[$byte] = chr($byte);
        }
    }

    
    public function getName()
    {
        return 'Q';
    }

    
    public function encodeString($string, $firstLineOffset = 0, $maxLineLength = 0)
    {
        return str_replace(array(' ', '=20', "=\r\n"), array('_', '_', "\r\n"),
            parent::encodeString($string, $firstLineOffset, $maxLineLength)
        );
    }
}
