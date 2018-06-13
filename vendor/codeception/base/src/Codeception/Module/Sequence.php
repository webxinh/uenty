<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\Exception\ModuleException;
use Codeception\TestInterface;


class Sequence extends CodeceptionModule
{
    public static $hash = [];
    public static $suiteHash = [];
    public static $prefix = '';

    protected $config = ['prefix' => '{id}_'];

    public function _initialize()
    {
        static::$prefix = $this->config['prefix'];
    }

    public function _after(TestInterface $t)
    {
        self::$hash = [];
    }

    public function _afterSuite()
    {
        self::$suiteHash = [];
    }
}

if (!function_exists('sq') && !function_exists('sqs')) {
    require_once __DIR__ . '/../Util/sq.php';
} else {
    throw new ModuleException('Codeception\Module\Sequence', "function 'sq' and 'sqs' already defined");
}
