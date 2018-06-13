<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_ContentEncoder_QpContentEncoderProxy implements Swift_Mime_ContentEncoder
{
    
    private $safeEncoder;

    
    private $nativeEncoder;

    
    private $charset;

    
    public function __construct(Swift_Mime_ContentEncoder_QpContentEncoder $safeEncoder, Swift_Mime_ContentEncoder_NativeQpContentEncoder $nativeEncoder, $charset)
    {
        $this->safeEncoder = $safeEncoder;
        $this->nativeEncoder = $nativeEncoder;
        $this->charset = $charset;
    }

    
    public function __clone()
    {
        $this->safeEncoder = clone $this->safeEncoder;
        $this->nativeEncoder = clone $this->nativeEncoder;
    }

    
    public function charsetChanged($charset)
    {
        $this->charset = $charset;
        $this->safeEncoder->charsetChanged($charset);
    }

    
    public function encodeByteStream(Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0, $maxLineLength = 0)
    {
        $this->getEncoder()->encodeByteStream($os, $is, $firstLineOffset, $maxLineLength);
    }

    
    public function getName()
    {
        return 'quoted-printable';
    }

    
    public function encodeString($string, $firstLineOffset = 0, $maxLineLength = 0)
    {
        return $this->getEncoder()->encodeString($string, $firstLineOffset, $maxLineLength);
    }

    
    private function getEncoder()
    {
        return 'utf-8' === $this->charset ? $this->nativeEncoder : $this->safeEncoder;
    }
}
