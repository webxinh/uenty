<?php

use aabc\helpers\Inflector;
use aabc\helpers\StringHelper;

/* @var $this aabc\web\View */
/* @var $generator aabc\gii\generators\crud\Generator */

/* @var $model \aabc\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>

use aabc\helpers\Html;

/* @var $this aabc\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form aabc\widgets\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-item">
   

<?php foreach ($generator->getColumnNames() as $attribute) {
    if (in_array($attribute, $safeAttributes)) {
    	 echo $attribute . ": " . "<?= \$model->". $attribute . "?> ";    	        
    }
} ?>
   


</div>

