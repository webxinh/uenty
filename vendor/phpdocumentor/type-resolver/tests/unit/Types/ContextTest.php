<?php


namespace phpDocumentor\Reflection\Types;

use Mockery as m;


class ContextTest extends \PHPUnit_Framework_TestCase
{
    
    public function testProvidesANormalizedNamespace()
    {
        $fixture = new Context('\My\Space');
        $this->assertSame('My\Space', $fixture->getNamespace());
    }

    
    public function testInterpretsNamespaceNamedGlobalAsRootNamespace()
    {
        $fixture = new Context('global');
        $this->assertSame('', $fixture->getNamespace());
    }

    
    public function testInterpretsNamespaceNamedDefaultAsRootNamespace()
    {
        $fixture = new Context('default');
        $this->assertSame('', $fixture->getNamespace());
    }

    
    public function testProvidesNormalizedNamespaceAliases()
    {
        $fixture = new Context('', ['Space' => '\My\Space']);
        $this->assertSame(['Space' => 'My\Space'], $fixture->getNamespaceAliases());
    }
}
