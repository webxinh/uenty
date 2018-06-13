<?php
/* @var $panel aabc\debug\panels\DbPanel */
/* @var $queryCount integer */
/* @var $queryTime integer */
?>
<?php if ($queryCount): ?>
<div class="aabc-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>" title="Executed <?= $queryCount ?> database queries which took <?= $queryTime ?>.">
        <?= $panel->getSummaryName() ?> <span class="aabc-debug-toolbar__label aabc-debug-toolbar__label_info"><?= $queryCount ?></span> <span class="aabc-debug-toolbar__label"><?= $queryTime ?></span>
    </a>
</div>
<?php endif; ?>
