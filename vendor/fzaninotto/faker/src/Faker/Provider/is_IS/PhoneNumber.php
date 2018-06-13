<?php

namespace Faker\Provider\is_IS;


class PhoneNumber extends \Faker\Provider\PhoneNumber
{
    
    protected static $formats = array(
        '+354 ### ####',
        '+354 #######',
        '+354#######',
        '### ####',
        '#######',
    );
}
