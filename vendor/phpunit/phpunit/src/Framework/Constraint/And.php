<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_And extends PHPUnit_Framework_Constraint
{
    
    protected $constraints = [];

    
    protected $lastConstraint = null;

    
    public function setConstraints(array $constraints)
    {
        $this->constraints = [];

        foreach ($constraints as $constraint) {
            if (!($constraint instanceof PHPUnit_Framework_Constraint)) {
                throw new PHPUnit_Framework_Exception(
                    'All parameters to ' . __CLASS__ .
                    ' must be a constraint object.'
                );
            }

            $this->constraints[] = $constraint;
        }
    }

    
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success    = true;
        $constraint = null;

        foreach ($this->constraints as $constraint) {
            if (!$constraint->evaluate($other, $description, true)) {
                $success = false;
                break;
            }
        }

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $this->fail($other, $description);
        }
    }

    
    public function toString()
    {
        $text = '';

        foreach ($this->constraints as $key => $constraint) {
            if ($key > 0) {
                $text .= ' and ';
            }

            $text .= $constraint->toString();
        }

        return $text;
    }

    
    public function count()
    {
        $count = 0;

        foreach ($this->constraints as $constraint) {
            $count += count($constraint);
        }

        return $count;
    }
}
