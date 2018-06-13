<?php


use aabc\db\ActiveRecordInterface;
use aabc\helpers\StringHelper;





/* @var $this aabc\web\View */
/* @var $generator aabc\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
//$controllerClass

$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>
namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;
use Aabc;

//use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
//use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use aabc\data\ActiveDataProvider;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use aabc\web\NotFoundHttpException;
use aabc\filters\VerbFilter;

<?php
    //primarykey;
    // print_r($pks[0]);
    $tableNamePrefix = substr($pks[0], 0, strpos($pks[0], '_',1));
    // echo $tableNamePrefix;
?>

use aabc\db\Transaction;
use aabc\base\Exception;
use aabc\base\ErrorException;
use aabc\base\ErrorHandler;

use aabc\web\ForbiddenHttpException;
use aabc\filters\AccessControl;
//use aabc\widgets\ActiveForm;


class <?= $controllerClass; ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    public function behaviors()
    {
        return [
            // 'access' => [
            //     'class' => AccessControl::className(),
            //     //'only' => ['create'],
            //     'rules' => [
            //         [
            //             'allow' => true,
            //             //'actions' => ['index','create'],
            //             'roles' => ['@'],
            //             'matchCallback' => function ($rule, $action){
            //                 $control = Aabc::$app->controller->id;
            //                 $action = Aabc::$app->controller->action->id;
            //                 $role = $action . '-' . $control;
            //                 if(Aabc::$app->user->can($role)){
            //                     return true;
            //                 }
            //             }
            //         ],
            //     ],
            // ],


            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'i' => ['POST'], //index
                    'ip' => ['POST'], //index popup
                    'ir' => ['POST'], //indexrecycle
                    'rec' => ['POST'], //recycle
                    'reca' => ['POST'], //recycleall
                    'res' => ['POST'], //restore
                    'd' => ['POST'], //delete
                    'da' => ['POST'], //
                    'c' => ['POST'], //create
                    'u' => ['POST'], //update
                    'ut' => ['POST'], //updatethutu
                    'us' => ['POST'], //updatestatus
                    'pja' => ['POST'], //Pjax element all
                ],
            ],
        ];
    }
    
 <?php 

// foreach ($modelClass->columns as $column) {
        // $format = $generator->generateColumnFormat($column);
        // if($count == 0 ){ 
        //     $columfirst = $column->name;
        //     $tableNamePrefix = substr($column->name, 0, strpos($column->name, '_',1));
        // }
        // if (++$count < 4) {
        //     echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        // } else {
        //     echo "            // '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        // }
    // }
?>


    public function actionI($t = 10)
    {
        //$role = 'backend-<?= strtolower($modelClass) ?>-index2';
        //if(!Aabc::$app->user->can($role)){ return 'nacc';die;}
<?php if (!empty($generator->searchModelClass)): ?>   

        <?=  '$_'.$modelClass. 'Search = Aabc::$app->_model->'.$modelClass.'Search'?>;
        $searchModel = new <?= '$_'.$modelClass ?>Search();

        //$searchModel = new <?= $modelClass ?>Search(
        //    [Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_recycle => '2']
        //);
        $dataProvider = $searchModel->search(Aabc::$app->request->queryParams);
        //$dataProvider->setSort([
        //    'defaultOrder' => ['id'=>SORT_DESC]        
        //]);
        $dataProvider->pagination->pageSize=$t;        
        $kq = $this->renderAjax('index', [        
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        $kq = Aabc::$app->d->decodeview($kq);
            return $kq;
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass ?>::find(),
        ]);
        $kq = $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
        $kq = Aabc::$app->d->decodeview($kq);
            return $kq;
<?php endif; ?>
    }





    public function actionIp($t = 10)
    {
        //$role = 'backend-<?= strtolower($modelClass) ?>-index';
        //if(!Aabc::$app->user->can($role)){ return 'nacc';die;}
<?php if (!empty($generator->searchModelClass)): ?>   
        <?=  '$_'.$modelClass. 'Search = Aabc::$app->_model->'.$modelClass.'Search'?>;
        $searchModel = new <?= '$_'.$modelClass ?>Search();

        //$searchModel = new <?= $modelClass ?>Search(
        //    [Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_recycle => '2']
        //);
        $dataProvider = $searchModel->search(Aabc::$app->request->queryParams);
        //$dataProvider->setSort([
        //    'defaultOrder' => ['id'=>SORT_DESC]        
        //]);
        $dataProvider->pagination->pageSize=$t;        
        $kq = $this->renderAjax('indexpopup', [        
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        $kq = Aabc::$app->d->decodeview($kq);
            return $kq;
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass ?>::find(),
        ]);
        return $this->render('indexpopup', [
            'dataProvider' => $dataProvider,
        ]);
<?php endif; ?>
    }


    public function actionIr() //Indexrecycle
    {
        //$role = 'backend-<?= strtolower($modelClass) ?>-indexrecycle';
        //if(!Aabc::$app->user->can($role)){ return 'nacc';die;}

<?php if (!empty($generator->searchModelClass)): ?>
        
        <?=  '$_'.$modelClass. 'Search = Aabc::$app->_model->'.$modelClass.'Search'?>;
        $searchModel = new <?= '$_'.$modelClass ?>Search(
            [Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_recycle => '1']
        );
        
        $dataProvider = $searchModel->search(Aabc::$app->request->queryParams);
        
        $kq = $this->renderAjax('indexrecycle', [        
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        $kq = Aabc::$app->d->decodeview($kq);
            return $kq;
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass ?>::find(),
        ]);

        return $this->render('indexrecycle', [
            'dataProvider' => $dataProvider,
        ]);
<?php endif; ?>
    }

     public function actionPja()
    {
       
        <?=  '$_'.$modelClass.' = Aabc::$app->_model->'.$modelClass?>;
        $model = new <?= '$_'.$modelClass ?>();
        $model = $model->getAll1();

        Aabc::$app->response->format = \aabc\web\Response::FORMAT_JSON;
        // $kq = serialize($kq);
        if(isset($model)){
            $kq = '';
            foreach ($model as $key => $value) {
                 $kq .= $value[Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_id] .'@abcd#'. $value[Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_ten] . '@aabc#';
            }
            $kq = Aabc::$app->d->decodeview($kq);
            return $kq;
        }        
        die;
    }


    
    //public function actionView(<?= $actionParams ?>)
    //{
    //    die;
    //    return $this->render('view', [
    //        'model' => $this->findModel(<?= $actionParams ?>),
    //    ]);
    //}

    


    public function actionC() //Create
    {
        //$role = 'backend-<?= strtolower($modelClass) ?>-create';
        //if(!Aabc::$app->user->can($role)){ return 'nacc';die;}

        <?=  '$_'.$modelClass. ' = Aabc::$app->_model->'.$modelClass?>;
        $model = new <?= '$_'.$modelClass ?>();        

        //if(Aabc::$app->request->isAjax && $model->load(Aabc::$app->request->post())){
        //    Aabc::$app->response->format = 'json';
        //    return ActiveForm::validate($model);
        //    die;
        //}

        if ($model->load(Aabc::$app->request->post())) {

            
            Aabc::$app->response->format = \aabc\web\Response::FORMAT_JSON;
            return (1 && $model->save());
           
            
            /* Binh thuong */
            /*
            $model->save();
            return $this->redirect(['view', <?= $urlParams ?>]);
            */
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kq = $this->renderAjax('create', [
                'model' => $model,
            ]);
            $kq = Aabc::$app->d->decodeview($kq);
            return $kq;
        }
        die;
    }

    
    public function actionU(<?= $actionParams ?>) //Update
    {
        //$role = 'backend-<?= strtolower($modelClass) ?>-update';
        //if(!Aabc::$app->user->can($role)){ return 'nacc';die;}

        $model = $this->findModel(<?= $actionParams ?>);

        if ($model->load(Aabc::$app->request->post()) ) {
            
            Aabc::$app->response->format = \aabc\web\Response::FORMAT_JSON;
            return (1 && $model->save());

        } 

         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kq = $this->renderAjax('update', [
                'model' => $model,
            ]);
            $kq = Aabc::$app->d->decodeview($kq);
            return $kq;
        }
        die;
    }



    public function actionUs($id) //Updatestatus
    {       
        //$role = 'backend-<?= strtolower($modelClass) ?>-updatestatus';
        //if(!Aabc::$app->user->can($role)){ return 'nacc';die;}

        $model = $this->findModel($id);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if($model[Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_status] == '2'){
                $model[Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_status] = '1';
            }else{
                $model[Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_status] = '2';
            }
             /* Json */
            Aabc::$app->response->format = \aabc\web\Response::FORMAT_JSON;
            return (1 && $model->save());
        } 
        die;
    }







    
     public function actionRes($id) //Restore
    {
        //$role = 'backend-<?= strtolower($modelClass) ?>-restore';
        //if(!Aabc::$app->user->can($role)){ return 'nacc';die;}

        $datajson = 'thatbai'; 
        $model = $this->findModel($id);
        $model[Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_recycle] = '2';
       
        Aabc::$app->response->format = \aabc\web\Response::FORMAT_JSON;
            return (1 && $model->save());
    }


     
    public function actionRec($id) //Recycle
    {
        //$role = 'backend-<?= strtolower($modelClass) ?>-recycle';
        //if(!Aabc::$app->user->can($role)){ return 'nacc';die;}

        $datajson = 'thatbai'; 
        $model = $this->findModel($id);
        $model[Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_recycle] = '1';
        
        Aabc::$app->response->format = \aabc\web\Response::FORMAT_JSON;
            return (1 && $model->save());
    }

     public function actionReca() //Recycleall
    {
        //$role = 'backend-<?= strtolower($modelClass) ?>-recycleall';
        //if(!Aabc::$app->user->can($role)){ return 'nacc';die;}

        $data = Aabc::$app->request->post('selects');
        $typ = Aabc::$app->request->post('typ');
        $valu = Aabc::$app->request->post('valu');

        $datajson = 0;

        $transaction = \Aabc::$app->db->beginTransaction();
        try {
                foreach ($data as $key => $value) {                    
                    $model = $this->findModel($value);
                    
                    if($typ == '3'){
                        $model[Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_recycle] = '1';
                    }

                    if($typ == '1' OR $typ == '2'){
                        $model[Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_status] = $valu;
                    } 

                    if($model->save()){                        
                    }else{
                        $transaction->rollback();
                        $datajson = 0;
                        Aabc::$app->response->format = \aabc\web\Response::FORMAT_JSON;
                        return $datajson;
                    }
                } 
            $transaction->commit();
            $datajson = 1;
        } catch (Exception $e) {            
            $transaction->rollback();
            $datajson = 0;
        }

        Aabc::$app->response->format = \aabc\web\Response::FORMAT_JSON;
        return $datajson;


    }



    public function actionD($id) //Delete
    {
        //$role = 'backend-<?= strtolower($modelClass) ?>-delete';
        //if(!Aabc::$app->user->can($role)){ return 'nacc';die;}

              
        $model =  $this->findModel($id);   
        
        Aabc::$app->response->format = \aabc\web\Response::FORMAT_JSON;
        return (1 && ($model[Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_recycle] == '1')  && $model->delete());
        
    }


    public function actionDa() //Deleteall
    {        
        //$role = 'backend-<?= strtolower($modelClass) ?>-deleteall';
        //if(!Aabc::$app->user->can($role)){ return 'nacc';die;}

        
        Aabc::$app->response->format = \aabc\web\Response::FORMAT_JSON;
        <?=  '$_'.$modelClass. ' = Aabc::$app->_model->'.$modelClass?>;
        return (1 && (<?= '$_'.$modelClass  ?>::deleteAll([Aabc::$app->_<?= strtolower($modelClass) ?>-><?=$tableNamePrefix?>_recycle => '1']) ) );
        
    }

    
    protected function findModel(<?= $actionParams ?>)
    {
<?php
if (count($pks) === 1) {
    $condition = '$id';
} else {
    $condition = [];
    foreach ($pks as $pk) {
        $condition[] = "'$pk' => \$$pk";
    }
    $condition = '[' . implode(', ', $condition) . ']';
}
?>
        <?=  '$_'.$modelClass. ' = Aabc::$app->_model->'.$modelClass?>;
        if (($model = <?= '$_'.$modelClass  ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
