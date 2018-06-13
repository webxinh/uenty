<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Argument\Token;

use SebastianBergmann\Comparator\ComparisonFailure;
use Prophecy\Comparator\Factory as ComparatorFactory;
use Prophecy\Util\StringUtil;


class ObjectStateToken implements TokenInterface
{
    private $name;
    private $value;
    private $util;
    private $comparatorFactory;

    
    public function __construct(
        $methodName,
        $value,
        StringUtil $util = null,
        ComparatorFactory $comparatorFactory = null
    ) {
        $this->name  = $methodName;
        $this->value = $value;
        $this->util  = $util ?: new StringUtil;

        $this->comparatorFactory = $comparatorFactory ?: ComparatorFactory::getInstance();
    }

    
    public function scoreArgument($argument)
    {
        if (is_object($argument) && method_exists($argument, $this->name)) {
            $actual = call_user_func(array($argument, $this->name));

            $comparator = $this->comparatorFactory->getComparatorFor(
                $this->value, $actual
            );

            try {
                $comparator->assertEquals($this->value, $actual);
                return 8;
            } catch (ComparisonFailure $failure) {
                return false;
            }
        }

        if (is_object($argument) && property_exists($argument, $this->name)) {
            return $argument->{$this->name} === $this->value ? 8 : false;
        }

        return false;
    }

    
    public function isLast()
    {
        return false;
    }

    
    public function __toString()
    {
        return sprintf('state(%s(), %s)',
            $this->name,
            $this->util->stringify($this->value)
        );
    }
}
