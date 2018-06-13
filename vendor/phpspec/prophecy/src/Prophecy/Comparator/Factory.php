<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Comparator;

use SebastianBergmann\Comparator\Factory as BaseFactory;


final class Factory extends BaseFactory
{
    
    private static $instance;

    public function __construct()
    {
        parent::__construct();

        $this->register(new ClosureComparator());
        $this->register(new ProphecyComparator());
    }

    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Factory;
        }

        return self::$instance;
    }
}
