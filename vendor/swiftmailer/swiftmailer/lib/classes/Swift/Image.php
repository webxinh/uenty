<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Image extends Swift_EmbeddedFile
{
    
    public function __construct($data = null, $filename = null, $contentType = null)
    {
        parent::__construct($data, $filename, $contentType);
    }

    
    public static function newInstance($data = null, $filename = null, $contentType = null)
    {
        return new self($data, $filename, $contentType);
    }

    
    public static function fromPath($path)
    {
        $image = self::newInstance()->setFile(
            new Swift_ByteStream_FileByteStream($path)
            );

        return $image;
    }
}
