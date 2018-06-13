<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_MimePart extends Swift_Mime_SimpleMimeEntity
{
    
    protected $_userFormat;

    
    protected $_userCharset;

    
    protected $_userDelSp;

    
    private $_nestingLevel = self::LEVEL_ALTERNATIVE;

    
    public function __construct(Swift_Mime_HeaderSet $headers, Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, Swift_Mime_Grammar $grammar, $charset = null)
    {
        parent::__construct($headers, $encoder, $cache, $grammar);
        $this->setContentType('text/plain');
        if (!is_null($charset)) {
            $this->setCharset($charset);
        }
    }

    
    public function setBody($body, $contentType = null, $charset = null)
    {
        if (isset($charset)) {
            $this->setCharset($charset);
        }
        $body = $this->_convertString($body);

        parent::setBody($body, $contentType);

        return $this;
    }

    
    public function getCharset()
    {
        return $this->_getHeaderParameter('Content-Type', 'charset');
    }

    
    public function setCharset($charset)
    {
        $this->_setHeaderParameter('Content-Type', 'charset', $charset);
        if ($charset !== $this->_userCharset) {
            $this->_clearCache();
        }
        $this->_userCharset = $charset;
        parent::charsetChanged($charset);

        return $this;
    }

    
    public function getFormat()
    {
        return $this->_getHeaderParameter('Content-Type', 'format');
    }

    
    public function setFormat($format)
    {
        $this->_setHeaderParameter('Content-Type', 'format', $format);
        $this->_userFormat = $format;

        return $this;
    }

    
    public function getDelSp()
    {
        return 'yes' == $this->_getHeaderParameter('Content-Type', 'delsp') ? true : false;
    }

    
    public function setDelSp($delsp = true)
    {
        $this->_setHeaderParameter('Content-Type', 'delsp', $delsp ? 'yes' : null);
        $this->_userDelSp = $delsp;

        return $this;
    }

    
    public function getNestingLevel()
    {
        return $this->_nestingLevel;
    }

    
    public function charsetChanged($charset)
    {
        $this->setCharset($charset);
    }

    
    protected function _fixHeaders()
    {
        parent::_fixHeaders();
        if (count($this->getChildren())) {
            $this->_setHeaderParameter('Content-Type', 'charset', null);
            $this->_setHeaderParameter('Content-Type', 'format', null);
            $this->_setHeaderParameter('Content-Type', 'delsp', null);
        } else {
            $this->setCharset($this->_userCharset);
            $this->setFormat($this->_userFormat);
            $this->setDelSp($this->_userDelSp);
        }
    }

    
    protected function _setNestingLevel($level)
    {
        $this->_nestingLevel = $level;
    }

    
    protected function _convertString($string)
    {
        $charset = strtolower($this->getCharset());
        if (!in_array($charset, array('utf-8', 'iso-8859-1', 'iso-8859-15', ''))) {
            // mb_convert_encoding must be the first one to check, since iconv cannot convert some words.
            if (function_exists('mb_convert_encoding')) {
                $string = mb_convert_encoding($string, $charset, 'utf-8');
            } elseif (function_exists('iconv')) {
                $string = iconv('utf-8//TRANSLIT//IGNORE', $charset, $string);
            } else {
                throw new Swift_SwiftException('No suitable convert encoding function (use UTF-8 as your charset or install the mbstring or iconv extension).');
            }

            return $string;
        }

        return $string;
    }
}
