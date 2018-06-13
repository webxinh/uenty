<?php
/*
 * This file is part of the php-code-coverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\CodeCoverage\Node;

use SebastianBergmann\CodeCoverage\InvalidArgumentException;


class File extends AbstractNode
{
    
    private $coverageData;

    
    private $testData;

    
    private $numExecutableLines = 0;

    
    private $numExecutedLines = 0;

    
    private $classes = [];

    
    private $traits = [];

    
    private $functions = [];

    
    private $linesOfCode = [];

    
    private $numClasses = null;

    
    private $numTestedClasses = 0;

    
    private $numTraits = null;

    
    private $numTestedTraits = 0;

    
    private $numMethods = null;

    
    private $numTestedMethods = null;

    
    private $numTestedFunctions = null;

    
    private $startLines = [];

    
    private $endLines = [];

    
    private $cacheTokens;

    
    public function __construct($name, AbstractNode $parent, array $coverageData, array $testData, $cacheTokens)
    {
        if (!is_bool($cacheTokens)) {
            throw InvalidArgumentException::create(
                1,
                'boolean'
            );
        }

        parent::__construct($name, $parent);

        $this->coverageData = $coverageData;
        $this->testData     = $testData;
        $this->cacheTokens  = $cacheTokens;

        $this->calculateStatistics();
    }

    
    public function count()
    {
        return 1;
    }

    
    public function getCoverageData()
    {
        return $this->coverageData;
    }

    
    public function getTestData()
    {
        return $this->testData;
    }

    
    public function getClasses()
    {
        return $this->classes;
    }

    
    public function getTraits()
    {
        return $this->traits;
    }

    
    public function getFunctions()
    {
        return $this->functions;
    }

    
    public function getLinesOfCode()
    {
        return $this->linesOfCode;
    }

    
    public function getNumExecutableLines()
    {
        return $this->numExecutableLines;
    }

    
    public function getNumExecutedLines()
    {
        return $this->numExecutedLines;
    }

    
    public function getNumClasses()
    {
        if ($this->numClasses === null) {
            $this->numClasses = 0;

            foreach ($this->classes as $class) {
                foreach ($class['methods'] as $method) {
                    if ($method['executableLines'] > 0) {
                        $this->numClasses++;

                        continue 2;
                    }
                }
            }
        }

        return $this->numClasses;
    }

    
    public function getNumTestedClasses()
    {
        return $this->numTestedClasses;
    }

    
    public function getNumTraits()
    {
        if ($this->numTraits === null) {
            $this->numTraits = 0;

            foreach ($this->traits as $trait) {
                foreach ($trait['methods'] as $method) {
                    if ($method['executableLines'] > 0) {
                        $this->numTraits++;

                        continue 2;
                    }
                }
            }
        }

        return $this->numTraits;
    }

    
    public function getNumTestedTraits()
    {
        return $this->numTestedTraits;
    }

    
    public function getNumMethods()
    {
        if ($this->numMethods === null) {
            $this->numMethods = 0;

            foreach ($this->classes as $class) {
                foreach ($class['methods'] as $method) {
                    if ($method['executableLines'] > 0) {
                        $this->numMethods++;
                    }
                }
            }

            foreach ($this->traits as $trait) {
                foreach ($trait['methods'] as $method) {
                    if ($method['executableLines'] > 0) {
                        $this->numMethods++;
                    }
                }
            }
        }

        return $this->numMethods;
    }

    
    public function getNumTestedMethods()
    {
        if ($this->numTestedMethods === null) {
            $this->numTestedMethods = 0;

            foreach ($this->classes as $class) {
                foreach ($class['methods'] as $method) {
                    if ($method['executableLines'] > 0 &&
                        $method['coverage'] == 100) {
                        $this->numTestedMethods++;
                    }
                }
            }

            foreach ($this->traits as $trait) {
                foreach ($trait['methods'] as $method) {
                    if ($method['executableLines'] > 0 &&
                        $method['coverage'] == 100) {
                        $this->numTestedMethods++;
                    }
                }
            }
        }

        return $this->numTestedMethods;
    }

    
    public function getNumFunctions()
    {
        return count($this->functions);
    }

    
    public function getNumTestedFunctions()
    {
        if ($this->numTestedFunctions === null) {
            $this->numTestedFunctions = 0;

            foreach ($this->functions as $function) {
                if ($function['executableLines'] > 0 &&
                    $function['coverage'] == 100) {
                    $this->numTestedFunctions++;
                }
            }
        }

        return $this->numTestedFunctions;
    }

    
    protected function calculateStatistics()
    {
        $classStack = $functionStack = [];

        if ($this->cacheTokens) {
            $tokens = \PHP_Token_Stream_CachingFactory::get($this->getPath());
        } else {
            $tokens = new \PHP_Token_Stream($this->getPath());
        }

        $this->processClasses($tokens);
        $this->processTraits($tokens);
        $this->processFunctions($tokens);
        $this->linesOfCode = $tokens->getLinesOfCode();
        unset($tokens);

        for ($lineNumber = 1; $lineNumber <= $this->linesOfCode['loc']; $lineNumber++) {
            if (isset($this->startLines[$lineNumber])) {
                // Start line of a class.
                if (isset($this->startLines[$lineNumber]['className'])) {
                    if (isset($currentClass)) {
                        $classStack[] = &$currentClass;
                    }

                    $currentClass = &$this->startLines[$lineNumber];
                } // Start line of a trait.
                elseif (isset($this->startLines[$lineNumber]['traitName'])) {
                    $currentTrait = &$this->startLines[$lineNumber];
                } // Start line of a method.
                elseif (isset($this->startLines[$lineNumber]['methodName'])) {
                    $currentMethod = &$this->startLines[$lineNumber];
                } // Start line of a function.
                elseif (isset($this->startLines[$lineNumber]['functionName'])) {
                    if (isset($currentFunction)) {
                        $functionStack[] = &$currentFunction;
                    }

                    $currentFunction = &$this->startLines[$lineNumber];
                }
            }

            if (isset($this->coverageData[$lineNumber])) {
                if (isset($currentClass)) {
                    $currentClass['executableLines']++;
                }

                if (isset($currentTrait)) {
                    $currentTrait['executableLines']++;
                }

                if (isset($currentMethod)) {
                    $currentMethod['executableLines']++;
                }

                if (isset($currentFunction)) {
                    $currentFunction['executableLines']++;
                }

                $this->numExecutableLines++;

                if (count($this->coverageData[$lineNumber]) > 0) {
                    if (isset($currentClass)) {
                        $currentClass['executedLines']++;
                    }

                    if (isset($currentTrait)) {
                        $currentTrait['executedLines']++;
                    }

                    if (isset($currentMethod)) {
                        $currentMethod['executedLines']++;
                    }

                    if (isset($currentFunction)) {
                        $currentFunction['executedLines']++;
                    }

                    $this->numExecutedLines++;
                }
            }

            if (isset($this->endLines[$lineNumber])) {
                // End line of a class.
                if (isset($this->endLines[$lineNumber]['className'])) {
                    unset($currentClass);

                    if ($classStack) {
                        end($classStack);
                        $key          = key($classStack);
                        $currentClass = &$classStack[$key];
                        unset($classStack[$key]);
                    }
                } // End line of a trait.
                elseif (isset($this->endLines[$lineNumber]['traitName'])) {
                    unset($currentTrait);
                } // End line of a method.
                elseif (isset($this->endLines[$lineNumber]['methodName'])) {
                    unset($currentMethod);
                } // End line of a function.
                elseif (isset($this->endLines[$lineNumber]['functionName'])) {
                    unset($currentFunction);

                    if ($functionStack) {
                        end($functionStack);
                        $key             = key($functionStack);
                        $currentFunction = &$functionStack[$key];
                        unset($functionStack[$key]);
                    }
                }
            }
        }

        foreach ($this->traits as &$trait) {
            foreach ($trait['methods'] as &$method) {
                if ($method['executableLines'] > 0) {
                    $method['coverage'] = ($method['executedLines'] /
                            $method['executableLines']) * 100;
                } else {
                    $method['coverage'] = 100;
                }

                $method['crap'] = $this->crap(
                    $method['ccn'],
                    $method['coverage']
                );

                $trait['ccn'] += $method['ccn'];
            }

            if ($trait['executableLines'] > 0) {
                $trait['coverage'] = ($trait['executedLines'] /
                        $trait['executableLines']) * 100;

                if ($trait['coverage'] == 100) {
                    $this->numTestedClasses++;
                }
            } else {
                $trait['coverage'] = 100;
            }

            $trait['crap'] = $this->crap(
                $trait['ccn'],
                $trait['coverage']
            );
        }

        foreach ($this->classes as &$class) {
            foreach ($class['methods'] as &$method) {
                if ($method['executableLines'] > 0) {
                    $method['coverage'] = ($method['executedLines'] /
                            $method['executableLines']) * 100;
                } else {
                    $method['coverage'] = 100;
                }

                $method['crap'] = $this->crap(
                    $method['ccn'],
                    $method['coverage']
                );

                $class['ccn'] += $method['ccn'];
            }

            if ($class['executableLines'] > 0) {
                $class['coverage'] = ($class['executedLines'] /
                        $class['executableLines']) * 100;

                if ($class['coverage'] == 100) {
                    $this->numTestedClasses++;
                }
            } else {
                $class['coverage'] = 100;
            }

            $class['crap'] = $this->crap(
                $class['ccn'],
                $class['coverage']
            );
        }
    }

    
    protected function processClasses(\PHP_Token_Stream $tokens)
    {
        $classes = $tokens->getClasses();
        unset($tokens);

        $link = $this->getId() . '.html#';

        foreach ($classes as $className => $class) {
            $this->classes[$className] = [
                'className'       => $className,
                'methods'         => [],
                'startLine'       => $class['startLine'],
                'executableLines' => 0,
                'executedLines'   => 0,
                'ccn'             => 0,
                'coverage'        => 0,
                'crap'            => 0,
                'package'         => $class['package'],
                'link'            => $link . $class['startLine']
            ];

            $this->startLines[$class['startLine']] = &$this->classes[$className];
            $this->endLines[$class['endLine']]     = &$this->classes[$className];

            foreach ($class['methods'] as $methodName => $method) {
                $this->classes[$className]['methods'][$methodName] = $this->newMethod($methodName, $method, $link);

                $this->startLines[$method['startLine']] = &$this->classes[$className]['methods'][$methodName];
                $this->endLines[$method['endLine']]     = &$this->classes[$className]['methods'][$methodName];
            }
        }
    }

    
    protected function processTraits(\PHP_Token_Stream $tokens)
    {
        $traits = $tokens->getTraits();
        unset($tokens);

        $link = $this->getId() . '.html#';

        foreach ($traits as $traitName => $trait) {
            $this->traits[$traitName] = [
                'traitName'       => $traitName,
                'methods'         => [],
                'startLine'       => $trait['startLine'],
                'executableLines' => 0,
                'executedLines'   => 0,
                'ccn'             => 0,
                'coverage'        => 0,
                'crap'            => 0,
                'package'         => $trait['package'],
                'link'            => $link . $trait['startLine']
            ];

            $this->startLines[$trait['startLine']] = &$this->traits[$traitName];
            $this->endLines[$trait['endLine']]     = &$this->traits[$traitName];

            foreach ($trait['methods'] as $methodName => $method) {
                $this->traits[$traitName]['methods'][$methodName] = $this->newMethod($methodName, $method, $link);

                $this->startLines[$method['startLine']] = &$this->traits[$traitName]['methods'][$methodName];
                $this->endLines[$method['endLine']]     = &$this->traits[$traitName]['methods'][$methodName];
            }
        }
    }

    
    protected function processFunctions(\PHP_Token_Stream $tokens)
    {
        $functions = $tokens->getFunctions();
        unset($tokens);

        $link = $this->getId() . '.html#';

        foreach ($functions as $functionName => $function) {
            $this->functions[$functionName] = [
                'functionName'    => $functionName,
                'signature'       => $function['signature'],
                'startLine'       => $function['startLine'],
                'executableLines' => 0,
                'executedLines'   => 0,
                'ccn'             => $function['ccn'],
                'coverage'        => 0,
                'crap'            => 0,
                'link'            => $link . $function['startLine']
            ];

            $this->startLines[$function['startLine']] = &$this->functions[$functionName];
            $this->endLines[$function['endLine']]     = &$this->functions[$functionName];
        }
    }

    
    protected function crap($ccn, $coverage)
    {
        if ($coverage == 0) {
            return (string) (pow($ccn, 2) + $ccn);
        }

        if ($coverage >= 95) {
            return (string) $ccn;
        }

        return sprintf(
            '%01.2F',
            pow($ccn, 2) * pow(1 - $coverage/100, 3) + $ccn
        );
    }

    
    private function newMethod($methodName, array $method, $link)
    {
        return [
            'methodName'      => $methodName,
            'visibility'      => $method['visibility'],
            'signature'       => $method['signature'],
            'startLine'       => $method['startLine'],
            'endLine'         => $method['endLine'],
            'executableLines' => 0,
            'executedLines'   => 0,
            'ccn'             => $method['ccn'],
            'coverage'        => 0,
            'crap'            => 0,
            'link'            => $link . $method['startLine'],
        ];
    }
}
