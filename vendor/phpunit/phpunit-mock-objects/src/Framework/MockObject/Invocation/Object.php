<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_MockObject_Invocation_Object extends PHPUnit_Framework_MockObject_Invocation_Static
{
    
    public $object;

    
    public function __construct($className, $methodName, array $parameters, $returnType, $object, $cloneObjects = false)
    {
        parent::__construct($className, $methodName, $parameters, $returnType, $cloneObjects);

        $this->object = $object;
    }
}
