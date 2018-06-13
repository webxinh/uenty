<?php
/* @var $this \aabc\web\View */
/* @var $summary array */
/* @var $tag string */
/* @var $manifest array */
/* @var $panels \aabc\debug\Panel[] */
/* @var $activePanel \aabc\debug\Panel */

use aabc\bootstrap\ButtonDropdown;
use aabc\bootstrap\ButtonGroup;
use aabc\helpers\Html;
use aabc\helpers\Url;

$this->title = 'Aabc Debugger';
?>
<div class="default-view">
    <div id="aabc-debug-toolbar" class="aabc-debug-toolbar aabc-debug-toolbar_position_top" style="display: none;">
        <div class="aabc-debug-toolbar__bar">
            <div class="aabc-debug-toolbar__block aabc-debug-toolbar__title">
                <a href="<?= Url::to(['index']) ?>">
                    <img width="29" height="30" alt="" src="<?= \aabc\debug\Module::getAabcLogo() ?>">
                </a>
            </div>

            <?php foreach ($panels as $panel): ?>
                <?= $panel->getSummary() ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="container main-container">
        <div class="row">
            <div class="col-lg-2 col-md-2">
                <div class="list-group">
                    <?php
                    foreach ($panels as $id => $panel) {
                        $label = '<i class="glyphicon glyphicon-chevron-right"></i>' . Html::encode($panel->getName());
                        echo Html::a($label, ['view', 'tag' => $tag, 'panel' => $id], [
                            'class' => $panel === $activePanel ? 'list-group-item active' : 'list-group-item',
                        ]);
                    }
                    ?>
                </div>
            </div>
            <div class="col-lg-10 col-md-10">
                <?php
                $statusCode = $summary['statusCode'];
                if ($statusCode === null) {
                    $statusCode = 200;
                }
                if ($statusCode >= 200 && $statusCode < 300) {
                    $calloutClass = 'callout-success';
                } elseif ($statusCode >= 300 && $statusCode < 400) {
                    $calloutClass = 'callout-info';
                } else {
                    $calloutClass = 'callout-important';
                }
                ?>
                <div class="callout <?= $calloutClass ?>">
                    <?php
                        $count = 0;
                        $items = [];
                        foreach ($manifest as $meta) {
                            $label = ($meta['tag'] == $tag ? Html::tag('strong', '&#9654;&nbsp;'.$meta['tag']) : $meta['tag'])
                                . ': ' . $meta['method'] . ' ' . $meta['url'] . ($meta['ajax'] ? ' (AJAX)' : '')
                                . ', ' . date('Y-m-d h:i:s a', $meta['time'])
                                . ', ' . $meta['ip'];
                            $url = ['view', 'tag' => $meta['tag'], 'panel' => $activePanel->id];
                            $items[] = [
                                'label' => $label,
                                'url' => $url,
                            ];
                            if (++$count >= 10) {
                                break;
                            }
                        }
                        echo ButtonGroup::widget([
                            'options'=>['class'=>'btn-group-sm'],
                            'buttons' => [
                                Html::a('All', ['index'], ['class' => 'btn btn-default']),
                                Html::a('Latest', ['view', 'panel' => $activePanel->id], ['class' => 'btn btn-default']),
                                ButtonDropdown::widget([
                                    'label' => 'Last 10',
                                    'options' => ['class' => 'btn-default btn-sm'],
                                    'dropdown' => ['items' => $items, 'encodeLabels' => false],
                                ]),
                            ],
                        ]);
                        echo "\n" . $summary['tag'] . ': ' . $summary['method'] . ' ' . Html::a(Html::encode($summary['url']), $summary['url']);
                        echo ' at ' . date('Y-m-d h:i:s a', $summary['time']) . ' by ' . $summary['ip'];
                    ?>
                </div>
                <?= $activePanel->getDetail() ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    if (!window.frameElement) {
        document.querySelector('#aabc-debug-toolbar').style.display = 'block';
    }
</script>
