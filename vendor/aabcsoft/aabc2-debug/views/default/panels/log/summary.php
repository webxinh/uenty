<?php
/* @var $panel aabc\debug\panels\LogPanel */
/* @var $data array */

use aabc\log\Target;
use aabc\log\Logger;

?>

<?php
$titles = ['all' => Aabc::$app->i18n->format('Logged {n,plural,=1{1 message} other{# messages}}', ['n' => count($data['messages'])], 'en-US')];
$errorCount = count(Target::filterMessages($data['messages'], Logger::LEVEL_ERROR));
$warningCount = count(Target::filterMessages($data['messages'], Logger::LEVEL_WARNING));

if ($errorCount) {
    $titles['errors'] = Aabc::$app->i18n->format('{n,plural,=1{1 error} other{# errors}}', ['n' => $errorCount], 'en-US');
}

if ($warningCount) {
    $titles['warnings'] = Aabc::$app->i18n->format('{n,plural,=1{1 warning} other{# warnings}}', ['n' => $warningCount], 'en-US');;
}
?>

<div class="aabc-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>" title="<?= implode(',&nbsp;', $titles) ?>">Log
        <span class="aabc-debug-toolbar__label"><?= count($data['messages']) ?></span>
    </a>
    <?php if ($errorCount): ?>
    <a href="<?= $panel->getUrl(['Log[level]' => Logger::LEVEL_ERROR])?>" title="<?= $titles['errors'] ?>">
        <span class="aabc-debug-toolbar__label aabc-debug-toolbar__label_important"><?= $errorCount ?></span>
    </a>
    <?php endif; ?>
    <?php if ($warningCount): ?>
    <a href="<?= $panel->getUrl(['Log[level]' => Logger::LEVEL_WARNING])?>" title="<?= $titles['warnings'] ?>">
        <span class="aabc-debug-toolbar__label aabc-debug-toolbar__label_warning"><?= $warningCount ?></span>
    </a>
    <?php endif; ?>
</div>
