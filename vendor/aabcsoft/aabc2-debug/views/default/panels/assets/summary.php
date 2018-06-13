<?php
/* @var $panel aabc\debug\panels\AssetPanel */
if (!empty($panel->data)):
?>
<div class="aabc-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>" title="Number of asset bundles loaded">Asset Bundles <span class="aabc-debug-toolbar__label aabc-debug-toolbar__label_info"><?= count($panel->data) ?></span></a>
</div>
<?php endif; ?>
