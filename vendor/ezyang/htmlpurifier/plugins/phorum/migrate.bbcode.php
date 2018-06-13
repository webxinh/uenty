<?php



if(!defined("PHORUM")) exit;

require_once(dirname(__FILE__) . "/../bbcode/bbcode.php");


function phorum_htmlpurifier_migrate($data)
{
    return phorum_mod_bbcode_format($data); // bbcode's 'format' hook
}

// vim: et sw=4 sts=4
