<?php
class MultipleDataProviderTest extends PHPUnit_Framework_TestCase
{
    
    public function testOne()
    {
    }

    
    public function testTwo()
    {
    }

    public static function providerA()
    {
        return [
            ['ok', null, null],
            ['ok', null, null],
            ['ok', null, null]
        ];
    }

    public static function providerB()
    {
        return [
            [null, 'ok', null],
            [null, 'ok', null],
            [null, 'ok', null]
        ];
    }

    public static function providerC()
    {
        return [
            [null, null, 'ok'],
            [null, null, 'ok'],
            [null, null, 'ok']
        ];
    }

    public static function providerD()
    {
        yield ['ok', null, null];
        yield ['ok', null, null];
        yield ['ok', null, null];
    }

    public static function providerE()
    {
        yield [null, 'ok', null];
        yield [null, 'ok', null];
        yield [null, 'ok', null];
    }

    public static function providerF()
    {
        $object = new ArrayObject(
            [
                [null, null, 'ok'],
                [null, null, 'ok'],
                [null, null, 'ok']
            ]
        );

        return $object->getIterator();
    }
}
