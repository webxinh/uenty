<?php


namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\Types\Context;

class FqsenResolver
{
    
    const OPERATOR_NAMESPACE = '\\';

    public function resolve($fqsen, Context $context = null)
    {
        if ($context === null) {
            $context = new Context('');
        }

        if ($this->isFqsen($fqsen)) {
            return new Fqsen($fqsen);
        }

        return $this->resolvePartialStructuralElementName($fqsen, $context);
    }

    
    private function isFqsen($type)
    {
        return strpos($type, self::OPERATOR_NAMESPACE) === 0;
    }

    
    private function resolvePartialStructuralElementName($type, Context $context)
    {
        $typeParts = explode(self::OPERATOR_NAMESPACE, $type, 2);

        $namespaceAliases = $context->getNamespaceAliases();

        // if the first segment is not an alias; prepend namespace name and return
        if (!isset($namespaceAliases[$typeParts[0]])) {
            $namespace = $context->getNamespace();
            if ('' !== $namespace) {
                $namespace .= self::OPERATOR_NAMESPACE;
            }

            return new Fqsen(self::OPERATOR_NAMESPACE . $namespace . $type);
        }

        $typeParts[0] = $namespaceAliases[$typeParts[0]];

        return new Fqsen(self::OPERATOR_NAMESPACE . implode(self::OPERATOR_NAMESPACE, $typeParts));
    }
}
