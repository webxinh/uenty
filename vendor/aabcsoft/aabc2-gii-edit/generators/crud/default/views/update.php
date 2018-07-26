<?php

use aabc\helpers\Inflector;
use aabc\helpers\StringHelper;

/* @var $this aabc\web\View */
/* @var $generator aabc\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams(strtolower(Inflector::camel2words(StringHelper::basename($generator->modelClass))));

echo "<?php\n";
?>

use aabc\helpers\Html;

/* @var $this aabc\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = <?= $generator->generateString('Update {modelClass}: ', ['modelClass' => Inflector::camel2words(StringHelper::basename($generator->modelClass))]) ?> . $model[Aabc::$app->_<?= strtolower(Inflector::camel2words(StringHelper::basename($generator->modelClass)))?>-><?= $generator->getNameAttribute() ?>];
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model[Aabc::$app->_<?= strtolower(Inflector::camel2words(StringHelper::basename($generator->modelClass)))?>-><?= $generator->getNameAttribute() ?>], 'url' => ['view', <?= $urlParams ?>]];
$this->params['breadcrumbs'][] = <?= $generator->generateString('Update') ?>;
?>
<div class="<?= '<?= Aabc::$app->_model->__'.  Inflector::camel2id(StringHelper::basename($generator->modelClass)). '?>' ?>-update">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>

    <?= "<?= " ?>$this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
