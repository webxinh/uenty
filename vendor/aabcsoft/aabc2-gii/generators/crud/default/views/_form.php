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
use aabc\widgets\ActiveForm;

/* @var $this aabc\web\View */
//use <?= ltrim($generator->modelClass, '\\') ?>;
/* @var $form aabc\widgets\ActiveForm */
?>

<div class="<?= '<?=' ?>Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?><?= '?>'?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin(
        [
            'id' => Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.'-form',
            // 'enableClientValidation' => false,
            // 'enableAjaxValidation' => true,
            //'validationUrl' => ['validate'],
            // 'validateOnBlur' => false,
            // 'validateOnChange' => false
            //'enableAjaxValidation' => true,
        ]
        );
     ?>
    <script type="text/javascript">
      $('[data-toggle="tooltip"]').tooltip();
    </script>
      <div class="">
      


<?php foreach ($generator->getColumnNames() as $attribute) {
    if (in_array($attribute, $safeAttributes)) {
        echo '<div class="col-md-12  pt100">'."\n";
        echo "    <?= " . $generator->generateActiveField($attribute,Inflector::camel2id(StringHelper::basename($generator->modelClass))) . " ?>\n";
        echo '<i class="hdtip glyphicon glyphicon-info-sign" data-trigger="hover" data-placement="top" data-html="true" data-toggle="tooltip" data-original-title="- Title." aria-invalid="false"></i>'."\n";
        echo '</div>'."\n\n";
    }
} ?>

    </div>

    <div class="form-group right">
        <button <?= '<?='?> Aabc::$app->d->i?> = <?= '<?='?> Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?> ?> type="submit" class="btn btn-default haserror lvt"><span class="glyphicon glyphicon-floppy-disk mxanh"></span>Lưu và Thêm</button>

        <button type="submit" class="btn btn-default haserror"><span class="glyphicon glyphicon-floppy-disk mxanh"></span>Lưu</button>

        <button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-ban-circle mdo"></span>Đóng</button>
    </div>




    <?= "<?php " ?>ActiveForm::end(); ?>

</div>



<script type="text/javascript">  

  

    $('.modal-content #<?= "<?=" ?>Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>?>-form').on('keyup keypress', function(e) {
      var keyCode = e.keyCode || e.which;
      if (keyCode === 13) { 
        e.preventDefault();
        return false;
      }
    });


$('form#<?= "<?=" ?>Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>?>-form').on('beforeSubmit', function(e) {
    loadimg();
    var form = $(this);
    var formData = form.serialize();
    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: formData,
        success: function (data) {
            reload('<?= "<?=" ?>Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>?>');  
            
            //update element
            // pjelm('<?= "<?=" ?>Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>?>');  

            if(data == 1){ 
                popthanhcong('','#<?= "<?php " ?> if(isset($_POST['modal'])) echo $_POST['modal']?>');
            }else{
                popthatbai('');
            }            
            // lvtok('<?= "<?=" ?>Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>?>');
        },
        error: function () {
            reload('<?= "<?=" ?>Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>?>');
            poploi();
        }
    });
}).on('submit', function(e){
    e.preventDefault();
});
</script>