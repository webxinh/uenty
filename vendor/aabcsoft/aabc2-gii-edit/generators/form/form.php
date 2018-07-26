<?php
/* @var $this aabc\web\View */
/* @var $form aabc\widgets\ActiveForm */
/* @var $generator aabc\gii\generators\form\Generator */

echo $form->field($generator, 'viewName');
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'scenarioName');
echo $form->field($generator, 'viewPath');
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');
