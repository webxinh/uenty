<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_Xor extends PHPUnit_Framework_Constraint
{
    
    protected $constraints = [];

    
    public function setConstraints(array $constraints)
    {
        $this->constraints = [];

        foreach ($constraints as $constraint) {
            if (!($constraint instanceof PHPUnit_Framework_Constraint)) {
                $constraint = new PHPUnit_Framework_Constraint_IsEqual(
                    $constraint
                );
            }

            $this->constraints[] = $constraint;
        }
    }

    
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success    = true;
        $lastResult = null;
        $constraint = null;

        foreach ($this->constraints as $constraint) {
            $result = $constraint->evaluate($other, $description, true);

            if ($result === $lastResult) {
                $success = false;
                break;
            }

            $lastResult = $result;
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
                $text .= ' xor ';
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
