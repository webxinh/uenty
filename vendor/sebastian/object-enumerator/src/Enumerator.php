<?php
/*
 * This file is part of Object Enumerator.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\ObjectEnumerator;

use SebastianBergmann\RecursionContext\Context;


class Enumerator
{
    
    public function enumerate($variable)
    {
        if (!is_array($variable) && !is_object($variable)) {
            throw new InvalidArgumentException;
        }

        if (isset(func_get_args()[1])) {
            if (!func_get_args()[1] instanceof Context) {
                throw new InvalidArgumentException;
            }

            $processed = func_get_args()[1];
        } else {
            $processed = new Context;
        }

        $objects = [];

        if ($processed->contains($variable)) {
            return $objects;
        }

        $array = $variable;
        $processed->add($variable);

        if (is_array($variable)) {
            foreach ($array as $element) {
                if (!is_array($element) && !is_object($element)) {
                    continue;
                }

                $objects = array_merge(
                    $objects,
                    $this->enumerate($element, $processed)
                );
            }
        } else {
            $objects[] = $variable;
            $reflector = new \ReflectionObject($variable);

            foreach ($reflector->getProperties() as $attribute) {
                $attribute->setAccessible(true);

                $value = $attribute->getValue($variable);

                if (!is_array($value) && !is_object($value)) {
                    continue;
                }

                $objects = array_merge(
                    $objects,
                    $this->enumerate($value, $processed)
                );
            }
        }

        return $objects;
    }
}
