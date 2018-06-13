<?php


class DependentCest {

    
    public function secondOne(OrderGuy $I)
    {

    }

    public function firstOne(OrderGuy $I)
    {
        $I->failNow();
    }
}