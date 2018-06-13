<?php


namespace aabc\console\controllers;

use Aabc;
use aabc\console\Exception;
use aabc\console\Controller;
use aabc\helpers\Console;
use aabc\helpers\FileHelper;
use aabc\helpers\VarDumper;
use aabc\web\AssetBundle;


class AssetController extends Controller
{
    
    public $defaultAction = 'compress';
    
    public $bundles = [];
    
    public $targets = [];
    
    public $jsCompressor = 'java -jar compiler.jar --js {from} --js_output_file {to}';
    
    public $cssCompressor = 'java -jar yuicompressor.jar --type css {from} -o {to}';
    
    public $deleteSource = false;

    
    private $_assetManager = [];


    
    public function getAssetManager()
    {
        if (!is_object($this->_assetManager)) {
            $options = $this->_assetManager;
            if (!isset($options['class'])) {
                $options['class'] = 'aabc\\web\\AssetManager';
            }
            if (!isset($options['basePath'])) {
                throw new Exception("Please specify 'basePath' for the 'assetManager' option.");
            }
            if (!isset($options['baseUrl'])) {
                throw new Exception("Please specify 'baseUrl' for the 'assetManager' option.");
            }

            if (!isset($options['forceCopy'])) {
                $options['forceCopy'] = true;
            }

            $this->_assetManager = Aabc::createObject($options);
        }

        return $this->_assetManager;
    }

    
    public function setAssetManager($assetManager)
    {
        if (is_scalar($assetManager)) {
            throw new Exception('"' . get_class($this) . '::assetManager" should be either object or array - "' . gettype($assetManager) . '" given.');
        }
        $this->_assetManager = $assetManager;
    }

    
    public function actionCompress($configFile, $bundleFile)
    {
        $this->loadConfiguration($configFile);
        $bundles = $this->loadBundles($this->bundles);
        $targets = $this->loadTargets($this->targets, $bundles);
        foreach ($targets as $name => $target) {
            $this->stdout("Creating output bundle '{$name}':\n");
            if (!empty($target->js)) {
                $this->buildTarget($target, 'js', $bundles);
            }
            if (!empty($target->css)) {
                $this->buildTarget($target, 'css', $bundles);
            }
            $this->stdout("\n");
        }

        $targets = $this->adjustDependency($targets, $bundles);
        $this->saveTargets($targets, $bundleFile);

        if ($this->deleteSource) {
            $this->deletePublishedAssets($bundles);
        }
    }

    
    protected function loadConfiguration($configFile)
    {
        $this->stdout("Loading configuration from '{$configFile}'...\n");
        foreach (require($configFile) as $name => $value) {
            if (property_exists($this, $name) || $this->canSetProperty($name)) {
                $this->$name = $value;
            } else {
                throw new Exception("Unknown configuration option: $name");
            }
        }

        $this->getAssetManager(); // check if asset manager configuration is correct
    }

    
    protected function loadBundles($bundles)
    {
        $this->stdout("Collecting source bundles information...\n");

        $am = $this->getAssetManager();
        $result = [];
        foreach ($bundles as $name) {
            $result[$name] = $am->getBundle($name);
        }
        foreach ($result as $bundle) {
            $this->loadDependency($bundle, $result);
        }

        return $result;
    }

    
    protected function loadDependency($bundle, &$result)
    {
        $am = $this->getAssetManager();
        foreach ($bundle->depends as $name) {
            if (!isset($result[$name])) {
                $dependencyBundle = $am->getBundle($name);
                $result[$name] = false;
                $this->loadDependency($dependencyBundle, $result);
                $result[$name] = $dependencyBundle;
            } elseif ($result[$name] === false) {
                throw new Exception("A circular dependency is detected for bundle '{$name}': " . $this->composeCircularDependencyTrace($name, $result) . '.');
            }
        }
    }

    
    protected function loadTargets($targets, $bundles)
    {
        // build the dependency order of bundles
        $registered = [];
        foreach ($bundles as $name => $bundle) {
            $this->registerBundle($bundles, $name, $registered);
        }
        $bundleOrders = array_combine(array_keys($registered), range(0, count($bundles) - 1));

        // fill up the target which has empty 'depends'.
        $referenced = [];
        foreach ($targets as $name => $target) {
            if (empty($target['depends'])) {
                if (!isset($all)) {
                    $all = $name;
                } else {
                    throw new Exception("Only one target can have empty 'depends' option. Found two now: $all, $name");
                }
            } else {
                foreach ($target['depends'] as $bundle) {
                    if (!isset($referenced[$bundle])) {
                        $referenced[$bundle] = $name;
                    } else {
                        throw new Exception("Target '{$referenced[$bundle]}' and '$name' cannot contain the bundle '$bundle' at the same time.");
                    }
                }
            }
        }
        if (isset($all)) {
            $targets[$all]['depends'] = array_diff(array_keys($registered), array_keys($referenced));
        }

        // adjust the 'depends' order for each target according to the dependency order of bundles
        // create an AssetBundle object for each target
        foreach ($targets as $name => $target) {
            if (!isset($target['basePath'])) {
                throw new Exception("Please specify 'basePath' for the '$name' target.");
            }
            if (!isset($target['baseUrl'])) {
                throw new Exception("Please specify 'baseUrl' for the '$name' target.");
            }
            usort($target['depends'], function ($a, $b) use ($bundleOrders) {
                if ($bundleOrders[$a] == $bundleOrders[$b]) {
                    return 0;
                } else {
                    return $bundleOrders[$a] > $bundleOrders[$b] ? 1 : -1;
                }
            });
            if (!isset($target['class'])) {
                $target['class'] = $name;
            }
            $targets[$name] = Aabc::createObject($target);
        }

        return $targets;
    }

    
    protected function buildTarget($target, $type, $bundles)
    {
        $inputFiles = [];
        foreach ($target->depends as $name) {
            if (isset($bundles[$name])) {
                if (!$this->isBundleExternal($bundles[$name])) {
                    foreach ($bundles[$name]->$type as $file) {
                        if (is_array($file)) {
                            $inputFiles[] = $bundles[$name]->basePath . '/' . $file[0];
                        } else {
                            $inputFiles[] = $bundles[$name]->basePath . '/' . $file;
                        }
                    }
                }
            } else {
                throw new Exception("Unknown bundle: '{$name}'");
            }
        }

        if (empty($inputFiles)) {
            $target->$type = [];
        } else {
            FileHelper::createDirectory($target->basePath, $this->getAssetManager()->dirMode);
            $tempFile = $target->basePath . '/' . strtr($target->$type, ['{hash}' => 'temp']);

            if ($type === 'js') {
                $this->compressJsFiles($inputFiles, $tempFile);
            } else {
                $this->compressCssFiles($inputFiles, $tempFile);
            }

            $targetFile = strtr($target->$type, ['{hash}' => md5_file($tempFile)]);
            $outputFile = $target->basePath . '/' . $targetFile;
            rename($tempFile, $outputFile);
            $target->$type = [$targetFile];
        }
    }

    
    protected function adjustDependency($targets, $bundles)
    {
        $this->stdout("Creating new bundle configuration...\n");

        $map = [];
        foreach ($targets as $name => $target) {
            foreach ($target->depends as $bundle) {
                $map[$bundle] = $name;
            }
        }

        foreach ($targets as $name => $target) {
            $depends = [];
            foreach ($target->depends as $bn) {
                foreach ($bundles[$bn]->depends as $bundle) {
                    $depends[$map[$bundle]] = true;
                }
            }
            unset($depends[$name]);
            $target->depends = array_keys($depends);
        }

        // detect possible circular dependencies
        foreach ($targets as $name => $target) {
            $registered = [];
            $this->registerBundle($targets, $name, $registered);
        }

        foreach ($map as $bundle => $target) {
            $sourceBundle = $bundles[$bundle];
            $depends = $sourceBundle->depends;
            if (!$this->isBundleExternal($sourceBundle)) {
                $depends[] = $target;
            }
            $targetBundle = clone $sourceBundle;
            $targetBundle->depends = $depends;
            $targets[$bundle] = $targetBundle;
        }

        return $targets;
    }

    
    protected function registerBundle($bundles, $name, &$registered)
    {
        if (!isset($registered[$name])) {
            $registered[$name] = false;
            $bundle = $bundles[$name];
            foreach ($bundle->depends as $depend) {
                $this->registerBundle($bundles, $depend, $registered);
            }
            unset($registered[$name]);
            $registered[$name] = $bundle;
        } elseif ($registered[$name] === false) {
            throw new Exception("A circular dependency is detected for target '{$name}': " . $this->composeCircularDependencyTrace($name, $registered) . '.');
        }
    }

    
    protected function saveTargets($targets, $bundleFile)
    {
        $array = [];
        foreach ($targets as $name => $target) {
            if (isset($this->targets[$name])) {
                $array[$name] = array_merge($this->targets[$name], [
                    'class' => get_class($target),
                    'sourcePath' => null,
                    'basePath' => $this->targets[$name]['basePath'],
                    'baseUrl' => $this->targets[$name]['baseUrl'],
                    'js' => $target->js,
                    'css' => $target->css,
                    'depends' => [],
                ]);
            } else {
                if ($this->isBundleExternal($target)) {
                    $array[$name] = $this->composeBundleConfig($target);
                } else {
                    $array[$name] = [
                        'sourcePath' => null,
                        'js' => [],
                        'css' => [],
                        'depends' => $target->depends,
                    ];
                }
            }
        }
        $array = VarDumper::export($array);
        $version = date('Y-m-d H:i:s', time());
        $bundleFileContent = <<<EOD
<?php

return {$array};
EOD;
        if (!file_put_contents($bundleFile, $bundleFileContent)) {
            throw new Exception("Unable to write output bundle configuration at '{$bundleFile}'.");
        }
        $this->stdout("Output bundle configuration created at '{$bundleFile}'.\n", Console::FG_GREEN);
    }

    
    protected function compressJsFiles($inputFiles, $outputFile)
    {
        if (empty($inputFiles)) {
            return;
        }
        $this->stdout("  Compressing JavaScript files...\n");
        if (is_string($this->jsCompressor)) {
            $tmpFile = $outputFile . '.tmp';
            $this->combineJsFiles($inputFiles, $tmpFile);
            $this->stdout(shell_exec(strtr($this->jsCompressor, [
                '{from}' => escapeshellarg($tmpFile),
                '{to}' => escapeshellarg($outputFile),
            ])));
            @unlink($tmpFile);
        } else {
            call_user_func($this->jsCompressor, $this, $inputFiles, $outputFile);
        }
        if (!file_exists($outputFile)) {
            throw new Exception("Unable to compress JavaScript files into '{$outputFile}'.");
        }
        $this->stdout("  JavaScript files compressed into '{$outputFile}'.\n");
    }

    
    protected function compressCssFiles($inputFiles, $outputFile)
    {
        if (empty($inputFiles)) {
            return;
        }
        $this->stdout("  Compressing CSS files...\n");
        if (is_string($this->cssCompressor)) {
            $tmpFile = $outputFile . '.tmp';
            $this->combineCssFiles($inputFiles, $tmpFile);
            $this->stdout(shell_exec(strtr($this->cssCompressor, [
                '{from}' => escapeshellarg($tmpFile),
                '{to}' => escapeshellarg($outputFile),
            ])));
            @unlink($tmpFile);
        } else {
            call_user_func($this->cssCompressor, $this, $inputFiles, $outputFile);
        }
        if (!file_exists($outputFile)) {
            throw new Exception("Unable to compress CSS files into '{$outputFile}'.");
        }
        $this->stdout("  CSS files compressed into '{$outputFile}'.\n");
    }

    
    public function combineJsFiles($inputFiles, $outputFile)
    {
        $content = '';
        foreach ($inputFiles as $file) {
            $content .= "\n"
                . file_get_contents($file)
                . "\n";
        }
        if (!file_put_contents($outputFile, $content)) {
            throw new Exception("Unable to write output JavaScript file '{$outputFile}'.");
        }
    }

    
    public function combineCssFiles($inputFiles, $outputFile)
    {
        $content = '';
        $outputFilePath = dirname($this->findRealPath($outputFile));
        foreach ($inputFiles as $file) {
            $content .= "\n"
                . $this->adjustCssUrl(file_get_contents($file), dirname($this->findRealPath($file)), $outputFilePath)
                . "\n";
        }
        if (!file_put_contents($outputFile, $content)) {
            throw new Exception("Unable to write output CSS file '{$outputFile}'.");
        }
    }

    
    protected function adjustCssUrl($cssContent, $inputFilePath, $outputFilePath)
    {
        $inputFilePath = str_replace('\\', '/', $inputFilePath);
        $outputFilePath = str_replace('\\', '/', $outputFilePath);

        $sharedPathParts = [];
        $inputFilePathParts = explode('/', $inputFilePath);
        $inputFilePathPartsCount = count($inputFilePathParts);
        $outputFilePathParts = explode('/', $outputFilePath);
        $outputFilePathPartsCount = count($outputFilePathParts);
        for ($i =0; $i < $inputFilePathPartsCount && $i < $outputFilePathPartsCount; $i++) {
            if ($inputFilePathParts[$i] == $outputFilePathParts[$i]) {
                $sharedPathParts[] = $inputFilePathParts[$i];
            } else {
                break;
            }
        }
        $sharedPath = implode('/', $sharedPathParts);

        $inputFileRelativePath = trim(str_replace($sharedPath, '', $inputFilePath), '/');
        $outputFileRelativePath = trim(str_replace($sharedPath, '', $outputFilePath), '/');
        if (empty($inputFileRelativePath)) {
            $inputFileRelativePathParts = [];
        } else {
            $inputFileRelativePathParts = explode('/', $inputFileRelativePath);
        }
        if (empty($outputFileRelativePath)) {
            $outputFileRelativePathParts = [];
        } else {
            $outputFileRelativePathParts = explode('/', $outputFileRelativePath);
        }

        $callback = function ($matches) use ($inputFileRelativePathParts, $outputFileRelativePathParts) {
            $fullMatch = $matches[0];
            $inputUrl = $matches[1];

            if (strpos($inputUrl, '/') === 0 || strpos($inputUrl, '#') === 0 || preg_match('/^https?:\/\//i', $inputUrl) || preg_match('/^data:/i', $inputUrl)) {
                return $fullMatch;
            }
            if ($inputFileRelativePathParts === $outputFileRelativePathParts) {
                return $fullMatch;
            }

            if (empty($outputFileRelativePathParts)) {
                $outputUrlParts = [];
            } else {
                $outputUrlParts = array_fill(0, count($outputFileRelativePathParts), '..');
            }
            $outputUrlParts = array_merge($outputUrlParts, $inputFileRelativePathParts);

            if (strpos($inputUrl, '/') !== false) {
                $inputUrlParts = explode('/', $inputUrl);
                foreach ($inputUrlParts as $key => $inputUrlPart) {
                    if ($inputUrlPart === '..') {
                        array_pop($outputUrlParts);
                        unset($inputUrlParts[$key]);
                    }
                }
                $outputUrlParts[] = implode('/', $inputUrlParts);
            } else {
                $outputUrlParts[] = $inputUrl;
            }
            $outputUrl = implode('/', $outputUrlParts);

            return str_replace($inputUrl, $outputUrl, $fullMatch);
        };

        $cssContent = preg_replace_callback('/url\(["\']?([^)^"^\']*)["\']?\)/i', $callback, $cssContent);

        return $cssContent;
    }

    
    public function actionTemplate($configFile)
    {
        $jsCompressor = VarDumper::export($this->jsCompressor);
        $cssCompressor = VarDumper::export($this->cssCompressor);

        $template = <<<EOD
<?php


// In the console environment, some path aliases may not exist. Please define these:
// Aabc::setAlias('@webroot', __DIR__ . '/../web');
// Aabc::setAlias('@web', '/');

return [
    // Adjust command/callback for JavaScript files compressing:
    'jsCompressor' => {$jsCompressor},
    // Adjust command/callback for CSS files compressing:
    'cssCompressor' => {$cssCompressor},
    // Whether to delete asset source after compression:
    'deleteSource' => false,
    // The list of asset bundles to compress:
    'bundles' => [
        // 'app\assets\AppAsset',
        // 'aabc\web\AabcAsset',
        // 'aabc\web\JqueryAsset',
    ],
    // Asset bundle for compression output:
    'targets' => [
        'all' => [
            'class' => 'aabc\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
        ],
    ],
    // Asset manager configuration:
    'assetManager' => [
        //'basePath' => '@webroot/assets',
        //'baseUrl' => '@web/assets',
    ],
];
EOD;
        if (file_exists($configFile)) {
            if (!$this->confirm("File '{$configFile}' already exists. Do you wish to overwrite it?")) {
                return self::EXIT_CODE_NORMAL;
            }
        }
        if (!file_put_contents($configFile, $template)) {
            throw new Exception("Unable to write template file '{$configFile}'.");
        } else {
            $this->stdout("Configuration file template created at '{$configFile}'.\n\n", Console::FG_GREEN);
            return self::EXIT_CODE_NORMAL;
        }
    }

    
    private function findRealPath($path)
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $pathParts = explode(DIRECTORY_SEPARATOR, $path);

