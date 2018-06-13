<?php

namespace Faker\Provider\da_DK;


class Internet extends \Faker\Provider\Internet
{
    
    protected static $safeEmailTld = array(
        'org', 'com', 'net', 'dk', 'dk', 'dk',
    );

    
    protected static $freeEmailDomain = array(
        'gmail.com', 'yahoo.com', 'yahoo.dk', 'hotmail.com', 'hotmail.dk', 'mail.dk', 'live.dk'
    );

    
    protected static $tld = array(
        'com', 'com', 'com', 'biz', 'info', 'net', 'org', 'dk', 'dk', 'dk',
    );
}
