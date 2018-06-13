<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_SimpleHeaderSet implements Swift_Mime_HeaderSet
{
    
    private $_factory;

    
    private $_headers = array();

    
    private $_order = array();

    
    private $_required = array();

    
    private $_charset;

    
    public function __construct(Swift_Mime_HeaderFactory $factory, $charset = null)
    {
        $this->_factory = $factory;
        if (isset($charset)) {
            $this->setCharset($charset);
        }
    }

    
    public function setCharset($charset)
    {
        $this->_charset = $charset;
        $this->_factory->charsetChanged($charset);
        $this->_notifyHeadersOfCharset($charset);
    }

    
    public function addMailboxHeader($name, $addresses = null)
    {
        $this->_storeHeader($name,
        $this->_factory->createMailboxHeader($name, $addresses));
    }

    
    public function addDateHeader($name, $timestamp = null)
    {
        $this->_storeHeader($name,
        $this->_factory->createDateHeader($name, $timestamp));
    }

    
    public function addTextHeader($name, $value = null)
    {
        $this->_storeHeader($name,
        $this->_factory->createTextHeader($name, $value));
    }

    
    public function addParameterizedHeader($name, $value = null, $params = array())
    {
        $this->_storeHeader($name, $this->_factory->createParameterizedHeader($name, $value, $params));
    }

    
    public function addIdHeader($name, $ids = null)
    {
        $this->_storeHeader($name, $this->_factory->createIdHeader($name, $ids));
    }

    
    public function addPathHeader($name, $path = null)
    {
        $this->_storeHeader($name, $this->_factory->createPathHeader($name, $path));
    }

    
    public function has($name, $index = 0)
    {
        $lowerName = strtolower($name);

        if (!array_key_exists($lowerName, $this->_headers)) {
            return false;
        }

        if (func_num_args() < 2) {
            // index was not specified, so we only need to check that there is at least one header value set
            return (bool) count($this->_headers[$lowerName]);
        }

        return array_key_exists($index, $this->_headers[$lowerName]);
    }

    
    public function set(Swift_Mime_Header $header, $index = 0)
    {
        $this->_storeHeader($header->getFieldName(), $header, $index);
    }

    
    public function get($name, $index = 0)
    {
        $name = strtolower($name);

        if (func_num_args() < 2) {
            if ($this->has($name)) {
                $values = array_values($this->_headers[$name]);

                return array_shift($values);
            }
        } else {
            if ($this->has($name, $index)) {
                return $this->_headers[$name][$index];
            }
        }
    }

    
    public function getAll($name = null)
    {
        if (!isset($name)) {
            $headers = array();
            foreach ($this->_headers as $collection) {
                $headers = array_merge($headers, $collection);
            }

            return $headers;
        }

        $lowerName = strtolower($name);
        if (!array_key_exists($lowerName, $this->_headers)) {
            return array();
        }

        return $this->_headers[$lowerName];
    }

    
    public function listAll()
    {
        $headers = $this->_headers;
        if ($this->_canSort()) {
            uksort($headers, array($this, '_sortHeaders'));
        }

        return array_keys($headers);
    }

    
    public function remove($name, $index = 0)
    {
        $lowerName = strtolower($name);
        unset($this->_headers[$lowerName][$index]);
    }

    
    public function removeAll($name)
    {
        $lowerName = strtolower($name);
        unset($this->_headers[$lowerName]);
    }

    
    public function newInstance()
    {
        return new self($this->_factory);
    }

    
    public function defineOrdering(array $sequence)
    {
        $this->_order = array_flip(array_map('strtolower', $sequence));
    }

    
    public function setAlwaysDisplayed(array $names)
    {
        $this->_required = array_flip(array_map('strtolower', $names));
    }

    
    public function charsetChanged($charset)
    {
        $this->setCharset($charset);
    }

    
    public function toString()
    {
        $string = '';
        $headers = $this->_headers;
        if ($this->_canSort()) {
            uksort($headers, array($this, '_sortHeaders'));
        }
        foreach ($headers as $collection) {
            foreach ($collection as $header) {
                if ($this->_isDisplayed($header) || $header->getFieldBody() != '') {
                    $string .= $header->toString();
                }
            }
        }

        return $string;
    }

    
    public function __toString()
    {
        return $this->toString();
    }

    
    private function _storeHeader($name, Swift_Mime_Header $header, $offset = null)
    {
        if (!isset($this->_headers[strtolower($name)])) {
            $this->_headers[strtolower($name)] = array();
        }
        if (!isset($offset)) {
            $this->_headers[strtolower($name)][] = $header;
        } else {
            $this->_headers[strtolower($name)][$offset] = $header;
        }
    }

    
    private function _canSort()
    {
        return count($this->_order) > 0;
    }

    
    private function _sortHeaders($a, $b)
    {
        $lowerA = strtolower($a);
        $lowerB = strtolower($b);
        $aPos = array_key_exists($lowerA, $this->_order) ? $this->_order[$lowerA] : -1;
        $bPos = array_key_exists($lowerB, $this->_order) ? $this->_order[$lowerB] : -1;

        if (-1 === $aPos && -1 === $bPos) {
            // just be sure to be determinist here
            return $a > $b ? -1 : 1;
        }

        if ($aPos == -1) {
            return 1;
        } elseif ($bPos == -1) {
            return -1;
        }

        return $aPos < $bPos ? -1 : 1;
    }

    
    private function _isDisplayed(Swift_Mime_Header $header)
    {
        return array_key_exists(strtolower($header->getFieldName()), $this->_required);
    }

    
    private function _notifyHeadersOfCharset($charset)
    {
        foreach ($this->_headers as $headerGroup) {
            foreach ($headerGroup as $header) {
                $header->setCharset($charset);
            }
        }
    }

    
    public function __clone()
    {
        $this->_factory = clone $this->_factory;
        foreach ($this->_headers as $groupKey => $headerGroup) {
            foreach ($headerGroup as $key => $header) {
                $this->_headers[$groupKey][$key] = clone $header;
            }
        }
    }
}
