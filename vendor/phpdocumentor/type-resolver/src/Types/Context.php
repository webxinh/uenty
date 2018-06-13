<?php


namespace phpDocumentor\Reflection\Types;


final class Context
{
    
    private $namespace = '';

    
    private $namespaceAliases = [];

    
    public function __construct($namespace, array $namespaceAliases = [])
    {
        $this->namespace = ('global' !== $namespace && 'default' !== $namespace)
            ? trim((string)$namespace, '\\')
            : '';

        foreach ($namespaceAliases as $alias => $fqnn) {
            if ($fqnn[0] === '\\') {
                $fqnn = substr($fqnn, 1);
            }
            if ($fqnn[strlen($fqnn) - 1] === '\\') {
                $fqnn = substr($fqnn, 0, -1);
            }

            $namespaceAliases[$alias] = $fqnn;
        }

        $this->namespaceAliases = $namespaceAliases;
    }

    
    public function getNamespace()
    {
        return $this->namespace;
    }

    
    public function getNamespaceAliases()
    {
        return $this->namespaceAliases;
    }
}
