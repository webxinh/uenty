<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_EmbeddedFile extends Swift_Mime_EmbeddedFile
{
    
    public function __construct($data = null, $filename = null, $contentType = null)
    {
        call_user_func_array(
            array($this, 'Swift_Mime_EmbeddedFile::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('mime.embeddedfile')
            );

        $this->setBody($data);
        $this->setFilename($filename);
        if ($contentType) {
            $this->setContentType($contentType);
        }
    }

    
    public static function newInstance($data = null, $filename = null, $contentType = null)
    {
        return new self($data, $filename, $contentType);
    }

    
    public static function fromPath($path)
    {
        return self::newInstance()->setFile(
            new Swift_ByteStream_FileByteStream($path)
            );
    }
}
