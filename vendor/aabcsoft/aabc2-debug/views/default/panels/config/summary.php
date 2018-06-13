<?php
/* @var $panel aabc\debug\panels\ConfigPanel */
?>
<div class="aabc-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>">
        <span class="aabc-debug-toolbar__label"><?= $panel->data['application']['aabc'] ?></span>
        PHP
        <span class="aabc-debug-toolbar__label"><?= $panel->data['php']['version'] ?></span>
    </a>
</div>
