<?php
/* @var $this aabc\web\View */
/* @var $form aabc\widgets\ActiveForm */
/* @var $generator aabc\gii\generators\crud\Generator */
echo 'e.g: <code>app\models\Post</code>';
echo $form->field($generator, 'modelClass');
echo 'e.g: <code>app\models\PostSearch</code>';
echo $form->field($generator, 'searchModelClass');
echo 'e.g:<code>app\controllers\PostController</code>';
echo $form->field($generator, 'controllerClass');
echo $form->field($generator, 'viewPath');
echo $form->field($generator, 'baseControllerClass');
echo $form->field($generator, 'indexWidgetType')->dropDownList([
    'grid' => 'GridView',
    'list' => 'ListView',
]);
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'enablePjax')->checkbox();
echo $form->field($generator, 'messageCategory');
