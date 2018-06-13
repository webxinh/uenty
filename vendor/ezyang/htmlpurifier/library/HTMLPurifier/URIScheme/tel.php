<?php



class HTMLPurifier_URIScheme_tel extends HTMLPurifier_URIScheme
{
    
    public $browsable = false;

    
    public $may_omit_host = true;

    
    public function doValidate(&$uri, $config, $context)
    {
        $uri->userinfo = null;
        $uri->host     = null;
        $uri->port     = null;

        // Delete all non-numeric characters, non-x characters
        // from phone number, EXCEPT for a leading plus sign.
        $uri->path = preg_replace('/(?!^\+)[^\dx]/', '',
                     // Normalize e(x)tension to lower-case
                     str_replace('X', 'x', $uri->path));

        return true;
    }
}

// vim: et sw=4 sts=4
