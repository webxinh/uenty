<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Doubler\ClassPatch;

use Prophecy\Doubler\Generator\Node\ClassNode;
use Prophecy\Doubler\Generator\Node\MethodNode;
use Prophecy\PhpDocumentor\ClassAndInterfaceTagRetriever;
use Prophecy\PhpDocumentor\MethodTagRetrieverInterface;


class MagicCallPatch implements ClassPatchInterface
{
    private $tagRetriever;

    public function __construct(MethodTagRetrieverInterface $tagRetriever = null)
    {
        $this->tagRetriever = null === $tagRetriever ? new ClassAndInterfaceTagRetriever() : $tagRetriever;
    }

    
    public function supports(ClassNode $node)
    {
        return true;
    }

    
    public function apply(ClassNode $node)
    {
        $types = array_filter($node->getInterfaces(), function ($interface) {
            return 0 !== strpos($interface, 'Prophecy\\');
        });
        $types[] = $node->getParentClass();

        foreach ($types as $type) {
            $reflectionClass = new \ReflectionClass($type);
            $tagList = $this->tagRetriever->getTagList($reflectionClass);

            foreach($tagList as $tag) {
                $methodName = $tag->getMethodName();

                if (empty($methodName)) {
                    continue;
                }

                if (!$reflectionClass->hasMethod($methodName)) {
                    $methodNode = new MethodNode($methodName);
                    $methodNode->setStatic($tag->isStatic());
                    $node->addMethod($methodNode);
                }
            }
        }
    }

    
    public function getPriority()
    {
        return 50;
    }
}
