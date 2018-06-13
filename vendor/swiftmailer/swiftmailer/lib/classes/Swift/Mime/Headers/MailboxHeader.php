<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_Headers_MailboxHeader extends Swift_Mime_Headers_AbstractHeader
{
    
    private $_mailboxes = array();

    
    public function __construct($name, Swift_Mime_HeaderEncoder $encoder, Swift_Mime_Grammar $grammar)
    {
        $this->setFieldName($name);
        $this->setEncoder($encoder);
        parent::__construct($grammar);
    }

    
    public function getFieldType()
    {
        return self::TYPE_MAILBOX;
    }

    
    public function setFieldBodyModel($model)
    {
        $this->setNameAddresses($model);
    }

    
    public function getFieldBodyModel()
    {
        return $this->getNameAddresses();
    }

    
    public function setNameAddresses($mailboxes)
    {
        $this->_mailboxes = $this->normalizeMailboxes((array) $mailboxes);
        $this->setCachedValue(null); //Clear any cached value
    }

    
    public function getNameAddressStrings()
    {
        return $this->_createNameAddressStrings($this->getNameAddresses());
    }

    
    public function getNameAddresses()
    {
        return $this->_mailboxes;
    }

    
    public function setAddresses($addresses)
    {
        $this->setNameAddresses(array_values((array) $addresses));
    }

    
    public function getAddresses()
    {
        return array_keys($this->_mailboxes);
    }

    
    public function removeAddresses($addresses)
    {
        $this->setCachedValue(null);
        foreach ((array) $addresses as $address) {
            unset($this->_mailboxes[$address]);
        }
    }

    
    public function getFieldBody()
    {
        // Compute the string value of the header only if needed
        if (is_null($this->getCachedValue())) {
            $this->setCachedValue($this->createMailboxListString($this->_mailboxes));
        }

        return $this->getCachedValue();
    }

    // -- Points of extension

    
    protected function normalizeMailboxes(array $mailboxes)
    {
        $actualMailboxes = array();

        foreach ($mailboxes as $key => $value) {
            if (is_string($key)) {
                //key is email addr
                $address = $key;
                $name = $value;
            } else {
                $address = $value;
                $name = null;
            }
            $this->_assertValidAddress($address);
            $actualMailboxes[$address] = $name;
        }

        return $actualMailboxes;
    }

    
    protected function createDisplayNameString($displayName, $shorten = false)
    {
        return $this->createPhrase($this, $displayName, $this->getCharset(), $this->getEncoder(), $shorten);
    }

    
    protected function createMailboxListString(array $mailboxes)
    {
        return implode(', ', $this->_createNameAddressStrings($mailboxes));
    }

    
    protected function tokenNeedsEncoding($token)
    {
        return preg_match('/[()<>\[\]:;@\,."]/', $token) || parent::tokenNeedsEncoding($token);
    }

    
    private function _createNameAddressStrings(array $mailboxes)
    {
        $strings = array();

        foreach ($mailboxes as $email => $name) {
            $mailboxStr = $email;
            if (!is_null($name)) {
                $nameStr = $this->createDisplayNameString($name, empty($strings));
                $mailboxStr = $nameStr.' <'.$mailboxStr.'>';
            }
            $strings[] = $mailboxStr;
        }

        return $strings;
    }

    
    private function _assertValidAddress($address)
    {
        if (!preg_match('/^'.$this->getGrammar()->getDefinition('addr-spec').'$/D',
            $address)) {
            throw new Swift_RfcComplianceException(
                'Address in mailbox given ['.$address.
                '] does not comply with RFC 2822, 3.6.2.'
                );
        }
    }
}
