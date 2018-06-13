<?php
/* @var $this \aabc\web\View */
/* @var $panels \aabc\debug\Panel[] */
/* @var $tag string */
/* @var $position string */

use aabc\helpers\Url;

$firstPanel = reset($panels);
$url = $firstPanel->getUrl();

?>
<div id="aabc-debug-toolbar" class="aabc-debug-toolbar aabc-debug-toolbar_position_<?= $position ?>">
    <div class="aabc-debug-toolbar__bar">
        <div class="aabc-debug-toolbar__block aabc-debug-toolbar__title">
            <a href="<?= Url::to(['index']) ?>">
                <img width="30" height="30" alt="" src="<?= \aabc\debug\Module::getAabcLogo() ?>">
            </a>
        </div>

        <div class="aabc-debug-toolbar__block aabc-debug-toolbar__ajax" style="display: none">
            AJAX <span class="aabc-debug-toolbar__label aabc-debug-toolbar__ajax_counter">0</span>
            <div class="aabc-debug-toolbar__ajax_info">
                <table>
                    <thead>
                    <tr>
                        <th>Method</th>
                        <th>Status</th>
                        <th>URL</th>
                        <th>Time</th>
                        <th>Profile</th>
                    </tr>
                    </thead>
                    <tbody class="aabc-debug-toolbar__ajax_requests"></tbody>
                </table>
            </div>
        </div>

        <?php foreach ($panels as $panel): ?>
            <?= $panel->getSummary() ?>
        <?php endforeach; ?>

        <div class="aabc-debug-toolbar__block_last">

        </div>
        <a class="aabc-debug-toolbar__external" href="#" target="_blank">
            <span class="aabc-debug-toolbar__external-icon"></span>
        </a>

        <span class="aabc-debug-toolbar__toggle">
            <span class="aabc-debug-toolbar__toggle-icon"></span>
        </span>
    </div>

    <div class="aabc-debug-toolbar__view">
        <iframe src="about:blank" frameborder="0"></iframe>
    </div>
</div>
