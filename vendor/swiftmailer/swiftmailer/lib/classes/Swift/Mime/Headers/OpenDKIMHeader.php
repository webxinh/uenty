<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_Headers_OpenDKIMHeader implements Swift_Mime_Header
{
    
    private $_value;

    
    private $_fieldName;

    
    public function __construct($name)
    {
        $this->_fieldName = $name;
    }

    
    public function getFieldType()
    {
        return self::TYPE_TEXT;
    }

    
    public function setFieldBodyModel($model)
    {
        $this->setValue($model);
    }

    
    public function getFieldBodyModel()
    {
        return $this->getValue();
    }

    
    public function getValue()
    {
        return $this->_value;
    }

    
    public function setValue($value)
    {
        $this->_value = $value;
    }

    
    public function getFieldBody()
    {
        return $this->_value;
    }

    
    public function toString()
    {
        return $this->_fieldName.': '.$this->_value;
    }

    
    public function getFieldName()
    {
        return $this->_fieldName;
    }

    
    public function setCharset($charset)
    {
    }
}
