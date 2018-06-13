<?php
/*
 * This file is part of the php-code-coverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\CodeCoverage;

use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Driver\Xdebug;
use SebastianBergmann\CodeCoverage\Driver\HHVM;
use SebastianBergmann\CodeCoverage\Driver\PHPDBG;
use SebastianBergmann\CodeCoverage\Node\Builder;
use SebastianBergmann\CodeCoverage\Node\Directory;
use SebastianBergmann\CodeUnitReverseLookup\Wizard;
use SebastianBergmann\Environment\Runtime;


class CodeCoverage
{
    
    private $driver;

    
    private $filter;

    
    private $wizard;

    
    private $cacheTokens = false;

    
    private $checkForUnintentionallyCoveredCode = false;

    
    private $forceCoversAnnotation = false;

    
    private $checkForUnexecutedCoveredCode = false;

    
    private $checkForMissingCoversAnnotation = false;

    
    private $addUncoveredFilesFromWhitelist = true;

    
    private $processUncoveredFilesFromWhitelist = false;

    
    private $ignoreDeprecatedCode = false;

    
    private $currentId;

    
    private $data = [];

    
    private $ignoredLines = [];

    
    private $disableIgnoredLines = false;

    
    private $tests = [];

    
    private $unintentionallyCoveredSubclassesWhitelist = [];

    
    private $isInitialized = false;

    
    private $shouldCheckForDeadAndUnused = true;

    
    public function __construct(Driver $driver = null, Filter $filter = null)
    {
        if ($driver === null) {
            $driver = $this->selectDriver();
        }

        if ($filter === null) {
            $filter = new Filter;
        }

        $this->driver = $driver;
        $this->filter = $filter;

        $this->wizard = new Wizard;
    }

    
    public function getReport()
    {
        $builder = new Builder;

        return $builder->build($this);
    }

    
    public function clear()
    {
        $this->isInitialized = false;
        $this->currentId     = null;
        $this->data          = [];
        $this->tests         = [];
    }

    
    public function filter()
    {
        return $this->filter;
    }

    
    public function getData($raw = false)
    {
        if (!$raw && $this->addUncoveredFilesFromWhitelist) {
            $this->addUncoveredFilesFromWhitelist();
        }

        return $this->data;
    }

    
    public function setData(array $data)
    {
        $this->data = $data;
    }

    
    public function getTests()
    {
        return $this->tests;
    }

    
    public function setTests(array $tests)
    {
        $this->tests = $tests;
    }

    
    public function start($id, $clear = false)
    {
        if (!is_bool($clear)) {
            throw InvalidArgumentException::create(
                1,
                'boolean'
            );
        }

        if ($clear) {
            $this->clear();
        }

        if ($this->isInitialized === false) {
            $this->initializeData();
        }

        $this->currentId = $id;

        $this->driver->start($this->shouldCheckForDeadAndUnused);
    }

    
    public function stop($append = true, $linesToBeCovered = [], array $linesToBeUsed = [])
    {
        if (!is_bool($append)) {
            throw InvalidArgumentException::create(
                1,
                'boolean'
            );
        }

        if (!is_array($linesToBeCovered) && $linesToBeCovered !== false) {
            throw InvalidArgumentException::create(
                2,
                'array or false'
            );
        }

        $data = $this->driver->stop();
        $this->append($data, null, $append, $linesToBeCovered, $linesToBeUsed);

        $this->currentId = null;

        return $data;
    }

    
    public function append(array $data, $id = null, $append = true, $linesToBeCovered = [], array $linesToBeUsed = [])
    {
        if ($id === null) {
            $id = $this->currentId;
        }

        if ($id === null) {
            throw new RuntimeException;
        }

        $this->applyListsFilter($data);
        $this->applyIgnoredLinesFilter($data);
        $this->initializeFilesThatAreSeenTheFirstTime($data);

        if (!$append) {
            return;
        }

        if ($id != 'UNCOVERED_FILES_FROM_WHITELIST') {
            $this->applyCoversAnnotationFilter(
                $data,
                $linesToBeCovered,
                $linesToBeUsed
            );
        }

        if (empty($data)) {
            return;
        }

        $size   = 'unknown';
        $status = null;

        if ($id instanceof \PHPUnit_Framework_TestCase) {
            $_size = $id->getSize();

            if ($_size == \PHPUnit_Util_Test::SMALL) {
                $size = 'small';
            } elseif ($_size == \PHPUnit_Util_Test::MEDIUM) {
                $size = 'medium';
            } elseif ($_size == \PHPUnit_Util_Test::LARGE) {
                $size = 'large';
            }

            $status = $id->getStatus();
            $id     = get_class($id) . '::' . $id->getName();
        } elseif ($id instanceof \PHPUnit_Extensions_PhptTestCase) {
            $size = 'large';
            $id   = $id->getName();
        }

        $this->tests[$id] = ['size' => $size, 'status' => $status];

        foreach ($data as $file => $lines) {
            if (!$this->filter->isFile($file)) {
                continue;
            }

            foreach ($lines as $k => $v) {
                if ($v == Driver::LINE_EXECUTED) {
                    if (empty($this->data[$file][$k]) || !in_array($id, $this->data[$file][$k])) {
                        $this->data[$file][$k][] = $id;
                    }
                }
            }
        }
    }

    
    public function merge(CodeCoverage $that)
    {
        $this->filter->setWhitelistedFiles(
            array_merge($this->filter->getWhitelistedFiles(), $that->filter()->getWhitelistedFiles())
        );

        foreach ($that->data as $file => $lines) {
            if (!isset($this->data[$file])) {
                if (!$this->filter->isFiltered($file)) {
                    $this->data[$file] = $lines;
                }

                continue;
            }

            foreach ($lines as $line => $data) {
                if ($data !== null) {
                    if (!isset($this->data[$file][$line])) {
                        $this->data[$file][$line] = $data;
                    } else {
                        $this->data[$file][$line] = array_unique(
                            array_merge($this->data[$file][$line], $data)
                        );
                    }
                }
            }
        }

        $this->tests = array_merge($this->tests, $that->getTests());
    }

    
    public function setCacheTokens($flag)
    {
        if (!is_bool($flag)) {
            throw InvalidArgumentException::create(
                1,
                'boolean'
            );
        }

        $this->cacheTokens = $flag;
    }

    
    public function getCacheTokens()
    {
        return $this->cacheTokens;
    }

    
    public function setCheckForUnintentionallyCoveredCode($flag)
    {
        if (!is_bool($flag)) {
            throw InvalidArgumentException::create(
                1,
                'boolean'
            );
        }

        $this->checkForUnintentionallyCoveredCode = $flag;
    }

    
    public function setForceCoversAnnotation($flag)
    {
        if (!is_bool($flag)) {
            throw InvalidArgumentException::create(
                1,
                'boolean'
            );
        }

        $this->forceCoversAnnotation = $flag;
    }

    
    public function setCheckForMissingCoversAnnotation($flag)
    {
        if (!is_bool($flag)) {
            throw InvalidArgumentException::create(
                1,
                'boolean'
            );
        }

        $this->checkForMissingCoversAnnotation = $flag;
    }

    
    public function setCheckForUnexecutedCoveredCode($flag)
    {
        if (!is_bool($flag)) {
            throw InvalidArgumentException::create(
                1,
                'boolean'
            );
        }

        $this->checkForUnexecutedCoveredCode = $flag;
    }

    
    public function setMapTestClassNameToCoveredClassName($flag)
    {
    }

    
    public function setAddUncoveredFilesFromWhitelist($flag)
    {
        if (!is_bool($flag)) {
            throw InvalidArgumentException::create(
                1,
                'boolean'
            );
        }

        $this->addUncoveredFilesFromWhitelist = $flag;
    }

    
    public function setProcessUncoveredFilesFromWhitelist($flag)
    {
        if (!is_bool($flag)) {
            throw InvalidArgumentException::create(
                1,
                'boolean'
            );
        }

        $this->processUncoveredFilesFromWhitelist = $flag;
    }

    
    public function setDisableIgnoredLines($flag)
    {
        if (!is_bool($flag)) {
            throw InvalidArgumentException::create(
                1,
                'boolean'
            );
        }

        $this->disableIgnoredLines = $flag;
    }

    
    public function setIgnoreDeprecatedCode($flag)
    {
        if (!is_bool($flag)) {
            throw InvalidArgumentException::create(
                1,
                'boolean'
            );
        }

        $this->ignoreDeprecatedCode = $flag;
    }

    
    public function setUnintentionallyCoveredSubclassesWhitelist(array $whitelist)
    {
        $this->unintentionallyCoveredSubclassesWhitelist = $whitelist;
    }

    
    private function applyCoversAnnotationFilter(array &$data, $linesToBeCovered, array $linesToBeUsed)
    {
        if ($linesToBeCovered === false ||
            ($this->forceCoversAnnotation && empty($linesToBeCovered))) {
            if ($this->checkForMissingCoversAnnotation) {
                throw new MissingCoversAnnotationException;
            }

            $data = [];

            return;
        }

        if (empty($linesToBeCovered)) {
            return;
        }

        if ($this->checkForUnintentionallyCoveredCode &&
            (!$this->currentId instanceof \PHPUnit_Framework_TestCase ||
            (!$this->currentId->isMedium() && !$this->currentId->isLarge()))) {
            $this->performUnintentionallyCoveredCodeCheck(
                $data,
                $linesToBeCovered,
                $linesToBeUsed
            );
        }

        if ($this->checkForUnexecutedCoveredCode) {
            $this->performUnexecutedCoveredCodeCheck($data, $linesToBeCovered, $linesToBeUsed);
        }

        $data = array_intersect_key($data, $linesToBeCovered);

        foreach (array_keys($data) as $filename) {
            $_linesToBeCovered = array_flip($linesToBeCovered[$filename]);

            $data[$filename] = array_intersect_key(
                $data[$filename],
                $_linesToBeCovered
            );
        }
    }

    
    private function applyListsFilter(array &$data)
    {
        foreach (array_keys($data) as $filename) {
            if ($this->filter->isFiltered($filename)) {
                unset($data[$filename]);
            }
        }
    }

    
    private function applyIgnoredLinesFilter(array &$data)
    {
        foreach (array_keys($data) as $filename) {
            if (!$this->filter->isFile($filename)) {
                continue;
            }

            foreach ($this->getLinesToBeIgnored($filename) as $line) {
                unset($data[$filename][$line]);
            }
        }
    }

    
    private function initializeFilesThatAreSeenTheFirstTime(array $data)
    {
        foreach ($data as $file => $lines) {
            if ($this->filter->isFile($file) && !isset($this->data[$file])) {
                $this->data[$file] = [];

                foreach ($lines as $k => $v) {
                    $this->data[$file][$k] = $v == -2 ? null : [];
                }
            }
        }
    }

    
    private function addUncoveredFilesFromWhitelist()
    {
        $data           = [];
        $uncoveredFiles = array_diff(
            $this->filter->getWhitelist(),
            array_keys($this->data)
        );

        foreach ($uncoveredFiles as $uncoveredFile) {
            if (!file_exists($uncoveredFile)) {
                continue;
            }

            if (!$this->processUncoveredFilesFromWhitelist) {
                $data[$uncoveredFile] = [];

                $lines = count(file($uncoveredFile));

                for ($i = 1; $i <= $lines; $i++) {
                    $data[$uncoveredFile][$i] = Driver::LINE_NOT_EXECUTED;
                }
            }
        }

        $this->append($data, 'UNCOVERED_FILES_FROM_WHITELIST');
    }

    
    private function getLinesToBeIgnored($filename)
    {
        if (!is_string($filename)) {
            throw InvalidArgumentException::create(
                1,
                'string'
            );
        }

        if (!isset($this->ignoredLines[$filename])) {
            $this->ignoredLines[$filename] = [];

            if ($this->disableIgnoredLines) {
                return $this->ignoredLines[$filename];
            }

            $ignore   = false;
            $stop     = false;
            $lines    = file($filename);
            $numLines = count($lines);

            foreach ($lines as $index => $line) {
                if (!trim($line)) {
                    $this->ignoredLines[$filename][] = $index + 1;
                }
            }

            if ($this->cacheTokens) {
                $tokens = \PHP_Token_Stream_CachingFactory::get($filename);
            } else {
                $tokens = new \PHP_Token_Stream($filename);
            }

            $classes = array_merge($tokens->getClasses(), $tokens->getTraits());
            $tokens  = $tokens->tokens();

            foreach ($tokens as $token) {
                switch (get_class($token)) {
                    case 'PHP_Token_COMMENT':
                    case 'PHP_Token_DOC_COMMENT':
                        $_token = trim($token);
                        $_line  = trim($lines[$token->getLine() - 1]);

                        if ($_token == '// @codeCoverageIgnore' ||
                            $_token == '//@codeCoverageIgnore') {
                            $ignore = true;
                            $stop   = true;
                        } elseif ($_token == '// @codeCoverageIgnoreStart' ||
                            $_token == '//@codeCoverageIgnoreStart') {
                            $ignore = true;
                        } elseif ($_token == '// @codeCoverageIgnoreEnd' ||
                            $_token == '//@codeCoverageIgnoreEnd') {
                            $stop = true;
                        }

                        if (!$ignore) {
                            $start = $token->getLine();
                            $end   = $start + substr_count($token, "\n");

                            // Do not ignore the first line when there is a token
                            // before the comment
                            if (0 !== strpos($_token, $_line)) {
                                $start++;
                            }

                            for ($i = $start; $i < $end; $i++) {
                                $this->ignoredLines[$filename][] = $i;
                            }

                            // A DOC_COMMENT token or a COMMENT token starting with "/*"
                            // does not contain the final \n character in its text
                            if (isset($lines[$i-1]) && 0 === strpos($_token, '/*') && '*/' === substr(trim($lines[$i-1]), -2)) {
                                $this->ignoredLines[$filename][] = $i;
                            }
                        }
                        break;

                    case 'PHP_Token_INTERFACE':
                    case 'PHP_Token_TRAIT':
                    case 'PHP_Token_CLASS':
                    case 'PHP_Token_FUNCTION':
                        /* @var \PHP_Token_Interface $token */

                        $docblock = $token->getDocblock();

                        $this->ignoredLines[$filename][] = $token->getLine();

                        if (strpos($docblock, '@codeCoverageIgnore') || ($this->ignoreDeprecatedCode && strpos($docblock, '@deprecated'))) {
                            $endLine = $token->getEndLine();

                            for ($i = $token->getLine(); $i <= $endLine; $i++) {
                                $this->ignoredLines[$filename][] = $i;
                            }
                        } elseif ($token instanceof \PHP_Token_INTERFACE ||
                            $token instanceof \PHP_Token_TRAIT ||
                            $token instanceof \PHP_Token_CLASS) {
                            if (empty($classes[$token->getName()]['methods'])) {
                                for ($i = $token->getLine();
                                     $i <= $token->getEndLine();
                                     $i++) {
                                    $this->ignoredLines[$filename][] = $i;
                                }
                            } else {
                                $firstMethod = array_shift(
                                    $classes[$token->getName()]['methods']
                                );

                                do {
                                    $lastMethod = array_pop(
                                        $classes[$token->getName()]['methods']
                                    );
                                } while ($lastMethod !== null &&
                                    substr($lastMethod['signature'], 0, 18) == 'anonymous function');

                                if ($lastMethod === null) {
                                    $lastMethod = $firstMethod;
                                }

                                for ($i = $token->getLine();
                                     $i < $firstMethod['startLine'];
                                     $i++) {
                                    $this->ignoredLines[$filename][] = $i;
                                }

                                for ($i = $token->getEndLine();
                                     $i > $lastMethod['endLine'];
                                     $i--) {
                                    $this->ignoredLines[$filename][] = $i;
                                }
                            }
                        }
                        break;

                    case 'PHP_Token_NAMESPACE':
                        $this->ignoredLines[$filename][] = $token->getEndLine();

                    // Intentional fallthrough
                    case 'PHP_Token_DECLARE':
                    case 'PHP_Token_OPEN_TAG':
                    case 'PHP_Token_CLOSE_TAG':
                    case 'PHP_Token_USE':
                        $this->ignoredLines[$filename][] = $token->getLine();
                        break;
                }

                if ($ignore) {
                    $this->ignoredLines[$filename][] = $token->getLine();

                    if ($stop) {
                        $ignore = false;
                        $stop   = false;
                    }
                }
            }

            $this->ignoredLines[$filename][] = $numLines + 1;

            $this->ignoredLines[$filename] = array_unique(
                $this->ignoredLines[$filename]
            );

            sort($this->ignoredLines[$filename]);
        }

        return $this->ignoredLines[$filename];
    }

    
    private function performUnintentionallyCoveredCodeCheck(array &$data, array $linesToBeCovered, array $linesToBeUsed)
    {
        $allowedLines = $this->getAllowedLines(
            $linesToBeCovered,
            $linesToBeUsed
        );

        $unintentionallyCoveredUnits = [];

        foreach ($data as $file => $_data) {
            foreach ($_data as $line => $flag) {
                if ($flag == 1 && !isset($allowedLines[$file][$line])) {
                    $unintentionallyCoveredUnits[] = $this->wizard->lookup($file, $line);
                }
            }
        }

        $unintentionallyCoveredUnits = $this->processUnintentionallyCoveredUnits($unintentionallyCoveredUnits);

        if (!empty($unintentionallyCoveredUnits)) {
            throw new UnintentionallyCoveredCodeException(
                $unintentionallyCoveredUnits
            );
        }
    }

    
    private function performUnexecutedCoveredCodeCheck(array &$data, array $linesToBeCovered, array $linesToBeUsed)
    {
        $expectedLines = $this->getAllowedLines(
            $linesToBeCovered,
            $linesToBeUsed
        );

        foreach ($data as $file => $_data) {
            foreach (array_keys($_data) as $line) {
                if (!isset($expectedLines[$file][$line])) {
                    continue;
                }

                unset($expectedLines[$file][$line]);
            }
        }

        $message = '';

        foreach ($expectedLines as $file => $lines) {
            if (empty($lines)) {
                continue;
            }

            foreach (array_keys($lines) as $line) {
                $message .= sprintf('- %s:%d' . PHP_EOL, $file, $line);
            }
        }

        if (!empty($message)) {
            throw new CoveredCodeNotExecutedException($message);
        }
    }

    
    private function getAllowedLines(array $linesToBeCovered, array $linesToBeUsed)
    {
        $allowedLines = [];

        foreach (array_keys($linesToBeCovered) as $file) {
            if (!isset($allowedLines[$file])) {
                $allowedLines[$file] = [];
            }

            $allowedLines[$file] = array_merge(
                $allowedLines[$file],
                $linesToBeCovered[$file]
            );
        }

        foreach (array_keys($linesToBeUsed) as $file) {
            if (!isset($allowedLines[$file])) {
                $allowedLines[$file] = [];
            }

            $allowedLines[$file] = array_merge(
                $allowedLines[$file],
                $linesToBeUsed[$file]
            );
        }

        foreach (array_keys($allowedLines) as $file) {
            $allowedLines[$file] = array_flip(
                array_unique($allowedLines[$file])
            );
        }

        return $allowedLines;
    }

    
    private function selectDriver()
    {
        $runtime = new Runtime;

        if (!$runtime->canCollectCodeCoverage()) {
            throw new RuntimeException('No code coverage driver available');
        }

        if ($runtime->isHHVM()) {
            return new HHVM;
        } elseif ($runtime->isPHPDBG()) {
            return new PHPDBG;
        } else {
            return new Xdebug;
        }
    }

    
    private function processUnintentionallyCoveredUnits(array $unintentionallyCoveredUnits)
    {
        $unintentionallyCoveredUnits = array_unique($unintentionallyCoveredUnits);
        sort($unintentionallyCoveredUnits);

        foreach (array_keys($unintentionallyCoveredUnits) as $k => $v) {
            $unit = explode('::', $unintentionallyCoveredUnits[$k]);

            if (count($unit) != 2) {
                continue;
            }

            $class = new \ReflectionClass($unit[0]);

            foreach ($this->unintentionallyCoveredSubclassesWhitelist as $whitelisted) {
                if ($class->isSubclassOf($whitelisted)) {
                    unset($unintentionallyCoveredUnits[$k]);
                    break;
                }
            }
        }

        return array_values($unintentionallyCoveredUnits);
    }

    
    protected function initializeData()
    {
        $this->isInitialized = true;

        if ($this->processUncoveredFilesFromWhitelist) {
            $this->shouldCheckForDeadAndUnused = false;

            $this->driver->start(true);

            foreach ($this->filter->getWhitelist() as $file) {
                if ($this->filter->isFile($file)) {
                    include_once($file);
                }
            }

            $data     = [];
            $coverage = $this->driver->stop();

            foreach ($coverage as $file => $fileCoverage) {
                if ($this->filter->isFiltered($file)) {
                    continue;
                }

                foreach (array_keys($fileCoverage) as $key) {
                    if ($fileCoverage[$key] == Driver::LINE_EXECUTED) {
                        $fileCoverage[$key] = Driver::LINE_NOT_EXECUTED;
                    }
                }

                $data[$file] = $fileCoverage;
            }

            $this->append($data, 'UNCOVERED_FILES_FROM_WHITELIST');
        }
    }
}
