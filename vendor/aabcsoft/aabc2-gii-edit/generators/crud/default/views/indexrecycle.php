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

use aabc\bootstrap\Modal; /*Them*/
use aabc\helpers\Url; /*Them*/
use aabc\helpers\ArrayHelper; /*Them*/
use aabc\widgets\ActiveForm;
/*use app\models\Dskh; */



/* @var $this aabc\web\View */
<?= !empty($generator->searchModelClass) ? "// use " . ltrim($generator->searchModelClass, '\\') . " ;\n" : '' ?>
<?= !empty($generator->modelClass) ? "// use " . ltrim($generator->modelClass, '\\') . " ;\n" : '' ?>
/* @var $dataProvider aabc\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>
<div id="pjr<?= '<?= Aabc::$app->_model->__'.  Inflector::camel2id(StringHelper::basename($generator->modelClass)) .'?>' ?>"  <?= '<?=Aabc::$app->d->i' ?>  <?= '?>' ?>="<?= '<?= Aabc::$app->_model->__'.  Inflector::camel2id(StringHelper::basename($generator->modelClass)). '?>' ?>"  class="pj">


<div class="<?= '<?= Aabc::$app->_model->__'. Inflector::camel2id(StringHelper::basename($generator->modelClass)) .'?>' ?>-index">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>
<?php if(!empty($generator->searchModelClass)): ?>
<?= "    <?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>



<p>
  
    <?= "<?php " ?> 
    <?= '$_'. Inflector::camel2words(StringHelper::basename($generator->modelClass)) . ' = Aabc::$app->_model->'. Inflector::camel2words(StringHelper::basename($generator->modelClass))?>; 
    $countgetAllRecycle1 = count(<?= '$_'. Inflector::camel2words(StringHelper::basename($generator->modelClass))?>::getAllRecycle1());
    
    if($countgetAllRecycle1 > 0){       
        echo '<button type="button"  '.Aabc::$app->d->ct.'="'.$countgetAllRecycle1.'" '.Aabc::$app->d->u.' ="da" class="btn btn-default bda" '.Aabc::$app->d->i.' ="'.Aabc::$app->_model->__<?=  Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.'"><span class="glyphicon glyphicon-ban-circle mden"></span><?=Aabc::$app->MyConst->vindexrecycle_xoatatca?></button>';
    }
     ?>

</p>




<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?= " ?>GridView::widget([ 
        'id' => 'grr'.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>,
        'options' => ['class' => 'gr'],
        'summary' => "<div class='sy'>(Từ {begin} - {end} trong {totalCount})</div>",
        'dataProvider' => $dataProvider,
        <?= !empty($generator->searchModelClass) ? "//'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n"; ?>


<?php
$count = 0;
$columfirst = '';
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if($count == 0 ){ $columfirst = $name;}
         if (++$count < 4) {
            echo "            Aabc::\$app->_" .Inflector::camel2id(StringHelper::basename($generator->modelClass)). '->' . $name . ",\n";
        } else {
           echo "            //Aabc::\$app->_" .Inflector::camel2id(StringHelper::basename($generator->modelClass)). '->' . $name . ",\n";
        }
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if($count == 0 ){ $columfirst = $column->name;}
         if (++$count < 4) {
            echo "             Aabc::\$app->_".Inflector::camel2id(StringHelper::basename($generator->modelClass)). '->' . $column->name . ($format === 'text' ? "" : ":" . $format) . ",\n";
        } else {
            echo "            //  Aabc::\$app->_".Inflector::camel2id(StringHelper::basename($generator->modelClass)). '->' . $column->name . ($format === 'text' ? "" : ":" . $format) . ",\n";
        }
    }
}
?>          
            //[                
            //    'label' => 'Khôi phục',                                  
            //    'format' => 'raw',
            //    'value' => function ($model) {                          
            //        return $model->id;                    
            //    }, 
            //],


             [                
                'label' => '<?=Aabc::$app->MyConst->vindexrecycle_khoiphuc?>',                  
                'headerOptions' => ['width' => '100'],
                'format' => 'raw',
                'value' => function ($model) {  
                    <?= '$_'. Inflector::camel2words(StringHelper::basename($generator->modelClass)) . ' = Aabc::$app->_model->'. Inflector::camel2words(StringHelper::basename($generator->modelClass))?>; 
                    $countgetAllRecycle1 = count(<?= '$_'. Inflector::camel2words(StringHelper::basename($generator->modelClass))?>::getAllRecycle1());                                      
                    return '<div class="text-center" ><button  '.Aabc::$app->d->ct.' ="'.$countgetAllRecycle1.'"  '.Aabc::$app->d->i.' ="'.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.'" '.Aabc::$app->d->u.' ="res?id='.$model[Aabc::$app->_<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)) .'->'. $columfirst;?>].'"  type="button" class="be glyphicon glyphicon-floppy-open" ></button></div>';                    
                }, 
            ],


            [           
                'label' => '<?=Aabc::$app->MyConst->vindexrecycle_xoa?>',  
                'headerOptions' => ['width' => '100'],
                'format' => 'raw',
                'value' => function ($model) {   
                    <?= '$_'. Inflector::camel2words(StringHelper::basename($generator->modelClass)) . ' = Aabc::$app->_model->'. Inflector::camel2words(StringHelper::basename($generator->modelClass))?>; 
                    $countgetAllRecycle1 = count(<?= '$_'. Inflector::camel2words(StringHelper::basename($generator->modelClass))?>::getAllRecycle1());                     
                    return '<div class="text-center" ><button '.Aabc::$app->d->ct.'="'.$countgetAllRecycle1.'"  '.Aabc::$app->d->i.' ="'.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.'" '.Aabc::$app->d->u.' ="d?id='.$model[Aabc::$app->_<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)) .'->'. $columfirst;?>].'"  type="button" class="bd glyphicon glyphicon-ban-circle" ></button></div>'; 
                }, 
            ],

        ],


         //« » ‹ ›
        'pager' => [
            'firstPageLabel' => '«',
            'prevPageLabel'  => '‹',
            'nextPageLabel' => '›',
            'lastPageLabel' => '»',

            'maxButtonCount'=>3, // Số page hiển thị ví dụ: (First  1 2 3 Last)
        ],


 ]); ?>


   
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

</div>
