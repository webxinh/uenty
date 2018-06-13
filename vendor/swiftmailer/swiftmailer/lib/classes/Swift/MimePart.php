<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_MimePart extends Swift_Mime_MimePart
{
    
    public function __construct($body = null, $contentType = null, $charset = null)
    {
        call_user_func_array(
            array($this, 'Swift_Mime_MimePart::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('mime.part')
            );

        if (!isset($charset)) {
            $charset = Swift_DependencyContainer::getInstance()
                ->lookup('properties.charset');
        }
        $this->setBody($body);
        $this->setCharset($charset);
        if ($contentType) {
            $this->setContentType($contentType);
        }
    }

    
    public static function newInstance($body = null, $contentType = null, $charset = null)
    {
        return new self($body, $contentType, $charset);
    }
}
