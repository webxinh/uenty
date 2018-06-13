<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_Callback extends PHPUnit_Framework_Constraint
{
    private $callback;

    
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                1,
                'callable'
            );
        }

        parent::__construct();

        $this->callback = $callback;
    }

    
    protected function matches($other)
    {
        return call_user_func($this->callback, $other);
    }

    
    public function toString()
    {
        return 'is accepted by specified callback';
    }
}
