<?php

namespace Faker\Provider\da_DK;


class PhoneNumber extends \Faker\Provider\PhoneNumber
{
    
    protected static $formats = array(
        '+45 ## ## ## ##',
        '+45 #### ####',
        '+45########',
        '## ## ## ##',
        '#### ####',
        '########',
    );
}
