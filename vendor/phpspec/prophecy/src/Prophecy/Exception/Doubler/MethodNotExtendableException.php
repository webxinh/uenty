<?php

    namespace Prophecy\Exception\Doubler;

    class MethodNotExtendableException extends DoubleException
    {
        private $methodName;

        private $className;

        
        public function __construct($message, $className, $methodName)
        {
            parent::__construct($message);

            $this->methodName = $methodName;
            $this->className = $className;
        }


        
        public function getMethodName()
        {
            return $this->methodName;
        }

        
        public function getClassName()
        {
            return $this->className;
        }

    }
