<?php
/* @var $panel aabc\debug\panels\MailPanel */
/* @var $searchModel aabc\debug\models\search\Mail */
/* @var $dataProvider aabc\data\ArrayDataProvider */

use \aabc\widgets\ListView;
use aabc\widgets\ActiveForm;
use aabc\helpers\Html;

$listView = new ListView([
    'dataProvider' => $dataProvider,
    'itemView' => '_item',
    'layout' => "{summary}\n{items}\n{pager}\n",
]);
$listView->sorter = ['options' => ['class' => 'mail-sorter']];
?>

<h1>Email messages</h1>

<div class="row">
    <div class="col-lg-2">
        <?= Html::button('Form filtering', ['class' => 'btn btn-default', 'onclick' => 'jQuery("#email-form").toggle();']) ?>
    </div>
    <div class="row col-lg-10">
        <?= $listView->renderSorter() ?>
    </div>
</div>

<div id="email-form" style="display: none;">
    <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['/debug/default/view', 'tag' => Aabc::$app->request->get('tag'), 'panel' => 'mail'],
    ]); ?>
    <div class="row">
        <?= $form->field($searchModel, 'from', ['options' => ['class' => 'col-lg-6']])->textInput() ?>

        <?= $form->field($searchModel, 'to', ['options' => ['class' => 'col-lg-6']])->textInput() ?>

        <?= $form->field($searchModel, 'reply', ['options' => ['class' => 'col-lg-6']])->textInput() ?>

        <?= $form->field($searchModel, 'cc', ['options' => ['class' => 'col-lg-6']])->textInput() ?>

        <?= $form->field($searchModel, 'bcc', ['options' => ['class' => 'col-lg-6']])->textInput() ?>

        <?= $form->field($searchModel, 'charset', ['options' => ['class' => 'col-lg-6']])->textInput() ?>

        <?= $form->field($searchModel, 'subject', ['options' => ['class' => 'col-lg-6']])->textInput()	?>

        <?= $form->field($searchModel, 'body', ['options' => ['class' => 'col-lg-6']])->textInput()	?>

        <div class="form-group col-lg-12">
            <?= Html::submitButton('Filter', ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?= $listView->run() ?>
