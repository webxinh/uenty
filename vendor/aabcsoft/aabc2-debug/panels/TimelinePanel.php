<?php


namespace aabc\debug\panels;

use Aabc;
use aabc\debug\Panel;
use aabc\debug\models\search\Timeline;
use aabc\base\InvalidConfigException;


class TimelinePanel extends Panel
{
    
    private $_colors = [
        20 => '#1e6823',
        10 => '#44a340',
        1 => '#8cc665'
    ];
    
    private $_models;
    
    private $_start;
    
    private $_end;
    
    private $_duration;


    
    public function init()
    {
        if (!isset($this->module->panels['profiling'])) {
            throw new InvalidConfigException('Unable to determine the profiling panel');
        }
        parent::init();
    }

    
    public function getName()
    {
        return 'Timeline';
    }

    
    public function getDetail()
    {
        $searchModel = new Timeline();
        $dataProvider = $searchModel->search(Aabc::$app->request->getQueryParams(), $this);

        return Aabc::$app->view->render('panels/timeline/detail', [
            'panel' => $this,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    
    public function load($data)
    {
        if (!isset($data['start']) || empty($data['start'])) {
            throw new \RuntimeException('Unable to determine request start time');
        }
        $this->_start = $data['start'] * 1000;

        if (!isset($data['end']) || empty($data['end'])) {
            throw new \RuntimeException('Unable to determine request end time');
        }
        $this->_end = $data['end'] * 1000;

        if (isset($this->module->panels['profiling']->data['time'])) {
            $this->_duration = $this->module->panels['profiling']->data['time'] * 1000;
        } else {
            $this->_duration = $this->_end - $this->_start;
        }

        if ($this->_duration <= 0) {
            throw new \RuntimeException('Duration cannot be zero');
        }
    }

    
    public function save()
    {
        return [
            'start' => AABC_BEGIN_TIME,
            'end' => microtime(true),
        ];
    }

    
    public function setColors($colors)
    {
        krsort($colors);
        $this->_colors = $colors;
    }

    
    public function getColors()
    {
        return $this->_colors;
    }

    
    public function getStart()
    {
        return $this->_start;
    }

    
    public function getDuration()
    {
        return $this->_duration;
    }

    
    protected function getModels($refresh = false)
    {
        if ($this->_models === null || $refresh) {
            $this->_models = [];
            if (isset($this->module->panels['profiling']->data['messages'])) {
                $this->_models = Aabc::getLogger()->calculateTimings($this->module->panels['profiling']->data['messages']);
            }
        }
        return $this->_models;
    }

}
