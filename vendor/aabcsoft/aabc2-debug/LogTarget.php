<?php


namespace aabc\debug;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\helpers\FileHelper;
use aabc\log\Target;


class LogTarget extends Target
{
    
    public $module;
    public $tag;


    
    public function __construct($module, $config = [])
    {
        parent::__construct($config);
        $this->module = $module;
        $this->tag = uniqid();
    }

    
    public function export()
    {
        $path = $this->module->dataPath;
        FileHelper::createDirectory($path, $this->module->dirMode);

        $summary = $this->collectSummary();
        $dataFile = "$path/{$this->tag}.data";
        $data = [];
        foreach ($this->module->panels as $id => $panel) {
            $data[$id] = $panel->save();
        }
        $data['summary'] = $summary;
        file_put_contents($dataFile, serialize($data));
        if ($this->module->fileMode !== null) {
            @chmod($dataFile, $this->module->fileMode);
        }

        $indexFile = "$path/index.data";
        $this->updateIndexFile($indexFile, $summary);
    }

    
    private function updateIndexFile($indexFile, $summary)
    {
        touch($indexFile);
        if (($fp = @fopen($indexFile, 'r+')) === false) {
            throw new InvalidConfigException("Unable to open debug data index file: $indexFile");
        }
        @flock($fp, LOCK_EX);
        $manifest = '';
        while (($buffer = fgets($fp)) !== false) {
            $manifest .= $buffer;
        }
        if (!feof($fp) || empty($manifest)) {
            // error while reading index data, ignore and create new
            $manifest = [];
        } else {
            $manifest = unserialize($manifest);
        }

        $manifest[$this->tag] = $summary;
        $this->gc($manifest);

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, serialize($manifest));

        @flock($fp, LOCK_UN);
        @fclose($fp);

        if ($this->module->fileMode !== null) {
            @chmod($indexFile, $this->module->fileMode);
        }
    }

    
    public function collect($messages, $final)
    {
        $this->messages = array_merge($this->messages, $messages);
        if ($final) {
            $this->export();
        }
    }

    protected function gc(&$manifest)
    {
        if (count($manifest) > $this->module->historySize + 10) {
            $n = count($manifest) - $this->module->historySize;
            foreach (array_keys($manifest) as $tag) {
                $file = $this->module->dataPath . "/$tag.data";
                @unlink($file);
                unset($manifest[$tag]);
                if (--$n <= 0) {
                    break;
                }
            }
        }
    }

    
    protected function collectSummary()
    {
        if (Aabc::$app === null) {
            return '';
        }

        $request = Aabc::$app->getRequest();
        $response = Aabc::$app->getResponse();
        $summary = [
            'tag' => $this->tag,
            'url' => $request->getAbsoluteUrl(),
            'ajax' => (int) $request->getIsAjax(),
            'method' => $request->getMethod(),
            'ip' => $request->getUserIP(),
            'time' => time(),
            'statusCode' => $response->statusCode,
            'sqlCount' => $this->getSqlTotalCount(),
        ];

        if (isset($this->module->panels['mail'])) {
            $summary['mailCount'] = count($this->module->panels['mail']->getMessages());
        }

        return $summary;
    }

    
    protected function getSqlTotalCount()
    {
        if (!isset($this->module->panels['db'])) {
            return 0;
        }
        $profileLogs = $this->module->panels['db']->getProfileLogs();

        # / 2 because messages are in couple (begin/end)

        return count($profileLogs) / 2;
    }
}
