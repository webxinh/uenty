<?php


namespace aabc\composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Script\CommandEvent;
use Composer\Script\Event;
use Composer\Util\Filesystem;


class Installer extends LibraryInstaller
{
    const EXTRA_BOOTSTRAP = 'bootstrap';
    const EXTENSION_FILE = 'aabcsoft/extensions.php';


    
    public function supports($packageType)
    {
        return $packageType === 'aabc2-extension';
    }

    
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        // install the package the normal composer way
        parent::install($repo, $package);
        // add the package to aabcsoft/extensions.php
        $this->addPackage($package);
        // ensure the aabc2-dev package also provides Aabc.php in the same place as aabc2 does
        if ($package->getName() == 'aabcsoft/aabc2-dev') {
            $this->linkBaseAabcFiles();
        }
    }

    
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);
        $this->removePackage($initial);
        $this->addPackage($target);
        // ensure the aabc2-dev package also provides Aabc.php in the same place as aabc2 does
        if ($initial->getName() == 'aabcsoft/aabc2-dev') {
            $this->linkBaseAabcFiles();
        }
    }

    
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        // uninstall the package the normal composer way
        parent::uninstall($repo, $package);
        // remove the package from aabcsoft/extensions.php
        $this->removePackage($package);
        // remove links for Aabc.php
        if ($package->getName() == 'aabcsoft/aabc2-dev') {
            $this->removeBaseAabcFiles();
        }
    }

    protected function addPackage(PackageInterface $package)
    {
        $extension = [
            'name' => $package->getName(),
            'version' => $package->getVersion(),
        ];

        $alias = $this->generateDefaultAlias($package);
        if (!empty($alias)) {
            $extension['alias'] = $alias;
        }
        $extra = $package->getExtra();
        if (isset($extra[self::EXTRA_BOOTSTRAP])) {
            $extension['bootstrap'] = $extra[self::EXTRA_BOOTSTRAP];
        }

        $extensions = $this->loadExtensions();
        $extensions[$package->getName()] = $extension;
        $this->saveExtensions($extensions);
    }

    protected function generateDefaultAlias(PackageInterface $package)
    {
        $fs = new Filesystem;
        $vendorDir = $fs->normalizePath($this->vendorDir);
        $autoload = $package->getAutoload();

        $aliases = [];

        if (!empty($autoload['psr-0'])) {
            foreach ($autoload['psr-0'] as $name => $path) {
                $name = str_replace('\\', '/', trim($name, '\\'));
                if (!$fs->isAbsolutePath($path)) {
                    $path = $this->vendorDir . '/' . $package->getPrettyName() . '/' . $path;
                }
                $path = $fs->normalizePath($path);
                if (strpos($path . '/', $vendorDir . '/') === 0) {
                    $aliases["@$name"] = '<vendor-dir>' . substr($path, strlen($vendorDir)) . '/' . $name;
                } else {
                    $aliases["@$name"] = $path . '/' . $name;
                }
            }
        }

        if (!empty($autoload['psr-4'])) {
            foreach ($autoload['psr-4'] as $name => $path) {
                if (is_array($path)) {
                    // ignore psr-4 autoload specifications with multiple search paths
                    // we can not convert them into aliases as they are ambiguous
                    continue;
                }
                $name = str_replace('\\', '/', trim($name, '\\'));
                if (!$fs->isAbsolutePath($path)) {
                    $path = $this->vendorDir . '/' . $package->getPrettyName() . '/' . $path;
                }
                $path = $fs->normalizePath($path);
                if (strpos($path . '/', $vendorDir . '/') === 0) {
                    $aliases["@$name"] = '<vendor-dir>' . substr($path, strlen($vendorDir));
                } else {
                    $aliases["@$name"] = $path;
                }
            }
        }

        return $aliases;
    }

    protected function removePackage(PackageInterface $package)
    {
        $packages = $this->loadExtensions();
        unset($packages[$package->getName()]);
        $this->saveExtensions($packages);
    }

    protected function loadExtensions()
    {
        $file = $this->vendorDir . '/' . static::EXTENSION_FILE;
        if (!is_file($file)) {
            return [];
        }
        // invalidate opcache of extensions.php if exists
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
        $extensions = require($file);

        $vendorDir = str_replace('\\', '/', $this->vendorDir);
        $n = strlen($vendorDir);

        foreach ($extensions as &$extension) {
            if (isset($extension['alias'])) {
                foreach ($extension['alias'] as $alias => $path) {
                    $path = str_replace('\\', '/', $path);
                    if (strpos($path . '/', $vendorDir . '/') === 0) {
                        $extension['alias'][$alias] = '<vendor-dir>' . substr($path, $n);
                    }
                }
            }
        }

        return $extensions;
    }

    protected function saveExtensions(array $extensions)
    {
        $file = $this->vendorDir . '/' . static::EXTENSION_FILE;
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        $array = str_replace("'<vendor-dir>", '$vendorDir . \'', var_export($extensions, true));
        file_put_contents($file, "<?php\n\n\$vendorDir = dirname(__DIR__);\n\nreturn $array;\n");
        // invalidate opcache of extensions.php if exists
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
    }

    protected function linkBaseAabcFiles()
    {
        $aabcDir = $this->vendorDir . '/aabcsoft/aabc2';
        if (!file_exists($aabcDir)) {
            mkdir($aabcDir, 0777, true);
        }
        foreach (['Aabc.php', 'BaseAabc.php', 'classes.php'] as $file) {
            file_put_contents($aabcDir . '/' . $file, <<<EOF
<?php


return require(__DIR__ . '/../aabc2-dev/framework/$file');

EOF
            );
        }
    }

    protected function removeBaseAabcFiles()
    {
        $aabcDir = $this->vendorDir . '/aabcsoft/aabc2';
        foreach (['Aabc.php', 'BaseAabc.php', 'classes.php'] as $file) {
            if (file_exists($aabcDir . '/' . $file)) {
                unlink($aabcDir . '/' . $file);
            }
        }
        if (file_exists($aabcDir)) {
            rmdir($aabcDir);
        }
    }

    
    public static function postCreateProject($event)
    {
        static::runCommands($event, __METHOD__);
    }

    
    public static function postInstall($event)
    {
        static::runCommands($event, __METHOD__);
    }

    
    protected static function runCommands($event, $extraKey)
    {
        $params = $event->getComposer()->getPackage()->getExtra();
        if (isset($params[$extraKey]) && is_array($params[$extraKey])) {
            foreach ($params[$extraKey] as $method => $args) {
                call_user_func_array([__CLASS__, $method], (array) $args);
            }
        }
    }

    
    public static function setPermission(array $paths)
    {
        foreach ($paths as $path => $permission) {
            echo "chmod('$path', $permission)...";
            if (is_dir($path) || is_file($path)) {
                try {
                    if (chmod($path, octdec($permission))) {
                        echo "done.\n";
                    };
                } catch (\Exception $e) {
                    echo $e->getMessage() . "\n";
                }
            } else {
                echo "file not found.\n";
            }
        }
    }

    
    public static function generateCookieValidationKey()
    {
        $configs = func_get_args();
        $key = self::generateRandomString();
        foreach ($configs as $config) {
            if (is_file($config)) {
                $content = preg_replace('/(("|\')cookieValidationKey("|\')\s*=>\s*)(""|\'\')/', "\\1'$key'", file_get_contents($config), -1, $count);
                if ($count > 0) {
                    file_put_contents($config, $content);
                }
            }
        }
    }

    protected static function generateRandomString()
    {
        if (!extension_loaded('openssl')) {
            throw new \Exception('The OpenSSL PHP extension is required by Aabc2.');
        }
        $length = 32;
        $bytes = openssl_random_pseudo_bytes($length);
        return strtr(substr(base64_encode($bytes), 0, $length), '+/=', '_-.');
    }

    
    public static function copyFiles(array $paths)
    {
        foreach ($paths as $source => $target) {
            // handle file target as array [path, overwrite]
            $target = (array) $target;
            echo "Copying file $source to $target[0] - ";

            if (!is_file($source)) {
                echo "source file not found.\n";
                continue;
            }

            if (is_file($target[0]) && empty($target[1])) {
                echo "target file exists - skip.\n";
                continue;
            } elseif (is_file($target[0]) && !empty($target[1])) {
                echo "target file exists - overwrite - ";
            }

            try {
                if (!is_dir(dirname($target[0]))) {
                    mkdir(dirname($target[0]), 0777, true);
                }
                if (copy($source, $target[0])) {
                    echo "done.\n";
                }
            } catch (\Exception $e) {
                echo $e->getMessage() . "\n";
            }
        }
    }
}
