<?php


namespace phpDocumentor\Reflection;


class FqsenTest extends \PHPUnit_Framework_TestCase
{
    
    public function testValidFormats($fqsen, $name)
    {
        $instance  = new Fqsen($fqsen);
        $this->assertEquals($name, $instance->getName());
    }

    
    public function validFqsenProvider()
    {
        return [
            ['\\', ''],
            ['\My\Space', 'Space'],
            ['\My\Space\myFunction()', 'myFunction'],
            ['\My\Space\MY_CONSTANT', 'MY_CONSTANT'],
            ['\My\Space\MY_CONSTANT2', 'MY_CONSTANT2'],
            ['\My\Space\MyClass', 'MyClass'],
            ['\My\Space\MyInterface', 'MyInterface'],
            ['\My\Space\MyTrait', 'MyTrait'],
            ['\My\Space\MyClass::myMethod()', 'myMethod'],
            ['\My\Space\MyClass::$my_property', 'my_property'],
            ['\My\Space\MyClass::MY_CONSTANT', 'MY_CONSTANT'],
        ];
    }

    
    public function testInValidFormats($fqsen)
    {
        new Fqsen($fqsen);
    }

    
    public function invalidFqsenProvider()
    {
        return [
            ['\My\*'],
            ['\My\Space\.()'],
            ['My\Space'],
        ];
    }

    
    public function testToString()
    {
        $className = new Fqsen('\\phpDocumentor\\Application');

        $this->assertEquals('\\phpDocumentor\\Application', (string)$className);
    }
}
