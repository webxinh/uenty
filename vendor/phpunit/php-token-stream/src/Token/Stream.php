<?php
/*
 * This file is part of the PHP_TokenStream package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHP_Token_Stream implements ArrayAccess, Countable, SeekableIterator
{
    
    protected static $customTokens = array(
        '(' => 'PHP_Token_OPEN_BRACKET',
        ')' => 'PHP_Token_CLOSE_BRACKET',
        '[' => 'PHP_Token_OPEN_SQUARE',
        ']' => 'PHP_Token_CLOSE_SQUARE',
        '{' => 'PHP_Token_OPEN_CURLY',
        '}' => 'PHP_Token_CLOSE_CURLY',
        ';' => 'PHP_Token_SEMICOLON',
        '.' => 'PHP_Token_DOT',
        ',' => 'PHP_Token_COMMA',
        '=' => 'PHP_Token_EQUAL',
        '<' => 'PHP_Token_LT',
        '>' => 'PHP_Token_GT',
        '+' => 'PHP_Token_PLUS',
        '-' => 'PHP_Token_MINUS',
        '*' => 'PHP_Token_MULT',
        '/' => 'PHP_Token_DIV',
        '?' => 'PHP_Token_QUESTION_MARK',
        '!' => 'PHP_Token_EXCLAMATION_MARK',
        ':' => 'PHP_Token_COLON',
        '"' => 'PHP_Token_DOUBLE_QUOTES',
        '@' => 'PHP_Token_AT',
        '&' => 'PHP_Token_AMPERSAND',
        '%' => 'PHP_Token_PERCENT',
        '|' => 'PHP_Token_PIPE',
        '$' => 'PHP_Token_DOLLAR',
        '^' => 'PHP_Token_CARET',
        '~' => 'PHP_Token_TILDE',
        '`' => 'PHP_Token_BACKTICK'
    );

    
    protected $filename;

    
    protected $tokens = array();

    
    protected $position = 0;

    
    protected $linesOfCode = array('loc' => 0, 'cloc' => 0, 'ncloc' => 0);

    
    protected $classes;

    
    protected $functions;

    
    protected $includes;

    
    protected $interfaces;

    
    protected $traits;

    
    protected $lineToFunctionMap = array();

    
    public function __construct($sourceCode)
    {
        if (is_file($sourceCode)) {
            $this->filename = $sourceCode;
            $sourceCode     = file_get_contents($sourceCode);
        }

        $this->scan($sourceCode);
    }

    
    public function __destruct()
    {
        $this->tokens = array();
    }

    
    public function __toString()
    {
        $buffer = '';

        foreach ($this as $token) {
            $buffer .= $token;
        }

        return $buffer;
    }

    
    public function getFilename()
    {
        return $this->filename;
    }

    
    protected function scan($sourceCode)
    {
        $line      = 1;
        $tokens    = token_get_all($sourceCode);
        $numTokens = count($tokens);

        $lastNonWhitespaceTokenWasDoubleColon = false;

        for ($i = 0; $i < $numTokens; ++$i) {
            $token = $tokens[$i];
            unset($tokens[$i]);

            if (is_array($token)) {
                $name = substr(token_name($token[0]), 2);
                $text = $token[1];

                if ($lastNonWhitespaceTokenWasDoubleColon && $name == 'CLASS') {
                    $name = 'CLASS_NAME_CONSTANT';
                } elseif ($name == 'USE' && isset($tokens[$i+2][0]) && $tokens[$i+2][0] == T_FUNCTION) {
                    unset($tokens[$i+1]);
                    unset($tokens[$i+2]);

                    $i += 2;

                    $name = 'USE_FUNCTION';
                }

                $tokenClass = 'PHP_Token_' . $name;
            } else {
                $text       = $token;
                $tokenClass = self::$customTokens[$token];
            }

            $this->tokens[] = new $tokenClass($text, $line, $this, $i);
            $lines          = substr_count($text, "\n");
            $line          += $lines;

            if ($tokenClass == 'PHP_Token_HALT_COMPILER') {
                break;
            } elseif ($tokenClass == 'PHP_Token_COMMENT' ||
                $tokenClass == 'PHP_Token_DOC_COMMENT') {
                $this->linesOfCode['cloc'] += $lines + 1;
            }

            if ($name == 'DOUBLE_COLON') {
                $lastNonWhitespaceTokenWasDoubleColon = true;
            } elseif ($name != 'WHITESPACE') {
                $lastNonWhitespaceTokenWasDoubleColon = false;
            }
        }

        $this->linesOfCode['loc']   = substr_count($sourceCode, "\n");
        $this->linesOfCode['ncloc'] = $this->linesOfCode['loc'] -
                                      $this->linesOfCode['cloc'];
    }

    
    public function count()
    {
        return count($this->tokens);
    }

    
    public function tokens()
    {
        return $this->tokens;
    }

    
    public function getClasses()
    {
        if ($this->classes !== null) {
            return $this->classes;
        }

        $this->parse();

        return $this->classes;
    }

    
    public function getFunctions()
    {
        if ($this->functions !== null) {
            return $this->functions;
        }

        $this->parse();

        return $this->functions;
    }

    
    public function getInterfaces()
    {
        if ($this->interfaces !== null) {
            return $this->interfaces;
        }

        $this->parse();

        return $this->interfaces;
    }

    
    public function getTraits()
    {
        if ($this->traits !== null) {
            return $this->traits;
        }

        $this->parse();

        return $this->traits;
    }

    
    public function getIncludes($categorize = false, $category = null)
    {
        if ($this->includes === null) {
            $this->includes = array(
              'require_once' => array(),
              'require'      => array(),
              'include_once' => array(),
              'include'      => array()
            );

            foreach ($this->tokens as $token) {
                switch (get_class($token)) {
                    case 'PHP_Token_REQUIRE_ONCE':
                    case 'PHP_Token_REQUIRE':
                    case 'PHP_Token_INCLUDE_ONCE':
                    case 'PHP_Token_INCLUDE':
                        $this->includes[$token->getType()][] = $token->getName();
                        break;
                }
            }
        }

        if (isset($this->includes[$category])) {
            $includes = $this->includes[$category];
        } elseif ($categorize === false) {
            $includes = array_merge(
                $this->includes['require_once'],
                $this->includes['require'],
                $this->includes['include_once'],
                $this->includes['include']
            );
        } else {
            $includes = $this->includes;
        }

        return $includes;
    }

    
    public function getFunctionForLine($line)
    {
        $this->parse();

        if (isset($this->lineToFunctionMap[$line])) {
            return $this->lineToFunctionMap[$line];
        }
    }

    protected function parse()
    {
        $this->interfaces = array();
        $this->classes    = array();
        $this->traits     = array();
        $this->functions  = array();
        $class            = array();
        $classEndLine     = array();
        $trait            = false;
        $traitEndLine     = false;
        $interface        = false;
        $interfaceEndLine = false;

        foreach ($this->tokens as $token) {
            switch (get_class($token)) {
                case 'PHP_Token_HALT_COMPILER':
                    return;

                case 'PHP_Token_INTERFACE':
                    $interface        = $token->getName();
                    $interfaceEndLine = $token->getEndLine();

                    $this->interfaces[$interface] = array(
                      'methods'   => array(),
                      'parent'    => $token->getParent(),
                      'keywords'  => $token->getKeywords(),
                      'docblock'  => $token->getDocblock(),
                      'startLine' => $token->getLine(),
                      'endLine'   => $interfaceEndLine,
                      'package'   => $token->getPackage(),
                      'file'      => $this->filename
                    );
                    break;

                case 'PHP_Token_CLASS':
                case 'PHP_Token_TRAIT':
                    $tmp = array(
                      'methods'   => array(),
                      'parent'    => $token->getParent(),
                      'interfaces'=> $token->getInterfaces(),
                      'keywords'  => $token->getKeywords(),
                      'docblock'  => $token->getDocblock(),
                      'startLine' => $token->getLine(),
                      'endLine'   => $token->getEndLine(),
                      'package'   => $token->getPackage(),
                      'file'      => $this->filename
                    );

                    if ($token instanceof PHP_Token_CLASS) {
                        $class[]        = $token->getName();
                        $classEndLine[] = $token->getEndLine();

                        if ($class[count($class)-1] != 'anonymous class') {
                            $this->classes[$class[count($class)-1]] = $tmp;
                        }
                    } else {
                        $trait                = $token->getName();
                        $traitEndLine         = $token->getEndLine();
                        $this->traits[$trait] = $tmp;
                    }
                    break;

                case 'PHP_Token_FUNCTION':
                    $name = $token->getName();
                    $tmp  = array(
                      'docblock'  => $token->getDocblock(),
                      'keywords'  => $token->getKeywords(),
                      'visibility'=> $token->getVisibility(),
                      'signature' => $token->getSignature(),
                      'startLine' => $token->getLine(),
                      'endLine'   => $token->getEndLine(),
                      'ccn'       => $token->getCCN(),
                      'file'      => $this->filename
                    );

                    if (empty($class) &&
                        $trait === false &&
                        $interface === false) {
                        $this->functions[$name] = $tmp;

                        $this->addFunctionToMap(
                            $name,
                            $tmp['startLine'],
                            $tmp['endLine']
                        );
                    } elseif (!empty($class) && $class[count($class)-1] != 'anonymous class') {
                        $this->classes[$class[count($class)-1]]['methods'][$name] = $tmp;

                        $this->addFunctionToMap(
                            $class[count($class)-1] . '::' . $name,
                            $tmp['startLine'],
                            $tmp['endLine']
                        );
                    } elseif ($trait !== false) {
                        $this->traits[$trait]['methods'][$name] = $tmp;

                        $this->addFunctionToMap(
                            $trait . '::' . $name,
                            $tmp['startLine'],
                            $tmp['endLine']
                        );
                    } else {
                        $this->interfaces[$interface]['methods'][$name] = $tmp;
                    }
                    break;

                case 'PHP_Token_CLOSE_CURLY':
                    if (!empty($classEndLine) &&
                        $classEndLine[count($classEndLine)-1] == $token->getLine()) {
                        array_pop($classEndLine);
                        array_pop($class);
                    } elseif ($traitEndLine !== false &&
                        $traitEndLine == $token->getLine()) {
                        $trait        = false;
                        $traitEndLine = false;
                    } elseif ($interfaceEndLine !== false &&
                        $interfaceEndLine == $token->getLine()) {
                        $interface        = false;
                        $interfaceEndLine = false;
                    }
                    break;
            }
        }
    }

    
    public function getLinesOfCode()
    {
        return $this->linesOfCode;
    }

    
    public function rewind()
    {
        $this->position = 0;
    }

    
    public function valid()
    {
        return isset($this->tokens[$this->position]);
    }

    
    public function key()
    {
        return $this->position;
    }

    
    public function current()
    {
        return $this->tokens[$this->position];
    }

    
    public function next()
    {
        $this->position++;
    }

    
    public function offsetExists($offset)
    {
        return isset($this->tokens[$offset]);
    }

    
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException(
                sprintf(
                    'No token at position "%s"',
                    $offset
                )
            );
        }

        return $this->tokens[$offset];
    }

    
    public function offsetSet($offset, $value)
    {
        $this->tokens[$offset] = $value;
    }

    
    public function offsetUnset($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException(
                sprintf(
                    'No token at position "%s"',
                    $offset
                )
            );
        }

        unset($this->tokens[$offset]);
    }

    
    public function seek($position)
    {
        $this->position = $position;

        if (!$this->valid()) {
            throw new OutOfBoundsException(
                sprintf(
                    'No token at position "%s"',
                    $this->position
                )
            );
        }
    }

    
    private function addFunctionToMap($name, $startLine, $endLine)
    {
        for ($line = $startLine; $line <= $endLine; $line++) {
            $this->lineToFunctionMap[$line] = $name;
        }
    }
}
