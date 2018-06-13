<?php
/* @var $panel aabc\debug\panels\ProfilingPanel */
/* @var $time integer */
/* @var $memory integer */
?>
<div class="aabc-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>" title="Total request processing time was <?= $time ?>">Time <span class="aabc-debug-toolbar__label aabc-debug-toolbar__label_info"><?= $time ?></span></a>
    <a href="<?= $panel->getUrl() ?>" title="Peak memory consumption">Memory <span class="aabc-debug-toolbar__label aabc-debug-toolbar__label_info"><?= $memory ?></span></a>
</div>