        $realPathParts = [];
        foreach ($pathParts as $pathPart) {
            if ($pathPart === '..') {
                array_pop($realPathParts);
            } else {
                $realPathParts[] = $pathPart;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $realPathParts);
    }

    
    private function isBundleExternal($bundle)
    {
        return (empty($bundle->sourcePath) && empty($bundle->basePath));
    }

    
    private function composeBundleConfig($bundle)
    {
        $config = Aabc::getObjectVars($bundle);
        $config['class'] = get_class($bundle);
        return $config;
    }

    
    private function composeCircularDependencyTrace($circularDependencyName, array $registered)
    {
        $dependencyTrace = [];
        $startFound = false;
        foreach ($registered as $name => $value) {
            if ($name === $circularDependencyName) {
                $startFound = true;
            }
            if ($startFound && $value === false) {
                $dependencyTrace[] = $name;
            }
        }
        $dependencyTrace[] = $circularDependencyName;
        return implode(' -> ', $dependencyTrace);
    }

    
    private function deletePublishedAssets($bundles)
    {
        $this->stdout("Deleting source files...\n");

        if ($this->getAssetManager()->linkAssets) {
            $this->stdout("`AssetManager::linkAssets` option is enabled. Deleting of source files canceled.\n", Console::FG_YELLOW);
            return;
        }

        foreach ($bundles as $bundle) {
            if ($bundle->sourcePath !== null) {
                foreach ($bundle->js as $jsFile) {
                    @unlink($bundle->basePath . DIRECTORY_SEPARATOR . $jsFile);
                }
                foreach ($bundle->css as $cssFile) {
                    @unlink($bundle->basePath . DIRECTORY_SEPARATOR . $cssFile);
                }
            }
        }

        $this->stdout("Source files deleted.\n", Console::FG_GREEN);
    }
}
