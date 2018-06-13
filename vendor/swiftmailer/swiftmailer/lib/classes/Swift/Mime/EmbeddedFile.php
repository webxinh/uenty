<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mime_EmbeddedFile extends Swift_Mime_Attachment
{
    
    public function __construct(Swift_Mime_HeaderSet $headers, Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, Swift_Mime_Grammar $grammar, $mimeTypes = array())
    {
        parent::__construct($headers, $encoder, $cache, $grammar, $mimeTypes);
        $this->setDisposition('inline');
        $this->setId($this->getId());
    }

    
    public function getNestingLevel()
    {
        return self::LEVEL_RELATED;
    }
}
