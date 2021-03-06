<?php
namespace Codeception\Lib\Console;

use Codeception\Test\Unit;


class ColorizerTest extends Unit
{
    
    protected $colorizer;

    
    protected function setUp()
    {
        parent::setUp();
        $this->colorizer = new Colorizer();
    }

    public function testItAddFormatToDiffMessage()
    {
        $toColorizeInput = <<<PLAIN
foo
bar
+ actual line
- expected line
bar
PLAIN;

        $expectedColorized = <<<COLORED
foo
bar
<info>+ actual line</info>
<comment>- expected line</comment>
bar
COLORED;

        $actual = $this->colorizer->colorize($toColorizeInput);


        $this->assertEquals($expectedColorized, $actual, 'it should add the format tags');
    }
}
