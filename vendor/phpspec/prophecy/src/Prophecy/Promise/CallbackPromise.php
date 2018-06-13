<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Promise;

use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Exception\InvalidArgumentException;
use Closure;


class CallbackPromise implements PromiseInterface
{
    private $callback;

    
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException(sprintf(
                'Callable expected as an argument to CallbackPromise, but got %s.',
                gettype($callback)
            ));
        }

        $this->callback = $callback;
    }

    
    public function execute(array $args, ObjectProphecy $object, MethodProphecy $method)
    {
        $callback = $this->callback;

        if ($callback instanceof Closure && method_exists('Closure', 'bind')) {
            $callback = Closure::bind($callback, $object);
        }

        return call_user_func($callback, $args, $object, $method);
    }
}
