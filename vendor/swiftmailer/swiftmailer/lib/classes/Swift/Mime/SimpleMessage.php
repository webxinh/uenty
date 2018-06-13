<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_SimpleMessage extends Swift_Mime_MimePart implements Swift_Mime_Message
{
    const PRIORITY_HIGHEST = 1;
    const PRIORITY_HIGH = 2;
    const PRIORITY_NORMAL = 3;
    const PRIORITY_LOW = 4;
    const PRIORITY_LOWEST = 5;

    
    public function __construct(Swift_Mime_HeaderSet $headers, Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, Swift_Mime_Grammar $grammar, $charset = null)
    {
        parent::__construct($headers, $encoder, $cache, $grammar, $charset);
        $this->getHeaders()->defineOrdering(array(
            'Return-Path',
            'Received',
            'DKIM-Signature',
            'DomainKey-Signature',
            'Sender',
            'Message-ID',
            'Date',
            'Subject',
            'From',
            'Reply-To',
            'To',
            'Cc',
            'Bcc',
            'MIME-Version',
            'Content-Type',
            'Content-Transfer-Encoding',
            ));
        $this->getHeaders()->setAlwaysDisplayed(array('Date', 'Message-ID', 'From'));
        $this->getHeaders()->addTextHeader('MIME-Version', '1.0');
        $this->setDate(time());
        $this->setId($this->getId());
        $this->getHeaders()->addMailboxHeader('From');
    }

    
    public function getNestingLevel()
    {
        return self::LEVEL_TOP;
    }

    
    public function setSubject($subject)
    {
        if (!$this->_setHeaderFieldModel('Subject', $subject)) {
            $this->getHeaders()->addTextHeader('Subject', $subject);
        }

        return $this;
    }

    
    public function getSubject()
    {
        return $this->_getHeaderFieldModel('Subject');
    }

    
    public function setDate($date)
    {
        if (!$this->_setHeaderFieldModel('Date', $date)) {
            $this->getHeaders()->addDateHeader('Date', $date);
        }

        return $this;
    }

    
    public function getDate()
    {
        return $this->_getHeaderFieldModel('Date');
    }

    
    public function setReturnPath($address)
    {
        if (!$this->_setHeaderFieldModel('Return-Path', $address)) {
            $this->getHeaders()->addPathHeader('Return-Path', $address);
        }

        return $this;
    }

    
    public function getReturnPath()
    {
        return $this->_getHeaderFieldModel('Return-Path');
    }

    
    public function setSender($address, $name = null)
    {
        if (!is_array($address) && isset($name)) {
            $address = array($address => $name);
        }

        if (!$this->_setHeaderFieldModel('Sender', (array) $address)) {
            $this->getHeaders()->addMailboxHeader('Sender', (array) $address);
        }

        return $this;
    }

    
    public function getSender()
    {
        return $this->_getHeaderFieldModel('Sender');
    }

    
    public function addFrom($address, $name = null)
    {
        $current = $this->getFrom();
        $current[$address] = $name;

        return $this->setFrom($current);
    }

    
    public function setFrom($addresses, $name = null)
    {
        if (!is_array($addresses) && isset($name)) {
            $addresses = array($addresses => $name);
        }

        if (!$this->_setHeaderFieldModel('From', (array) $addresses)) {
            $this->getHeaders()->addMailboxHeader('From', (array) $addresses);
        }

        return $this;
    }

    
    public function getFrom()
    {
        return $this->_getHeaderFieldModel('From');
    }

    
    public function addReplyTo($address, $name = null)
    {
        $current = $this->getReplyTo();
        $current[$address] = $name;

        return $this->setReplyTo($current);
    }

    
    public function setReplyTo($addresses, $name = null)
    {
        if (!is_array($addresses) && isset($name)) {
            $addresses = array($addresses => $name);
        }

        if (!$this->_setHeaderFieldModel('Reply-To', (array) $addresses)) {
            $this->getHeaders()->addMailboxHeader('Reply-To', (array) $addresses);
        }

        return $this;
    }

    
    public function getReplyTo()
    {
        return $this->_getHeaderFieldModel('Reply-To');
    }

    
    public function addTo($address, $name = null)
    {
        $current = $this->getTo();
        $current[$address] = $name;

        return $this->setTo($current);
    }

    
    public function setTo($addresses, $name = null)
    {
        if (!is_array($addresses) && isset($name)) {
            $addresses = array($addresses => $name);
        }

        if (!$this->_setHeaderFieldModel('To', (array) $addresses)) {
            $this->getHeaders()->addMailboxHeader('To', (array) $addresses);
        }

        return $this;
    }

    
    public function getTo()
    {
        return $this->_getHeaderFieldModel('To');
    }

    
    public function addCc($address, $name = null)
    {
        $current = $this->getCc();
        $current[$address] = $name;

        return $this->setCc($current);
    }

    
    public function setCc($addresses, $name = null)
    {
        if (!is_array($addresses) && isset($name)) {
            $addresses = array($addresses => $name);
        }

        if (!$this->_setHeaderFieldModel('Cc', (array) $addresses)) {
            $this->getHeaders()->addMailboxHeader('Cc', (array) $addresses);
        }

        return $this;
    }

    
    public function getCc()
    {
        return $this->_getHeaderFieldModel('Cc');
    }

    
    public function addBcc($address, $name = null)
    {
        $current = $this->getBcc();
        $current[$address] = $name;

        return $this->setBcc($current);
    }

    
    public function setBcc($addresses, $name = null)
    {
        if (!is_array($addresses) && isset($name)) {
            $addresses = array($addresses => $name);
        }

        if (!$this->_setHeaderFieldModel('Bcc', (array) $addresses)) {
            $this->getHeaders()->addMailboxHeader('Bcc', (array) $addresses);
        }

        return $this;
    }

    
    public function getBcc()
    {
        return $this->_getHeaderFieldModel('Bcc');
    }

    
    public function setPriority($priority)
    {
        $priorityMap = array(
            self::PRIORITY_HIGHEST => 'Highest',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_LOWEST => 'Lowest',
            );
        $pMapKeys = array_keys($priorityMap);
        if ($priority > max($pMapKeys)) {
            $priority = max($pMapKeys);
        } elseif ($priority < min($pMapKeys)) {
            $priority = min($pMapKeys);
        }
        if (!$this->_setHeaderFieldModel('X-Priority',
            sprintf('%d (%s)', $priority, $priorityMap[$priority]))) {
            $this->getHeaders()->addTextHeader('X-Priority',
                sprintf('%d (%s)', $priority, $priorityMap[$priority]));
        }

        return $this;
    }

    
    public function getPriority()
    {
        list($priority) = sscanf($this->_getHeaderFieldModel('X-Priority'),
            '%[1-5]'
            );

        return isset($priority) ? $priority : 3;
    }

    
    public function setReadReceiptTo($addresses)
    {
        if (!$this->_setHeaderFieldModel('Disposition-Notification-To', $addresses)) {
            $this->getHeaders()
                ->addMailboxHeader('Disposition-Notification-To', $addresses);
        }

        return $this;
    }

    
    public function getReadReceiptTo()
    {
        return $this->_getHeaderFieldModel('Disposition-Notification-To');
    }

    
    public function attach(Swift_Mime_MimeEntity $entity)
    {
        $this->setChildren(array_merge($this->getChildren(), array($entity)));

        return $this;
    }

    
    public function detach(Swift_Mime_MimeEntity $entity)
    {
        $newChildren = array();
        foreach ($this->getChildren() as $child) {
            if ($entity !== $child) {
                $newChildren[] = $child;
            }
        }
        $this->setChildren($newChildren);

        return $this;
    }

    
    public function embed(Swift_Mime_MimeEntity $entity)
    {
        $this->attach($entity);

        return 'cid:'.$entity->getId();
    }

    
    public function toString()
    {
        if (count($children = $this->getChildren()) > 0 && $this->getBody() != '') {
            $this->setChildren(array_merge(array($this->_becomeMimePart()), $children));
            $string = parent::toString();
            $this->setChildren($children);
        } else {
            $string = parent::toString();
        }

        return $string;
    }

    
    public function __toString()
    {
        return $this->toString();
    }

    
    public function toByteStream(Swift_InputByteStream $is)
    {
        if (count($children = $this->getChildren()) > 0 && $this->getBody() != '') {
            $this->setChildren(array_merge(array($this->_becomeMimePart()), $children));
            parent::toByteStream($is);
            $this->setChildren($children);
        } else {
            parent::toByteStream($is);
        }
    }

    
    protected function _getIdField()
    {
        return 'Message-ID';
    }

    
    protected function _becomeMimePart()
    {
        $part = new parent($this->getHeaders()->newInstance(), $this->getEncoder(),
            $this->_getCache(), $this->_getGrammar(), $this->_userCharset
            );
        $part->setContentType($this->_userContentType);
        $part->setBody($this->getBody());
        $part->setFormat($this->_userFormat);
        $part->setDelSp($this->_userDelSp);
        $part->_setNestingLevel($this->_getTopNestingLevel());

        return $part;
    }

    
    private function _getTopNestingLevel()
    {
        $highestLevel = $this->getNestingLevel();
        foreach ($this->getChildren() as $child) {
            $childLevel = $child->getNestingLevel();
            if ($highestLevel < $childLevel) {
                $highestLevel = $childLevel;
            }
        }

        return $highestLevel;
    }
}
