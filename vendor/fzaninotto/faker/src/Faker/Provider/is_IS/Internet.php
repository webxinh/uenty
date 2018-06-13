<?php

namespace Faker\Provider\is_IS;


class Internet extends \Faker\Provider\Internet
{
    
    protected static $freeEmailDomain = array(
        'gmail.com', 'yahoo.com', 'hotmail.com', 'visir.is', 'simnet.is', 'internet.is'
    );

    
    protected static $tld = array(
        'com', 'com', 'com', 'net', 'is', 'is', 'is',
    );
}
