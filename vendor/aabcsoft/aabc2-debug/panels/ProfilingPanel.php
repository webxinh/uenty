<?php


namespace aabc\debug\panels;

use Aabc;
use aabc\debug\Panel;
use aabc\log\Logger;
use aabc\debug\models\search\Profile;


class ProfilingPanel extends Panel
{
    
    private $_models;


    
    public function getName()
    {
        return 'Profiling';
    }

    
    public function getSummary()
    {
        return Aabc::$app->view->render('panels/profile/summary', [
            'memory' => sprintf('%.3f MB', $this->data['memory'] / 1048576),
            'time' => number_format($this->data['time'] * 1000) . ' ms',
            'panel' => $this
        ]);
    }

    
    public function getDetail()
    {
        $searchModel = new Profile();
        $dataProvider = $searchModel->search(Aabc::$app->request->getQueryParams(), $this->getModels());

        return Aabc::$app->view->render('panels/profile/detail', [
            'panel' => $this,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'memory' => sprintf('%.3f MB', $this->data['memory'] / 1048576),
            'time' => number_format($this->data['time'] * 1000) . ' ms',
        ]);
    }

    
    public function save()
    {
        $target = $this->module->logTarget;
        $messages = $target->filterMessages($target->messages, Logger::LEVEL_PROFILE);
        return [
            'memory' => memory_get_peak_usage(),
            'time' => microtime(true) - AABC_BEGIN_TIME,
            'messages' => $messages,
        ];
    }

    
    protected function getModels()
    {
        if ($this->_models === null) {
            $this->_models = [];
            $timings = Aabc::getLogger()->calculateTimings(isset($this->data['messages']) ? $this->data['messages'] : []);

            foreach ($timings as $seq => $profileTiming) {
                $this->_models[] = 	[
                    'duration' => $profileTiming['duration'] * 1000, // in milliseconds
                    'category' => $profileTiming['category'],
                    'info' => $profileTiming['info'],
                    'level' => $profileTiming['level'],
                    'timestamp' => $profileTiming['timestamp'] * 1000, //in milliseconds
                    'seq' => $seq,
                ];
            }
        }

        return $this->_models;
    }
}
