<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_Attachment extends Swift_Mime_SimpleMimeEntity
{
    
    private $_mimeTypes = array();

    
    public function __construct(Swift_Mime_HeaderSet $headers, Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, Swift_Mime_Grammar $grammar, $mimeTypes = array())
    {
        parent::__construct($headers, $encoder, $cache, $grammar);
        $this->setDisposition('attachment');
        $this->setContentType('application/octet-stream');
        $this->_mimeTypes = $mimeTypes;
    }

    
    public function getNestingLevel()
    {
        return self::LEVEL_MIXED;
    }

    
    public function getDisposition()
    {
        return $this->_getHeaderFieldModel('Content-Disposition');
    }

    
    public function setDisposition($disposition)
    {
        if (!$this->_setHeaderFieldModel('Content-Disposition', $disposition)) {
            $this->getHeaders()->addParameterizedHeader('Content-Disposition', $disposition);
        }

        return $this;
    }

    
    public function getFilename()
    {
        return $this->_getHeaderParameter('Content-Disposition', 'filename');
    }

    
    public function setFilename($filename)
    {
        $this->_setHeaderParameter('Content-Disposition', 'filename', $filename);
        $this->_setHeaderParameter('Content-Type', 'name', $filename);

        return $this;
    }

    
    public function getSize()
    {
        return $this->_getHeaderParameter('Content-Disposition', 'size');
    }

    
    public function setSize($size)
    {
        $this->_setHeaderParameter('Content-Disposition', 'size', $size);

        return $this;
    }

    
    public function setFile(Swift_FileStream $file, $contentType = null)
    {
        $this->setFilename(basename($file->getPath()));
        $this->setBody($file, $contentType);
        if (!isset($contentType)) {
            $extension = strtolower(substr($file->getPath(), strrpos($file->getPath(), '.') + 1));

            if (array_key_exists($extension, $this->_mimeTypes)) {
                $this->setContentType($this->_mimeTypes[$extension]);
            }
        }

        return $this;
    }
}
