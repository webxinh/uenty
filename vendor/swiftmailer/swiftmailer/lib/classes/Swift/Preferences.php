<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Preferences
{
    
    private static $_instance = null;

    
    private function __construct()
    {
    }

    
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    
    public function setCharset($charset)
    {
        Swift_DependencyContainer::getInstance()
            ->register('properties.charset')->asValue($charset);

        return $this;
    }

    
    public function setTempDir($dir)
    {
        Swift_DependencyContainer::getInstance()
            ->register('tempdir')->asValue($dir);

        return $this;
    }

    
    public function setCacheType($type)
    {
        Swift_DependencyContainer::getInstance()
            ->register('cache')->asAliasOf(sprintf('cache.%s', $type));

        return $this;
    }

    
    public function setQPDotEscape($dotEscape)
    {
        $dotEscape = !empty($dotEscape);
        Swift_DependencyContainer::getInstance()
            ->register('mime.qpcontentencoder')
            ->asNewInstanceOf('Swift_Mime_ContentEncoder_QpContentEncoder')
            ->withDependencies(array('mime.charstream', 'mime.bytecanonicalizer'))
            ->addConstructorValue($dotEscape);

        return $this;
    }
}
