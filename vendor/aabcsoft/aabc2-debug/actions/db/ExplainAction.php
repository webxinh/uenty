<?php


namespace aabc\debug\actions\db;

use aabc\base\Action;
use aabc\debug\panels\DbPanel;
use aabc\web\HttpException;


class ExplainAction extends Action
{
    
    public $panel;


    public function run($seq, $tag)
    {
        $this->controller->loadData($tag);

        $timings = $this->panel->calculateTimings();

        if (!isset($timings[$seq])) {
            throw new HttpException(404, 'Log message not found.');
        }

        $query = $timings[$seq]['info'];

        $results = $this->panel->getDb()->createCommand('EXPLAIN ' . $query)->queryAll();

        $output[] = '<table class="table"><thead><tr>' . implode(array_map(function($key) {
            return '<th>' . $key . '</th>';
        }, array_keys($results[0]))) . '</tr></thead><tbody>';

        foreach ($results as $result) {
            $output[] = '<tr>' . implode(array_map(function($value) {
                return '<td>' . (empty($value) ? 'NULL' : htmlspecialchars($value)) . '</td>';
            }, $result)) . '</tr>';
        }
        $output[] = '</tbody></table>';
        return implode($output);
    }
}
