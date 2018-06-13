<?php


namespace aabc\console\controllers;

use Aabc;
use aabc\console\Controller;
use aabc\caching\Cache;
use aabc\helpers\Console;
use aabc\console\Exception;


class CacheController extends Controller
{
    
    public function actionIndex()
    {
        $caches = $this->findCaches();

        if (!empty($caches)) {
            $this->notifyCachesCanBeFlushed($caches);
        } else {
            $this->notifyNoCachesFound();
        }
    }

    
    public function actionFlush()
    {
        $cachesInput = func_get_args();

        if (empty($cachesInput)) {
            throw new Exception('You should specify cache components names');
        }

        $caches = $this->findCaches($cachesInput);
        $cachesInfo = [];

        $foundCaches = array_keys($caches);
        $notFoundCaches = array_diff($cachesInput, array_keys($caches));

        if ($notFoundCaches) {
            $this->notifyNotFoundCaches($notFoundCaches);
        }

        if (!$foundCaches) {
            $this->notifyNoCachesFound();
            return static::EXIT_CODE_NORMAL;
        }

        if (!$this->confirmFlush($foundCaches)) {
            return static::EXIT_CODE_NORMAL;
        }

        foreach ($caches as $name => $class) {
            $cachesInfo[] = [
                'name' => $name,
                'class' => $class,
                'is_flushed' => Aabc::$app->get($name)->flush(),
            ];
        }

        $this->notifyFlushed($cachesInfo);
    }

    
    public function actionFlushAll()
    {
        $caches = $this->findCaches();
        $cachesInfo = [];

        if (empty($caches)) {
            $this->notifyNoCachesFound();
            return static::EXIT_CODE_NORMAL;
        }

        foreach ($caches as $name => $class) {
            $cachesInfo[] = [
                'name' => $name,
                'class' => $class,
                'is_flushed' => Aabc::$app->get($name)->flush(),
            ];
        }

        $this->notifyFlushed($cachesInfo);
    }

    
    public function actionFlushSchema($db = 'db')
    {
        $connection = Aabc::$app->get($db, false);
        if ($connection === null) {
            $this->stdout("Unknown component \"$db\".\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }

        if (!$connection instanceof \aabc\db\Connection) {
            $this->stdout("\"$db\" component doesn't inherit \\aabc\\db\\Connection.\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        } elseif (!$this->confirm("Flush cache schema for \"$db\" connection?")) {
            return static::EXIT_CODE_NORMAL;
        }

        try {
            $schema = $connection->getSchema();
            $schema->refresh();
            $this->stdout("Schema cache for component \"$db\", was flushed.\n\n", Console::FG_GREEN);
        } catch (\Exception $e) {
            $this->stdout($e->getMessage() . "\n\n", Console::FG_RED);
        }
    }

    
    private function notifyCachesCanBeFlushed($caches)
    {
        $this->stdout("The following caches were found in the system:\n\n", Console::FG_YELLOW);

        foreach ($caches as $name => $class) {
            $this->stdout("\t* $name ($class)\n", Console::FG_GREEN);
        }

        $this->stdout("\n");
    }

    
    private function notifyNoCachesFound()
    {
        $this->stdout("No cache components were found in the system.\n", Console::FG_RED);
    }

    
    private function notifyNotFoundCaches($cachesNames)
    {
        $this->stdout("The following cache components were NOT found:\n\n", Console::FG_RED);

        foreach ($cachesNames as $name) {
            $this->stdout("\t* $name \n", Console::FG_GREEN);
        }

        $this->stdout("\n");
    }

    
    private function notifyFlushed($caches)
    {
        $this->stdout("The following cache components were processed:\n\n", Console::FG_YELLOW);

        foreach ($caches as $cache) {
            $this->stdout("\t* " . $cache['name'] .' (' . $cache['class'] . ')', Console::FG_GREEN);

            if (!$cache['is_flushed']) {
                $this->stdout(" - not flushed\n", Console::FG_RED);
            } else {
                $this->stdout("\n");
            }
        }

        $this->stdout("\n");
    }

    
    private function confirmFlush($cachesNames)
    {
        $this->stdout("The following cache components will be flushed:\n\n", Console::FG_YELLOW);

        foreach ($cachesNames as $name) {
            $this->stdout("\t* $name \n", Console::FG_GREEN);
        }

        return $this->confirm("\nFlush above cache components?");
    }

    
    private function findCaches(array $cachesNames = [])
    {
        $caches = [];
        $components = Aabc::$app->getComponents();
        $findAll = ($cachesNames === []);

        foreach ($components as $name => $component) {
            if (!$findAll && !in_array($name, $cachesNames)) {
                continue;
            }

            if ($component instanceof Cache) {
                $caches[$name] = get_class($component);
            } elseif (is_array($component) && isset($component['class']) && $this->isCacheClass($component['class'])) {
                $caches[$name] = $component['class'];
            } elseif (is_string($component) && $this->isCacheClass($component)) {
                $caches[$name] = $component;
            }
        }

        return $caches;
    }

    
    private function isCacheClass($className)
    {
        return is_subclass_of($className, Cache::className());
    }
}
