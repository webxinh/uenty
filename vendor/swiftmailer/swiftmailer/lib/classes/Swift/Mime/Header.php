<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Mime_Header
{
    
    const TYPE_TEXT = 2;

    
    const TYPE_PARAMETERIZED = 6;

    
    const TYPE_MAILBOX = 8;

    
    const TYPE_DATE = 16;

    
    const TYPE_ID = 32;

    
    const TYPE_PATH = 64;

    
    public function getFieldType();

    
    public function setFieldBodyModel($model);

    
    public function setCharset($charset);

    
    public function getFieldBodyModel();

    
    public function getFieldName();

    
    public function getFieldBody();

    
    public function toString();
}
