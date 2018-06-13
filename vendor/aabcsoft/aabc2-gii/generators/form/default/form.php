<?php


/* @var $this aabc\web\View */
/* @var $generator aabc\gii\generators\form\Generator */

echo "<?php\n";
?>

use aabc\helpers\Html;
use aabc\widgets\ActiveForm;

/* @var $this aabc\web\View */
/* @var $model <?= $generator->modelClass ?> */
/* @var $form ActiveForm */
<?= "?>" ?>

<div class="<?= str_replace('/', '-', trim($generator->viewName, '_')) ?>">

    <?= "<?php " ?>$form = ActiveForm::begin(); ?>

    <?php foreach ($generator->getModelAttributes() as $attribute): ?>
    <?= "<?= " ?>$form->field($model, '<?= $attribute ?>') ?>
    <?php endforeach; ?>

        <div class="form-group">
            <?= "<?= " ?>Html::submitButton(<?= $generator->generateString('Submit') ?>, ['class' => 'btn btn-primary']) ?>
        </div>
    <?= "<?php " ?>ActiveForm::end(); ?>

</div><!-- <?= str_replace('/', '-', trim($generator->viewName, '-')) ?> -->
