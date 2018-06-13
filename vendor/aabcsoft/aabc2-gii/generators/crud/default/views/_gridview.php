
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
use aabc\grid\GridView;

use aabc\helpers\Url; /*Them*/
use aabc\helpers\ArrayHelper; /*Them*/
use aabc\widgets\ActiveForm;

?>



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


    <?= "<?= " ?>GridView::widget([ 
        'id' => 'gr'.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>,
        'options' => ['class' => 'gr'],
        'emptyText' => Aabc::$app->MyConst->gridview_khongthayketqua,
        'summary' => "<div class='sy'>(Từ {begin} - {end} trong {totalCount})</div>",
        'dataProvider' => $dataProvider,
        'rowOptions' => function($model){            
            $class = '';
            if ($model[Aabc::$app->_<?= Inflector::camel2id(StringHelper::basename($generator->modelClass))?>-><?= $tableNamePrefix?>_status] == '2'){
                $class = 'an';
            }
            return ['class'=>$class];
        },
        <?= !empty($generator->searchModelClass) ? "//'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n"; ?>


         [
            'class' => 'aabc\grid\CheckboxColumn',             
                'checkboxOptions' => function($model) {                  
                   return ['value' => $model[Aabc::$app->_<?= Inflector::camel2id(StringHelper::basename($generator->modelClass))?>-><?=$columfirst;?>]];                    
                },
                'headerOptions' => ['width' => '32'],
                'cssClass' => 'ca',
                'name' => 'tuyen', 
          ],

     


<?php
$count = 0;
// $columfirst = '';
// $tableNamePrefix = '';
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        // if($count == 0 ){ 
            // $columfirst = $name;
        //     $tableNamePrefix = substr($name, 0, strpos($name, '_',1));
        // }
        if (++$count < 4) {
            echo "            Aabc::\$app->_" .Inflector::camel2id(StringHelper::basename($generator->modelClass)). '->' . $name . ",\n";
        } else {
           echo "            //Aabc::\$app->_" .Inflector::camel2id(StringHelper::basename($generator->modelClass)). '->' . $name . ",\n";
        }
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        // if($count == 0 ){ 
            // $columfirst = $column->name;
        //     $tableNamePrefix = substr($column->name, 0, strpos($column->name, '_',1));
        // }
        if (++$count < 4) {
            echo "             Aabc::\$app->_".Inflector::camel2id(StringHelper::basename($generator->modelClass)). '->' . $column->name . ($format === 'text' ? "" : ":" . $format) . ",\n";
        } else {
            echo "            //  Aabc::\$app->_".Inflector::camel2id(StringHelper::basename($generator->modelClass)). '->' . $column->name . ($format === 'text' ? "" : ":" . $format) . ",\n";
        }
    }
}
?>                     

             [                
                //'header' => '<a href="'.Aabc::$app->homeUrl.'sanpham">Reset</a>',
                'attribute' => Aabc::$app->_<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)).'->'. $columfirst;?>,
                //'headerOptions' => ['width' => '100'],                 
                'contentOptions' => [
                    'class' => 'omb',                    
                ],
                'format' => 'raw',
                'value' => function ($model) {                         
                    return '<div>'.$model[Aabc::$app->_<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)) .'->'. $columfirst;?>].'</div><div class="omc" id="'.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.$model[Aabc::$app->_<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)) .'->'. $columfirst;?>].'"><div class="omd">

                    <button type="button"  '.Aabc::$app->d->m.'="2"  class="mb btn btn-default" '.Aabc::$app->d->i.'='.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.'  '.Aabc::$app->d->u.'="u?id='.$model[Aabc::$app->_<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)) .'->'. $columfirst;?>].'">'.Aabc::$app->MyConst->gridview_menu_suachitiet.'<span class="glyphicon glyphicon-pencil"></span></button>

                    <div class="gn"></div>
                    '.                        
                        (Aabc::$app->user->can('web') ?  ($model[Aabc::$app->_<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-><?=$tableNamePrefix?>_status] == 2 ? '<button type="button" class="ml btn btn-default" '.Aabc::$app->d->i.'='.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.'   '.Aabc::$app->d->u.'="us?id='.$model[Aabc::$app->_<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)) .'->'. $columfirst;?>].'">'.Aabc::$app->MyConst->gridview_menu_hienthi.'<span class="glyphicon glyphicon-eye-open"></span></button>' : '<button type="button" class="ml btn btn-default" '.Aabc::$app->d->i.'='.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.'   '.Aabc::$app->d->u.'="us?id='.$model[Aabc::$app->_<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)) .'->'. $columfirst;?>].'">'.Aabc::$app->MyConst->gridview_menu_an.'<span class="glyphicon glyphicon-eye-open"></span></button>') : "" )

                    .'
                    <button type="button" class="br btn btn-default" '.Aabc::$app->d->i.'='.Aabc::$app->_model->__<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.'  '.Aabc::$app->d->u.'="rec?id='.$model[Aabc::$app->_<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)).'->'. $columfirst;?>].'">'.Aabc::$app->MyConst->gridview_menu_thungrac.'<span class="glyphicon glyphicon-trash"></span></button>

                    </div></div>';                                      
                }, 
            ],

        


        ],


         //« » ‹ ›
        'pager' => [
            'firstPageLabel' => '«',
            'prevPageLabel'  => '‹',
            'nextPageLabel' => '›',
            'lastPageLabel' => '»',

            'maxButtonCount'=>1, // Số page hiển thị ví dụ: (First  1 2 3 Last)
        ],

 ]); ?>

<div class="endgr">

<div class='per-page'>

<?= "<?=" ?> 
Html::dropDownList('t', Aabc::$app->request->get('t') != NULL ? Aabc::$app->request->get('t') : [10 => 10], [10 => 10, 20 => 20, 50 => 50, 100 => 100, 200 => 200], [    
    'class' => 'ipage btn btn-default',
    'id' => ''
])<?= "?>" ?>
</div>

<div class="sy0"></div>

 <div class='cas'>

     <select id="sel<?= '<?= Aabc::$app->_model->__' . Inflector::camel2id(StringHelper::basename($generator->modelClass)). '?>' ?>" class="btn btn-default">
         <option value="" selected=""><?= "<?=" ?>Aabc::$app->MyConst->gridview_selectmultiitem_chonthaotac?></option>
          <?php if(Aabc::$app->user->can('web')){ ?>
        <option value="1"><?= "<?=" ?>Aabc::$app->MyConst->gridview_selectmultiitem_an?></option>
        <option value="2"><?= "<?=" ?>Aabc::$app->MyConst->gridview_selectmultiitem_hienthi?></option>
        <?php }?>
        <option value="3"><?= "<?=" ?>Aabc::$app->MyConst->gridview_selectmultiitem_thungrac?></option>      
    </select>

    <?= '<?=' ?> Html::button(Aabc::$app->MyConst->gridview_selectmultiitem_thuchien, [Aabc::$app->d->i => <?= 'Aabc::$app->_model->__' . Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>, Aabc::$app->d->u =>'reca','class' => 'btn btn-default bra', 'method' => 'POST']) ?>
</div>

</div>

  
