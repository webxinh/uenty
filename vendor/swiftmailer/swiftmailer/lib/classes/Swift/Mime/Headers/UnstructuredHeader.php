<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_Headers_UnstructuredHeader extends Swift_Mime_Headers_AbstractHeader
{
    
    private $_value;

    
    public function __construct($name, Swift_Mime_HeaderEncoder $encoder, Swift_Mime_Grammar $grammar)
    {
        $this->setFieldName($name);
        $this->setEncoder($encoder);
        parent::__construct($grammar);
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
        $this->clearCachedValueIf($this->_value != $value);
        $this->_value = $value;
    }

    
    public function getFieldBody()
    {
        if (!$this->getCachedValue()) {
            $this->setCachedValue(
                $this->encodeWords($this, $this->_value)
                );
        }

        return $this->getCachedValue();
    }
}
