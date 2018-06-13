<?php
/* @var $panel aabc\debug\panels\RequestPanel */

use aabc\helpers\Html;
use aabc\web\Response;

$statusCode = $panel->data['statusCode'];
if ($statusCode === null) {
    $statusCode = 200;
}
if ($statusCode >= 200 && $statusCode < 300) {
    $class = 'aabc-debug-toolbar__label_success';
} elseif ($statusCode >= 300 && $statusCode < 400) {
    $class = 'aabc-debug-toolbar__label_info';
} else {
    $class = 'aabc-debug-toolbar__label_important';
}
$statusText = Html::encode(isset(Response::$httpStatuses[$statusCode]) ? Response::$httpStatuses[$statusCode] : '');
?>
<div class="aabc-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>" title="Status code: <?= $statusCode ?> <?= $statusText ?>">Status <span class="aabc-debug-toolbar__label <?= $class ?>"><?= $statusCode ?></span></a>
    <a href="<?= $panel->getUrl() ?>" title="Action: <?= $panel->data['action'] ?>">Route <span class="aabc-debug-toolbar__label"><?= $panel->data['route'] ?></span></a>
</div>
