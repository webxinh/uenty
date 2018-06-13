<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_Headers_DateHeader extends Swift_Mime_Headers_AbstractHeader
{
    
    private $_timestamp;

    
    public function __construct($name, Swift_Mime_Grammar $grammar)
    {
        $this->setFieldName($name);
        parent::__construct($grammar);
    }

    
    public function getFieldType()
    {
        return self::TYPE_DATE;
    }

    
    public function setFieldBodyModel($model)
    {
        $this->setTimestamp($model);
    }

    
    public function getFieldBodyModel()
    {
        return $this->getTimestamp();
    }

    
    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    
    public function setTimestamp($timestamp)
    {
        if (!is_null($timestamp)) {
            $timestamp = (int) $timestamp;
        }
        $this->clearCachedValueIf($this->_timestamp != $timestamp);
        $this->_timestamp = $timestamp;
    }

    
    public function getFieldBody()
    {
        if (!$this->getCachedValue()) {
            if (isset($this->_timestamp)) {
                $this->setCachedValue(date('r', $this->_timestamp));
            }
        }

        return $this->getCachedValue();
    }
}
