<?php

namespace Faker;


class ValidGenerator
{
    protected $generator;
    protected $validator;
    protected $maxRetries;

    
    public function __construct(Generator $generator, $validator = null, $maxRetries = 10000)
    {
        if (is_null($validator)) {
            $validator = function () {
                return true;
            };
        } elseif (!is_callable($validator)) {
            throw new \InvalidArgumentException('valid() only accepts callables as first argument');
        }
        $this->generator = $generator;
        $this->validator = $validator;
        $this->maxRetries = $maxRetries;
    }

    
    public function __get($attribute)
    {
        return $this->__call($attribute, array());
    }

    
    public function __call($name, $arguments)
    {
        $i = 0;
        do {
            $res = call_user_func_array(array($this->generator, $name), $arguments);
            $i++;
            if ($i > $this->maxRetries) {
                throw new \OverflowException(sprintf('Maximum retries of %d reached without finding a valid value', $this->maxRetries));
            }
        } while (!call_user_func($this->validator, $res));

        return $res;
    }
}
