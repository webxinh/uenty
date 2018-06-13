<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Doubler\ClassPatch;

use Prophecy\Doubler\Generator\Node\ClassNode;


class ReflectionClassNewInstancePatch implements ClassPatchInterface
{
    
    public function supports(ClassNode $node)
    {
        return 'ReflectionClass' === $node->getParentClass();
    }

    
    public function apply(ClassNode $node)
    {
        foreach ($node->getMethod('newInstance')->getArguments() as $argument) {
            $argument->setDefault(null);
        }
    }

    
    public function getPriority()
    {
        return 50;
    }
}
