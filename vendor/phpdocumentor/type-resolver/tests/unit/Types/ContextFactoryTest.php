<?php


namespace phpDocumentor\Reflection\Types {

// Added imports on purpose as mock for the unit tests, please do not remove.
    use Mockery as m;
    use phpDocumentor\Reflection\DocBlock,
        phpDocumentor\Reflection\DocBlock\Tag;
    use phpDocumentor;
    use \ReflectionClass; // yes, the slash is part of the test

    
    class ContextFactoryTest extends \PHPUnit_Framework_TestCase
    {
        
        public function testReadsNamespaceFromClassReflection()
        {
            $fixture = new ContextFactory();
            $context = $fixture->createFromReflector(new ReflectionClass($this));

            $this->assertSame(__NAMESPACE__, $context->getNamespace());
        }

        
        public function testReadsAliasesFromClassReflection()
        {
            $fixture = new ContextFactory();
            $expected = [
                'm' => 'Mockery',
                'DocBlock' => 'phpDocumentor\Reflection\DocBlock',
                'Tag' => 'phpDocumentor\Reflection\DocBlock\Tag',
                'phpDocumentor' => 'phpDocumentor',
                'ReflectionClass' => 'ReflectionClass'
            ];
            $context = $fixture->createFromReflector(new ReflectionClass($this));

            $this->assertSame($expected, $context->getNamespaceAliases());
        }

        
        public function testReadsNamespaceFromProvidedNamespaceAndContent()
        {
            $fixture = new ContextFactory();
            $context = $fixture->createForNamespace(__NAMESPACE__, file_get_contents(__FILE__));

            $this->assertSame(__NAMESPACE__, $context->getNamespace());
        }

        
        public function testReadsAliasesFromProvidedNamespaceAndContent()
        {
            $fixture = new ContextFactory();
            $expected = [
                'm'               => 'Mockery',
                'DocBlock'        => 'phpDocumentor\Reflection\DocBlock',
                'Tag'             => 'phpDocumentor\Reflection\DocBlock\Tag',
                'phpDocumentor' => 'phpDocumentor',
                'ReflectionClass' => 'ReflectionClass'
            ];
            $context = $fixture->createForNamespace(__NAMESPACE__, file_get_contents(__FILE__));

            $this->assertSame($expected, $context->getNamespaceAliases());
        }

        
        public function testTraitUseIsNotDetectedAsNamespaceUse()
        {
            $php = "<?php
                namespace Foo;

                trait FooTrait {}

                class FooClass {
                    use FooTrait;
                }
            ";

            $fixture = new ContextFactory();
            $context = $fixture->createForNamespace('Foo', $php);

            $this->assertSame([], $context->getNamespaceAliases());
        }

        
        public function testAllOpeningBracesAreCheckedWhenSearchingForEndOfClass()
        {
            $php = '<?php
                namespace Foo;

                trait FooTrait {}
                trait BarTrait {}

                class FooClass {
                    use FooTrait;

                    public function bar()
                    {
                        echo "{$baz}";
                        echo "${baz}";
                    }
                }

                class BarClass {
                    use BarTrait;

                    public function bar()
                    {
                        echo "{$baz}";
                        echo "${baz}";
                    }
                }
            ';

            $fixture = new ContextFactory();
            $context = $fixture->createForNamespace('Foo', $php);

            $this->assertSame([], $context->getNamespaceAliases());
        }

        
        public function testEmptyFileName()
        {
            $fixture = new ContextFactory();
            $context = $fixture->createFromReflector(new \ReflectionClass('stdClass'));

            $this->assertSame([], $context->getNamespaceAliases());
        }

        
        public function testEvalDClass()
        {
            eval(<<<PHP
namespace Foo;

class Bar
{
}
PHP
);
            $fixture = new ContextFactory();
            $context = $fixture->createFromReflector(new \ReflectionClass('Foo\Bar'));

            $this->assertSame([], $context->getNamespaceAliases());
        }
    }
}

namespace phpDocumentor\Reflection\Types\Mock {
    // the following import should not show in the tests above
    use phpDocumentor\Reflection\DocBlock\Description;
}
