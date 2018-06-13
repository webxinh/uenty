<?php


namespace phpDocumentor\Reflection\Types;


final class ContextFactory
{
    
    const T_LITERAL_END_OF_USE = ';';

    
    const T_LITERAL_USE_SEPARATOR = ',';

    
    public function createFromReflector(\Reflector $reflector)
    {
        if (method_exists($reflector, 'getDeclaringClass')) {
            $reflector = $reflector->getDeclaringClass();
        }

        $fileName = $reflector->getFileName();
        $namespace = $reflector->getNamespaceName();

        if (file_exists($fileName)) {
            return $this->createForNamespace($namespace, file_get_contents($fileName));
        }

        return new Context($namespace, []);
    }

    
    public function createForNamespace($namespace, $fileContents)
    {
        $namespace = trim($namespace, '\\');
        $useStatements = [];
        $currentNamespace = '';
        $tokens = new \ArrayIterator(token_get_all($fileContents));

        while ($tokens->valid()) {
            switch ($tokens->current()[0]) {
                case T_NAMESPACE:
                    $currentNamespace = $this->parseNamespace($tokens);
                    break;
                case T_CLASS:
                    // Fast-forward the iterator through the class so that any
                    // T_USE tokens found within are skipped - these are not
                    // valid namespace use statements so should be ignored.
                    $braceLevel = 0;
                    $firstBraceFound = false;
                    while ($tokens->valid() && ($braceLevel > 0 || !$firstBraceFound)) {
                        if ($tokens->current() === '{'
                            || $tokens->current()[0] === T_CURLY_OPEN
                            || $tokens->current()[0] === T_DOLLAR_OPEN_CURLY_BRACES) {
                            if (!$firstBraceFound) {
                                $firstBraceFound = true;
                            }
                            $braceLevel++;
                        }

                        if ($tokens->current() === '}') {
                            $braceLevel--;
                        }
                        $tokens->next();
                    }
                    break;
                case T_USE:
                    if ($currentNamespace === $namespace) {
                        $useStatements = array_merge($useStatements, $this->parseUseStatement($tokens));
                    }
                    break;
            }
            $tokens->next();
        }

        return new Context($namespace, $useStatements);
    }

    
    private function parseNamespace(\ArrayIterator $tokens)
    {
        // skip to the first string or namespace separator
        $this->skipToNextStringOrNamespaceSeparator($tokens);

        $name = '';
        while ($tokens->valid() && ($tokens->current()[0] === T_STRING || $tokens->current()[0] === T_NS_SEPARATOR)
        ) {
            $name .= $tokens->current()[1];
            $tokens->next();
        }

        return $name;
    }

    
    private function parseUseStatement(\ArrayIterator $tokens)
    {
        $uses = [];
        $continue = true;

        while ($continue) {
            $this->skipToNextStringOrNamespaceSeparator($tokens);

            list($alias, $fqnn) = $this->extractUseStatement($tokens);
            $uses[$alias] = $fqnn;
            if ($tokens->current()[0] === self::T_LITERAL_END_OF_USE) {
                $continue = false;
            }
        }

        return $uses;
    }

    
    private function skipToNextStringOrNamespaceSeparator(\ArrayIterator $tokens)
    {
        while ($tokens->valid() && ($tokens->current()[0] !== T_STRING) && ($tokens->current()[0] !== T_NS_SEPARATOR)) {
            $tokens->next();
        }
    }

    
    private function extractUseStatement(\ArrayIterator $tokens)
    {
        $result = [''];
        while ($tokens->valid()
            && ($tokens->current()[0] !== self::T_LITERAL_USE_SEPARATOR)
            && ($tokens->current()[0] !== self::T_LITERAL_END_OF_USE)
        ) {
            if ($tokens->current()[0] === T_AS) {
                $result[] = '';
            }
            if ($tokens->current()[0] === T_STRING || $tokens->current()[0] === T_NS_SEPARATOR) {
                $result[count($result) - 1] .= $tokens->current()[1];
            }
            $tokens->next();
        }

        if (count($result) == 1) {
            $backslashPos = strrpos($result[0], '\\');

            if (false !== $backslashPos) {
                $result[] = substr($result[0], $backslashPos + 1);
            } else {
                $result[] = $result[0];
            }
        }

        return array_reverse($result);
    }
}
