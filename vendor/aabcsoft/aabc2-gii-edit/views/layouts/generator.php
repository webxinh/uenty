<?php
use aabc\helpers\Html;

/* @var $this \aabc\web\View */
/* @var $generators \aabc\gii\Generator[] */
/* @var $activeGenerator \aabc\gii\Generator */
/* @var $content string */

$generators = Aabc::$app->controller->module->generators;
$activeGenerator = Aabc::$app->controller->generator;
?>
<?php $this->beginContent('@aabc/gii/views/layouts/main.php'); ?>
<div class="row">
    <div class="col-md-3 col-sm-4">
        <div class="list-group">
            <?php
            foreach ($generators as $id => $generator) {
                $label = '<i class="glyphicon glyphicon-chevron-right"></i>' . Html::encode($generator->getName());
                echo Html::a($label, ['default/view', 'id' => $id], [
                    'class' => $generator === $activeGenerator ? 'list-group-item active' : 'list-group-item',
                ]);
            }
            ?>
        </div>
    </div>
    <div class="col-md-9 col-sm-8">
        <?= $content ?>
    </div>
</div>
<?php $this->endContent(); ?>
