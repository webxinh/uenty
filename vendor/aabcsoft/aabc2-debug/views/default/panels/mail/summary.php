<?php
/* @var $panel aabc\debug\panels\MailPanel */
/* @var $mailCount integer */
if ($mailCount): ?>
<div class="aabc-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>">Mail <span class="aabc-debug-toolbar__label"><?= $mailCount ?></span></a>
</div>
<?php endif ?>
