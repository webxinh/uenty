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


<div class="<?= '<?= Aabc::$app->_model->__'. Inflector::camel2id(StringHelper::basename($generator->modelClass)).'?>' ?>-index">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>
<?php if(!empty($generator->searchModelClass)): ?>
<?= "    <?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>


<?= $generator->enablePjax ? '<?php //Pjax::begin([["options" => ["class" => "pj"], "enablePushState" => false,"id" => "pj'.Inflector::camel2id(StringHelper::basename($generator->modelClass)).'" ,"clientOptions" => ["method"=> "GET",] ]); ?>' : '' ?>
<p>  
        
        <button type="button"  <?= '<?=Aabc::$app->d->m' ?>  <?= '?>' ?>="2" id="mb<?= '<?= Aabc::$app->_model->__'.  Inflector::camel2id(StringHelper::basename($generator->modelClass)) .'?>' ?>" <?= '<?=Aabc::$app->d->u' ?>  <?= '?>' ?>  ="c" class="btn btn-default mb" <?= '<?=Aabc::$app->d->i' ?>  <?= '?>' ?>="<?= '<?= Aabc::$app->_model->__'.  Inflector::camel2id(StringHelper::basename($generator->modelClass)) .'?>' ?>"><span class="glyphicon glyphicon-plus mxanh"></span><?= "<?=" ?>Aabc::$app->MyConst->view_btn_them?></button>
  

         <?= "<?php " ?>        
        <?= '$_'. Inflector::camel2words(StringHelper::basename($generator->modelClass)) . ' = Aabc::$app->_model->'. Inflector::camel2words(StringHelper::basename($generator->modelClass))?>;        
        $demthungrac = count(<?= '$_'. Inflector::camel2words(StringHelper::basename($generator->modelClass))?>::getAllRecycle1());

            echo '<button type="button"  '.Aabc::$app->d->m.'="2"  '.($demthungrac > 0 ? : 'disabled').'  id="mb'.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass))?>.'r" '.Aabc::$app->d->u.'="ir" class="btn btn-danger mb" '.Aabc::$app->d->i.'="'.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass))?>.'"><span class="glyphicon glyphicon-trash mden"></span>'.Aabc::$app->MyConst->view_btn_thungrac.' ('.$demthungrac.')</button>';
        
        ?>



    </p>

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

            'maxButtonCount'=>3, // Số page hiển thị ví dụ: (First  1 2 3 Last)
        ],


    ]) ?>

<?php endif; ?>


<div style="clear: both"></div>



<?= $generator->enablePjax ? '<?php //Pjax::end(); ?>' : '' ?>
</div>
