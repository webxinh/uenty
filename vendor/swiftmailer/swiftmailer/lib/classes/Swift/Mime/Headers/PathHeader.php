<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_Headers_PathHeader extends Swift_Mime_Headers_AbstractHeader
{
    
    private $_address;

    
    public function __construct($name, Swift_Mime_Grammar $grammar)
    {
        $this->setFieldName($name);
        parent::__construct($grammar);
    }

    
    public function getFieldType()
    {
        return self::TYPE_PATH;
    }

    
    public function setFieldBodyModel($model)
    {
        $this->setAddress($model);
    }

    
    public function getFieldBodyModel()
    {
        return $this->getAddress();
    }

    
    public function setAddress($address)
    {
        if (is_null($address)) {
            $this->_address = null;
        } elseif ('' == $address) {
            $this->_address = '';
        } else {
            $this->_assertValidAddress($address);
            $this->_address = $address;
        }
        $this->setCachedValue(null);
    }

    
    public function getAddress()
    {
        return $this->_address;
    }

    
    public function getFieldBody()
    {
        if (!$this->getCachedValue()) {
            if (isset($this->_address)) {
                $this->setCachedValue('<'.$this->_address.'>');
            }
        }

        return $this->getCachedValue();
    }

    
    private function _assertValidAddress($address)
    {
        if (!preg_match('/^'.$this->getGrammar()->getDefinition('addr-spec').'$/D',
            $address)) {
            throw new Swift_RfcComplianceException(
                'Address set in PathHeader does not comply with addr-spec of RFC 2822.'
                );
        }
    }
}
