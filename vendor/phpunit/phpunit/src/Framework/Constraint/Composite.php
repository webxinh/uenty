<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


abstract class PHPUnit_Framework_Constraint_Composite extends PHPUnit_Framework_Constraint
{
    
    protected $innerConstraint;

    
    public function __construct(PHPUnit_Framework_Constraint $innerConstraint)
    {
        parent::__construct();
        $this->innerConstraint = $innerConstraint;
    }

    
    public function evaluate($other, $description = '', $returnResult = false)
    {
        try {
            return $this->innerConstraint->evaluate(
                $other,
                $description,
                $returnResult
            );
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->fail($other, $description);
        }
    }

    
    public function count()
    {
        return count($this->innerConstraint);
    }
}
