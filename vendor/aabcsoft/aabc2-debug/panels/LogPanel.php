<?php


namespace aabc\debug\panels;

use Aabc;
use aabc\debug\Panel;
use aabc\log\Logger;
use aabc\debug\models\search\Log;


class LogPanel extends Panel
{
    
    private $_models;


    
    public function getName()
    {
        return 'Logs';
    }

    
    public function getSummary()
    {
        return Aabc::$app->view->render('panels/log/summary', ['data' => $this->data, 'panel' => $this]);
    }

    
    public function getDetail()
    {
        $searchModel = new Log();
        $dataProvider = $searchModel->search(Aabc::$app->request->getQueryParams(), $this->getModels());

        return Aabc::$app->view->render('panels/log/detail', [
            'dataProvider' => $dataProvider,
            'panel' => $this,
            'searchModel' => $searchModel,
        ]);
    }

    
    public function save()
    {
        $target = $this->module->logTarget;
        $messages = $target->filterMessages($target->messages, Logger::LEVEL_ERROR | Logger::LEVEL_INFO | Logger::LEVEL_WARNING | Logger::LEVEL_TRACE);
        foreach($messages as &$message) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($message[0] instanceof \Throwable || $message[0] instanceof \Exception) {
                $message[0] = (string) $message[0];
            }
        }
        return ['messages' => $messages];
    }

    
    protected function getModels($refresh = false)
    {
        if ($this->_models === null || $refresh) {
            $this->_models = [];

            foreach ($this->data['messages'] as $message) {
                $this->_models[] = 	[
                    'message' => $message[0],
                    'level' => $message[1],
                    'category' => $message[2],
                    'time' => ($message[3] * 1000), // time in milliseconds
                    'trace' => $message[4]
                ];
            }
        }

        return $this->_models;
    }
}
