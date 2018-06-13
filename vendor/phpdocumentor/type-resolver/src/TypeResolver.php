<?php


namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Object_;

final class TypeResolver
{
    
    const OPERATOR_ARRAY = '[]';

    
    const OPERATOR_NAMESPACE = '\\';

    
    private $keywords = array(
        'string' => 'phpDocumentor\Reflection\Types\String_',
        'int' => 'phpDocumentor\Reflection\Types\Integer',
        'integer' => 'phpDocumentor\Reflection\Types\Integer',
        'bool' => 'phpDocumentor\Reflection\Types\Boolean',
        'boolean' => 'phpDocumentor\Reflection\Types\Boolean',
        'float' => 'phpDocumentor\Reflection\Types\Float_',
        'double' => 'phpDocumentor\Reflection\Types\Float_',
        'object' => 'phpDocumentor\Reflection\Types\Object_',
        'mixed' => 'phpDocumentor\Reflection\Types\Mixed',
        'array' => 'phpDocumentor\Reflection\Types\Array_',
        'resource' => 'phpDocumentor\Reflection\Types\Resource',
        'void' => 'phpDocumentor\Reflection\Types\Void_',
        'null' => 'phpDocumentor\Reflection\Types\Null_',
        'scalar' => 'phpDocumentor\Reflection\Types\Scalar',
        'callback' => 'phpDocumentor\Reflection\Types\Callable_',
        'callable' => 'phpDocumentor\Reflection\Types\Callable_',
        'false' => 'phpDocumentor\Reflection\Types\Boolean',
        'true' => 'phpDocumentor\Reflection\Types\Boolean',
        'self' => 'phpDocumentor\Reflection\Types\Self_',
        '$this' => 'phpDocumentor\Reflection\Types\This',
        'static' => 'phpDocumentor\Reflection\Types\Static_'
    );

    
    private $fqsenResolver;

    
    public function __construct(FqsenResolver $fqsenResolver = null)
    {
        $this->fqsenResolver = $fqsenResolver ?: new FqsenResolver();
    }

    
    public function resolve($type, Context $context = null)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException(
                'Attempted to resolve type but it appeared not to be a string, received: ' . var_export($type, true)
            );
        }

        $type = trim($type);
        if (!$type) {
            throw new \InvalidArgumentException('Attempted to resolve "' . $type . '" but it appears to be empty');
        }

        if ($context === null) {
            $context = new Context('');
        }

        switch (true) {
            case $this->isKeyword($type):
                return $this->resolveKeyword($type);
            case ($this->isCompoundType($type)):
                return $this->resolveCompoundType($type, $context);
            case $this->isTypedArray($type):
                return $this->resolveTypedArray($type, $context);
            case $this->isFqsen($type):
                return $this->resolveTypedObject($type);
            case $this->isPartialStructuralElementName($type):
                return $this->resolveTypedObject($type, $context);
            // @codeCoverageIgnoreStart
            default:
                // I haven't got the foggiest how the logic would come here but added this as a defense.
                throw new \RuntimeException(
                    'Unable to resolve type "' . $type . '", there is no known method to resolve it'
                );
        }
        // @codeCoverageIgnoreEnd
    }

    
    public function addKeyword($keyword, $typeClassName)
    {
        if (!class_exists($typeClassName)) {
            throw new \InvalidArgumentException(
                'The Value Object that needs to be created with a keyword "' . $keyword . '" must be an existing class'
                . ' but we could not find the class ' . $typeClassName
            );
        }

        if (!in_array(Type::class, class_implements($typeClassName))) {
            throw new \InvalidArgumentException(
                'The class "' . $typeClassName . '" must implement the interface "phpDocumentor\Reflection\Type"'
            );
        }

        $this->keywords[$keyword] = $typeClassName;
    }

    
    private function isTypedArray($type)
    {
        return substr($type, -2) === self::OPERATOR_ARRAY;
    }

    
    private function isKeyword($type)
    {
        return in_array(strtolower($type), array_keys($this->keywords), true);
    }

    
    private function isPartialStructuralElementName($type)
    {
        return ($type[0] !== self::OPERATOR_NAMESPACE) && !$this->isKeyword($type);
    }

    
    private function isFqsen($type)
    {
        return strpos($type, self::OPERATOR_NAMESPACE) === 0;
    }

    
    private function isCompoundType($type)
    {
        return strpos($type, '|') !== false;
    }

    
    private function resolveTypedArray($type, Context $context)
    {
        return new Array_($this->resolve(substr($type, 0, -2), $context));
    }

    
    private function resolveKeyword($type)
    {
        $className = $this->keywords[strtolower($type)];

        return new $className();
    }

    
    private function resolveTypedObject($type, Context $context = null)
    {
        return new Object_($this->fqsenResolver->resolve($type, $context));
    }

    
    private function resolveCompoundType($type, Context $context)
    {
        $types = [];

        foreach (explode('|', $type) as $part) {
            $types[] = $this->resolve($part, $context);
        }

        return new Compound($types);
    }
}
