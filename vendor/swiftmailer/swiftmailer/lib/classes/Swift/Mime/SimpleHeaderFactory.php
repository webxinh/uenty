<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_SimpleHeaderFactory implements Swift_Mime_HeaderFactory
{
    
    private $_encoder;

    
    private $_paramEncoder;

    
    private $_grammar;

    
    private $_charset;

    
    public function __construct(Swift_Mime_HeaderEncoder $encoder, Swift_Encoder $paramEncoder, Swift_Mime_Grammar $grammar, $charset = null)
    {
        $this->_encoder = $encoder;
        $this->_paramEncoder = $paramEncoder;
        $this->_grammar = $grammar;
        $this->_charset = $charset;
    }

    
    public function createMailboxHeader($name, $addresses = null)
    {
        $header = new Swift_Mime_Headers_MailboxHeader($name, $this->_encoder, $this->_grammar);
        if (isset($addresses)) {
            $header->setFieldBodyModel($addresses);
        }
        $this->_setHeaderCharset($header);

        return $header;
    }

    
    public function createDateHeader($name, $timestamp = null)
    {
        $header = new Swift_Mime_Headers_DateHeader($name, $this->_grammar);
        if (isset($timestamp)) {
            $header->setFieldBodyModel($timestamp);
        }
        $this->_setHeaderCharset($header);

        return $header;
    }

    
    public function createTextHeader($name, $value = null)
    {
        $header = new Swift_Mime_Headers_UnstructuredHeader($name, $this->_encoder, $this->_grammar);
        if (isset($value)) {
            $header->setFieldBodyModel($value);
        }
        $this->_setHeaderCharset($header);

        return $header;
    }

    
    public function createParameterizedHeader($name, $value = null,
        $params = array())
    {
        $header = new Swift_Mime_Headers_ParameterizedHeader($name, $this->_encoder, strtolower($name) == 'content-disposition' ? $this->_paramEncoder : null, $this->_grammar);
        if (isset($value)) {
            $header->setFieldBodyModel($value);
        }
        foreach ($params as $k => $v) {
            $header->setParameter($k, $v);
        }
        $this->_setHeaderCharset($header);

        return $header;
    }

    
    public function createIdHeader($name, $ids = null)
    {
        $header = new Swift_Mime_Headers_IdentificationHeader($name, $this->_grammar);
        if (isset($ids)) {
            $header->setFieldBodyModel($ids);
        }
        $this->_setHeaderCharset($header);

        return $header;
    }

    
    public function createPathHeader($name, $path = null)
    {
        $header = new Swift_Mime_Headers_PathHeader($name, $this->_grammar);
        if (isset($path)) {
            $header->setFieldBodyModel($path);
        }
        $this->_setHeaderCharset($header);

        return $header;
    }

    
    public function charsetChanged($charset)
    {
        $this->_charset = $charset;
        $this->_encoder->charsetChanged($charset);
        $this->_paramEncoder->charsetChanged($charset);
    }

    
    public function __clone()
    {
        $this->_encoder = clone $this->_encoder;
        $this->_paramEncoder = clone $this->_paramEncoder;
    }

    
    private function _setHeaderCharset(Swift_Mime_Header $header)
    {
        if (isset($this->_charset)) {
            $header->setCharset($this->_charset);
        }
    }
}
