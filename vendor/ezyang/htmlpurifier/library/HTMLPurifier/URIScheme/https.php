<?php


class HTMLPurifier_URIScheme_https extends HTMLPurifier_URIScheme_http
{
    
    public $default_port = 443;
    
    public $secure = true;
}

// vim: et sw=4 sts=4