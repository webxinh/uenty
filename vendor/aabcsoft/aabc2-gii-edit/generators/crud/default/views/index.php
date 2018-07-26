<?php

use aabc\helpers\Inflector;
use aabc\helpers\StringHelper;

/* @var $this aabc\web\View */
/* @var $generator aabc\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

use aabc\helpers\Html;   
use <?= $generator->indexWidgetType === 'grid' ? "aabc\\grid\\GridView" : "aabc\\widgets\\ListView" ?>;

//use aabc\bootstrap\Modal; /*Them*/
use aabc\helpers\Url; /*Them*/
use aabc\helpers\ArrayHelper; /*Them*/
use aabc\widgets\ActiveForm;
/*use app\models\Dskh; */

<?= $generator->enablePjax ? 'use aabc\widgets\Pjax;' : '' ?>

/* @var $this aabc\web\View */
<?= !empty($generator->searchModelClass) ? "// use " . ltrim($generator->searchModelClass, '\\') . " ;\n" : '' ?>
<?= !empty($generator->modelClass) ? "// use " . ltrim($generator->modelClass, '\\') . " ;\n" : '' ?>
/* @var $dataProvider aabc\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>

<div id="pj<?= '<?='?>Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass))?>?>"    <?= '<?=Aabc::$app->d->i'?>?>=<?=  '<?= '?>Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass))?>?> class="pj">


<div class="<?= '<?='?>Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>?>-index">

    

     <div class="content-left  col-md-2">
         <div class="dnn">
            <fieldset>               
              
            </fieldset>  
            <div class="bhelp">
                <button class="btn btn-default bhelp"  <?= '<?= Aabc::$app->d->st'?><?='?>'?> ="1"    <?= '<?= Aabc::$app->d->gr'?><?='?>'?> ="1" >Hướng dẫn sử dụng</button>
            </div>
        </div>
    </div>


<?= $generator->enablePjax ? '<?php //Pjax::begin([["options" => ["class" => "pj"], "enablePushState" => false,"id" => "pj'.Inflector::camel2id(StringHelper::basename($generator->modelClass)).'" ,"clientOptions" => ["method"=> "GET",] ]); ?>' : '' ?>

  <div class="content-right  col-md-10">

    <div class="content-right-top">       
        <?= "<?php " ?>     
        <?= '$_'. Inflector::camel2words(StringHelper::basename($generator->modelClass)) . ' = Aabc::$app->_model->'. Inflector::camel2words(StringHelper::basename($generator->modelClass))?>;        
        $demthungrac = count(<?= '$_'. Inflector::camel2words(StringHelper::basename($generator->modelClass))?>::getAllRecycle1());

            echo '<button type="button"  '.($demthungrac > 0 ? : 'disabled').'  id="mb'.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.'r" '.Aabc::$app->d->u.'="ir" class="btn btn-danger mb" '.Aabc::$app->d->i.'= '.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?> .'><span class="glyphicon glyphicon-trash mden"></span>'.Aabc::$app->MyConst->view_btn_thungrac.' ('.$demthungrac.')</button>';
        
        

         echo '<button type="button" '.Aabc::$app->d->m.' = "2" id="mb'.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.'"  '.Aabc::$app->d->u .'="c" class="btn btn-success mb"   '. Aabc::$app->d->i.'='.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.'><span class="glyphicon glyphicon-plus mtrang"></span>'.Aabc::$app->MyConst->view_btn_them.'</button>';

         ?>
    </div>

  




<?php
$count = 0;
$columfirst = '';
$tableNamePrefix = '';
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if($count == 0 ){
            $columfirst = $name;
            $tableNamePrefix = substr($name, 0, strpos($name, '_',1));
        }  
        ++$count;      
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if($count == 0 ){ 
            $columfirst = $column->name;
            $tableNamePrefix = substr($column->name, 0, strpos($column->name, '_',1));
        }  
        ++$count;      
    }
}
?>


<?php if ($generator->indexWidgetType === 'grid'): ?>

       <?= "<?php "?>
            //echo $this->render('_gridview', [
            //     'searchModel' => $searchModel,
            //     'dataProvider' => $dataProvider,
            // ]) 
         ?>




   
   
<?php else: ?>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        
        //'itemView' => function ($model, $key, $index, $widget) {
        //    return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
        //},

        'itemView' => '_item',

         //« » ‹ ›
        'pager' => [
            'firstPageLabel' => '«',
            'prevPageLabel'  => '‹',
            'nextPageLabel' => '›',
            'lastPageLabel' => '»',

            'maxButtonCount'=>1, // Số page hiển thị ví dụ: (First  1 2 3 Last)
        ],


    ]) ?>

<?php endif; ?>


</div>

   

<?= $generator->enablePjax ? '<?php //Pjax::end(); ?>' : '' ?>
</div>
