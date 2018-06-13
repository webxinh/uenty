<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_Headers_IdentificationHeader extends Swift_Mime_Headers_AbstractHeader
{
    
    private $_ids = array();

    
    public function __construct($name, Swift_Mime_Grammar $grammar)
    {
        $this->setFieldName($name);
        parent::__construct($grammar);
    }

    
    public function getFieldType()
    {
        return self::TYPE_ID;
    }

    
    public function setFieldBodyModel($model)
    {
        $this->setId($model);
    }

    
    public function getFieldBodyModel()
    {
        return $this->getIds();
    }

    
    public function setId($id)
    {
        $this->setIds(is_array($id) ? $id : array($id));
    }

    
    public function getId()
    {
        if (count($this->_ids) > 0) {
            return $this->_ids[0];
        }
    }

    
    public function setIds(array $ids)
    {
        $actualIds = array();

        foreach ($ids as $id) {
            $this->_assertValidId($id);
            $actualIds[] = $id;
        }

        $this->clearCachedValueIf($this->_ids != $actualIds);
        $this->_ids = $actualIds;
    }

    
    public function getIds()
    {
        return $this->_ids;
    }

    
    public function getFieldBody()
    {
        if (!$this->getCachedValue()) {
            $angleAddrs = array();

            foreach ($this->_ids as $id) {
                $angleAddrs[] = '<'.$id.'>';
            }

            $this->setCachedValue(implode(' ', $angleAddrs));
        }

        return $this->getCachedValue();
    }

    
    private function _assertValidId($id)
    {
        if (!preg_match(
            '/^'.$this->getGrammar()->getDefinition('id-left').'@'.
            $this->getGrammar()->getDefinition('id-right').'$/D',
            $id
            )) {
            throw new Swift_RfcComplianceException(
                'Invalid ID given <'.$id.'>'
                );
        }
    }
}
