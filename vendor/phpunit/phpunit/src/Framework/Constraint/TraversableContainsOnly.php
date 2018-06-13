<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_TraversableContainsOnly extends PHPUnit_Framework_Constraint
{
    
    protected $constraint;

    
    protected $type;

    
    public function __construct($type, $isNativeType = true)
    {
        parent::__construct();

        if ($isNativeType) {
            $this->constraint = new PHPUnit_Framework_Constraint_IsType($type);
        } else {
            $this->constraint = new PHPUnit_Framework_Constraint_IsInstanceOf(
                $type
            );
        }

        $this->type = $type;
    }

    
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success = true;

        foreach ($other as $item) {
            if (!$this->constraint->evaluate($item, '', true)) {
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
        return 'contains only values of type "' . $this->type . '"';
    }
}
