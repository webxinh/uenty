<?php


namespace aabc\web;

use Aabc;
use aabc\base\Component;
use aabc\base\Exception;


class AssetConverter extends Component implements AssetConverterInterface
{
    
    public $commands = [
        'less' => ['css', 'lessc {from} {to} --no-color --source-map'],
        'scss' => ['css', 'sass {from} {to} --sourcemap'],
        'sass' => ['css', 'sass {from} {to} --sourcemap'],
        'styl' => ['css', 'stylus < {from} > {to}'],
        'coffee' => ['js', 'coffee -p {from} > {to}'],
        'ts' => ['js', 'tsc --out {to} {from}'],
    ];
    
    public $forceConvert = false;


    
    public function convert($asset, $basePath)
    {
        $pos = strrpos($asset, '.');
        if ($pos !== false) {
            $ext = substr($asset, $pos + 1);
            if (isset($this->commands[$ext])) {
                list ($ext, $command) = $this->commands[$ext];
                $result = substr($asset, 0, $pos + 1) . $ext;
                if ($this->forceConvert || @filemtime("$basePath/$result") < @filemtime("$basePath/$asset")) {
                    $this->runCommand($command, $basePath, $asset, $result);
                }

                return $result;
            }
        }

        return $asset;
    }

    
    protected function runCommand($command, $basePath, $asset, $result)
    {
        $command = Aabc::getAlias($command);
        
        $command = strtr($command, [
            '{from}' => escapeshellarg("$basePath/$asset"),
            '{to}' => escapeshellarg("$basePath/$result"),
        ]);
        $descriptor = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $pipes = [];
        $proc = proc_open($command, $descriptor, $pipes, $basePath);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        $status = proc_close($proc);

        if ($status === 0) {
            Aabc::trace("Converted $asset into $result:\nSTDOUT:\n$stdout\nSTDERR:\n$stderr", __METHOD__);
        } elseif (AABC_DEBUG) {
            throw new Exception("AssetConverter command '$command' failed with exit code $status:\nSTDOUT:\n$stdout\nSTDERR:\n$stderr");
        } else {
            Aabc::error("AssetConverter command '$command' failed with exit code $status:\nSTDOUT:\n$stdout\nSTDERR:\n$stderr", __METHOD__);
        }

        return $status === 0;
    }
}
