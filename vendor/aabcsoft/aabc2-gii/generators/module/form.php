<?php
/* @var $this aabc\web\View */
/* @var $form aabc\widgets\ActiveForm */
/* @var $generator aabc\gii\generators\module\Generator */

?>
<div class="module-form">
<?php
	echo "e.g: <code>app\modules\admin\Module</code>";
    echo $form->field($generator, 'moduleClass');
    echo 'e.g: <code>admin</code>';
    echo $form->field($generator, 'moduleID');
?>
</div>
