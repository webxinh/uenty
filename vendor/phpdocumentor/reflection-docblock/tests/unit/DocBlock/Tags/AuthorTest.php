<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;


class AuthorTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Author('Mike van Riel', 'mike@phpdoc.org');

        $this->assertSame('author', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Author('Mike van Riel', 'mike@phpdoc.org');

        $this->assertSame('@author Mike van Riel<mike@phpdoc.org>', $fixture->render());
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Author('Mike van Riel', 'mike@phpdoc.org');

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasTheAuthorName()
    {
        $expected = 'Mike van Riel';

        $fixture = new Author($expected, 'mike@phpdoc.org');

        $this->assertSame($expected, $fixture->getAuthorName());
    }

    
    public function testInitializationFailsIfAuthorNameIsNotAString()
    {
        new Author([], 'mike@phpdoc.org');
    }

    
    public function testHasTheAuthorMailAddress()
    {
        $expected = 'mike@phpdoc.org';

        $fixture = new Author('Mike van Riel', $expected);

        $this->assertSame($expected, $fixture->getEmail());
    }

    
    public function testInitializationFailsIfEmailIsNotAString()
    {
        new Author('Mike van Riel', []);
    }

    
    public function testInitializationFailsIfEmailIsNotValid()
    {
        new Author('Mike van Riel', 'mike');
    }

    
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Author('Mike van Riel', 'mike@phpdoc.org');

        $this->assertSame('Mike van Riel<mike@phpdoc.org>', (string)$fixture);
    }

    
    public function testFactoryMethod()
    {
        $fixture = Author::create('Mike van Riel <mike@phpdoc.org>');

        $this->assertSame('Mike van Riel<mike@phpdoc.org>', (string)$fixture);
        $this->assertSame('Mike van Riel', $fixture->getAuthorName());
        $this->assertSame('mike@phpdoc.org', $fixture->getEmail());
    }

    
    public function testFactoryMethodReturnsNullIfItCouldNotReadBody()
    {
        $this->assertNull(Author::create('dfgr<'));
    }
}
