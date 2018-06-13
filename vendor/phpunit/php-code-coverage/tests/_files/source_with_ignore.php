<?php
if ($neverHappens) {
    // @codeCoverageIgnoreStart
    print '*';
    // @codeCoverageIgnoreEnd
}


class Foo
{
    public function bar()
    {
    }
}

class Bar
{
    
    public function foo()
    {
    }
}

function baz()
{
    print '*'; // @codeCoverageIgnore
}

interface Bor
{
    public function foo();

}
