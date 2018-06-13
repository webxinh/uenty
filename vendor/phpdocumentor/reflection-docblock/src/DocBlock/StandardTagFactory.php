<?php


namespace phpDocumentor\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock\Tags\Factory\StaticMethod;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;


final class StandardTagFactory implements TagFactory
{
    
    const REGEX_TAGNAME = '[\w\-\_\\\\]+';

    
    private $tagHandlerMappings = [
        'author'         => '\phpDocumentor\Reflection\DocBlock\Tags\Author',
        'covers'         => '\phpDocumentor\Reflection\DocBlock\Tags\Covers',
        'deprecated'     => '\phpDocumentor\Reflection\DocBlock\Tags\Deprecated',
        // 'example'        => '\phpDocumentor\Reflection\DocBlock\Tags\Example',
        'link'           => '\phpDocumentor\Reflection\DocBlock\Tags\Link',
        'method'         => '\phpDocumentor\Reflection\DocBlock\Tags\Method',
        'param'          => '\phpDocumentor\Reflection\DocBlock\Tags\Param',
        'property-read'  => '\phpDocumentor\Reflection\DocBlock\Tags\PropertyRead',
        'property'       => '\phpDocumentor\Reflection\DocBlock\Tags\Property',
        'property-write' => '\phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite',
        'return'         => '\phpDocumentor\Reflection\DocBlock\Tags\Return_',
        'see'            => '\phpDocumentor\Reflection\DocBlock\Tags\See',
        'since'          => '\phpDocumentor\Reflection\DocBlock\Tags\Since',
        'source'         => '\phpDocumentor\Reflection\DocBlock\Tags\Source',
        'throw'          => '\phpDocumentor\Reflection\DocBlock\Tags\Throws',
        'throws'         => '\phpDocumentor\Reflection\DocBlock\Tags\Throws',
        'uses'           => '\phpDocumentor\Reflection\DocBlock\Tags\Uses',
        'var'            => '\phpDocumentor\Reflection\DocBlock\Tags\Var_',
        'version'        => '\phpDocumentor\Reflection\DocBlock\Tags\Version'
    ];

    
    private $tagHandlerParameterCache = [];

    
    private $fqsenResolver;

    
    private $serviceLocator = [];

    
    public function __construct(FqsenResolver $fqsenResolver, array $tagHandlers = null)
    {
        $this->fqsenResolver = $fqsenResolver;
        if ($tagHandlers !== null) {
            $this->tagHandlerMappings = $tagHandlers;
        }

        $this->addService($fqsenResolver, FqsenResolver::class);
    }

    
    public function create($tagLine, TypeContext $context = null)
    {
        if (! $context) {
            $context = new TypeContext('');
        }

        list($tagName, $tagBody) = $this->extractTagParts($tagLine);

        return $this->createTag($tagBody, $tagName, $context);
    }

    
    public function addParameter($name, $value)
    {
        $this->serviceLocator[$name] = $value;
    }

    
    public function addService($service, $alias = null)
    {
        $this->serviceLocator[$alias ?: get_class($service)] = $service;
    }

    
    public function registerTagHandler($tagName, $handler)
    {
        Assert::stringNotEmpty($tagName);
        Assert::stringNotEmpty($handler);
        Assert::classExists($handler);
        Assert::implementsInterface($handler, StaticMethod::class);

        if (strpos($tagName, '\\') && $tagName[0] !== '\\') {
            throw new \InvalidArgumentException(
                'A namespaced tag must have a leading backslash as it must be fully qualified'
            );
        }

        $this->tagHandlerMappings[$tagName] = $handler;
    }

    
    private function extractTagParts($tagLine)
    {
        $matches = array();
        if (! preg_match('/^@(' . self::REGEX_TAGNAME . ')(?:\s*([^\s].*)|$)?/us', $tagLine, $matches)) {
            throw new \InvalidArgumentException(
                'The tag "' . $tagLine . '" does not seem to be wellformed, please check it for errors'
            );
        }

        if (count($matches) < 3) {
            $matches[] = '';
        }

        return array_slice($matches, 1);
    }

    
    private function createTag($body, $name, TypeContext $context)
    {
        $handlerClassName = $this->findHandlerClassName($name, $context);
        $arguments        = $this->getArgumentsForParametersFromWiring(
            $this->fetchParametersForHandlerFactoryMethod($handlerClassName),
            $this->getServiceLocatorWithDynamicParameters($context, $name, $body)
        )
        ;

        return call_user_func_array([$handlerClassName, 'create'], $arguments);
    }

    
    private function findHandlerClassName($tagName, TypeContext $context)
    {
        $handlerClassName = Generic::class;
        if (isset($this->tagHandlerMappings[$tagName])) {
            $handlerClassName = $this->tagHandlerMappings[$tagName];
        } elseif ($this->isAnnotation($tagName)) {
            // TODO: Annotation support is planned for a later stage and as such is disabled for now
            // $tagName = (string)$this->fqsenResolver->resolve($tagName, $context);
            // if (isset($this->annotationMappings[$tagName])) {
            //     $handlerClassName = $this->annotationMappings[$tagName];
            // }
        }

        return $handlerClassName;
    }

    
    private function getArgumentsForParametersFromWiring($parameters, $locator)
    {
        $arguments = [];
        foreach ($parameters as $index => $parameter) {
            $typeHint = $parameter->getClass() ? $parameter->getClass()->getName() : null;
            if (isset($locator[$typeHint])) {
                $arguments[] = $locator[$typeHint];
                continue;
            }

            $parameterName = $parameter->getName();
            if (isset($locator[$parameterName])) {
                $arguments[] = $locator[$parameterName];
                continue;
            }

            $arguments[] = null;
        }

        return $arguments;
    }

    
    private function fetchParametersForHandlerFactoryMethod($handlerClassName)
    {
        if (! isset($this->tagHandlerParameterCache[$handlerClassName])) {
            $methodReflection                                  = new \ReflectionMethod($handlerClassName, 'create');
            $this->tagHandlerParameterCache[$handlerClassName] = $methodReflection->getParameters();
        }

        return $this->tagHandlerParameterCache[$handlerClassName];
    }

    
    private function getServiceLocatorWithDynamicParameters(TypeContext $context, $tagName, $tagBody)
    {
        $locator = array_merge(
            $this->serviceLocator,
            [
                'name'             => $tagName,
                'body'             => $tagBody,
                TypeContext::class => $context
            ]
        );

        return $locator;
    }

    
    private function isAnnotation($tagContent)
    {
        // 1. Contains a namespace separator
        // 2. Contains parenthesis
        // 3. Is present in a list of known annotations (make the algorithm smart by first checking is the last part
        //    of the annotation class name matches the found tag name

        return false;
    }
}
