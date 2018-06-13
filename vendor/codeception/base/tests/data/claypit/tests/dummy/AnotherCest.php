<?php

class AnotherCest
{

    
    public function optimistic(DumbGuy $I) {
        $I->expect('everything is ok');
    }

    public function pessimistic(DumbGuy $I)
    {
        $I->expect('everything is bad');
    }

}