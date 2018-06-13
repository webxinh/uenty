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


class ReturnPromise implements PromiseInterface
{
    private $returnValues = array();

    
    public function __construct(array $returnValues)
    {
        $this->returnValues = $returnValues;
    }

    
    public function execute(array $args, ObjectProphecy $object, MethodProphecy $method)
    {
        $value = array_shift($this->returnValues);

        if (!count($this->returnValues)) {
            $this->returnValues[] = $value;
        }

        return $value;
    }
}
