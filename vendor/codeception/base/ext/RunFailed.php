<?php
namespace Codeception\Extension;

use Codeception\Event\PrintResultEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;


class RunFailed extends Extension
{
    public static $events = [
        Events::RESULT_PRINT_AFTER => 'saveFailed'
    ];

    protected $config = ['file' => 'failed'];

    public function _initialize()
    {
        $logPath = str_replace($this->getRootDir(), '', $this->getLogDir()); // get local path to logs
        $this->_reconfigure(['groups' => ['failed' => $logPath . $this->config['file']]]);
    }

    public function saveFailed(PrintResultEvent $e)
    {
        $file = $this->getLogDir() . $this->config['file'];
        $result = $e->getResult();
        if ($result->wasSuccessful()) {
            if (is_file($file)) {
                unlink($file);
            }
            return;
        }
        $output = [];
        foreach ($result->failures() as $fail) {
            $output[] = $this->localizePath(Descriptor::getTestFullName($fail->failedTest()));
        }
        foreach ($result->errors() as $fail) {
            $output[] = $this->localizePath(Descriptor::getTestFullName($fail->failedTest()));
        }

        file_put_contents($file, implode("\n", $output));
    }

    protected function localizePath($path)
    {
        $root = realpath($this->getRootDir()) . DIRECTORY_SEPARATOR;
        if (substr($path, 0, strlen($root)) == $root) {
            return substr($path, strlen($root));
        }
        return $path;
    }
}
