<?php


namespace aabc\debug\panels;

use Aabc;
use aabc\debug\Panel;
use aabc\log\Logger;
use aabc\debug\models\search\Db;


class DbPanel extends Panel
{
    
    public $criticalQueryThreshold;
    
    public $db = 'db';
    
    public $defaultOrder = [
        'seq' => SORT_ASC
    ];
    
    public $defaultFilter = [];

    
    private $_models;
    
    private $_timings;


    
    public function init()
    {
        $this->actions['db-explain'] = [
            'class' => 'aabc\\debug\\actions\\db\\ExplainAction',
            'panel' => $this,
        ];
    }

    
    public function getName()
    {
        return 'Database';
    }

    
    public function getSummaryName()
    {
        return 'DB';
    }

    
    public function getSummary()
    {
        $timings = $this->calculateTimings();
        $queryCount = count($timings);
        $queryTime = number_format($this->getTotalQueryTime($timings) * 1000) . ' ms';

        return Aabc::$app->view->render('panels/db/summary', [
            'timings' => $this->calculateTimings(),
            'panel' => $this,
            'queryCount' => $queryCount,
            'queryTime' => $queryTime,
        ]);
    }

    
    public function getDetail()
    {
        $searchModel = new Db();

        if (!$searchModel->load(Aabc::$app->request->getQueryParams())) {
            $searchModel->load($this->defaultFilter, '');
        }

        $dataProvider = $searchModel->search($this->getModels());
        $dataProvider->getSort()->defaultOrder = $this->defaultOrder;

        return Aabc::$app->view->render('panels/db/detail', [
            'panel' => $this,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'hasExplain' => $this->hasExplain()
        ]);
    }

    
    public function calculateTimings()
    {
        if ($this->_timings === null) {
            $this->_timings = Aabc::getLogger()->calculateTimings(isset($this->data['messages']) ? $this->data['messages'] : []);
        }

        return $this->_timings;
    }

    
    public function save()
    {
        return ['messages' => $this->getProfileLogs()];
    }

    
    public function getProfileLogs()
    {
        $target = $this->module->logTarget;

        return $target->filterMessages($target->messages, Logger::LEVEL_PROFILE, ['aabc\db\Command::query', 'aabc\db\Command::execute']);
    }

    
    protected function getTotalQueryTime($timings)
    {
        $queryTime = 0;

        foreach ($timings as $timing) {
            $queryTime += $timing['duration'];
        }

        return $queryTime;
    }

    
    protected function getModels()
    {
        if ($this->_models === null) {
            $this->_models = [];
            $timings = $this->calculateTimings();

            foreach ($timings as $seq => $dbTiming) {
                $this->_models[] = [
                    'type' => $this->getQueryType($dbTiming['info']),
                    'query' => $dbTiming['info'],
                    'duration' => ($dbTiming['duration'] * 1000), // in milliseconds
                    'trace' => $dbTiming['trace'],
                    'timestamp' => ($dbTiming['timestamp'] * 1000), // in milliseconds
                    'seq' => $seq,
                ];
            }
        }

        return $this->_models;
    }

    
    protected function getQueryType($timing)
    {
        $timing = ltrim($timing);
        preg_match('/^([a-zA-z]*)/', $timing, $matches);

        return count($matches) ? mb_strtoupper($matches[0], 'utf8') : '';
    }

    
    public function isQueryCountCritical($count)
    {
        return (($this->criticalQueryThreshold !== null) && ($count > $this->criticalQueryThreshold));
    }

    
    public function getTypes()
    {
        return array_reduce(
            $this->_models,
            function ($result, $item) {
                $result[$item['type']] = $item['type'];
                return $result;
            },
            []
        );
    }

    
    protected function hasExplain()
    {
        $db = $this->getDb();
        if (!($db instanceof \aabc\db\Connection)) {
            return false;
        }
        switch ($db->getDriverName()) {
            case 'mysql':
            case 'sqlite':
            case 'pgsql':
            case 'cubrid':
                return true;
            default:
                return false;
        }
    }

    
    public static function canBeExplained($type)
    {
        return $type !== 'SHOW';
    }

    
    public function getDb()
    {
        return Aabc::$app->get($this->db);
    }
}
