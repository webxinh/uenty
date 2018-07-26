<?php


use aabc\helpers\StringHelper;


/* @var $this aabc\web\View */
/* @var $generator aabc\gii\generators\crud\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $modelAlias = $modelClass . 'Model';
}
$rules = $generator->generateSearchRules(strtolower($modelClass));
$labels = $generator->generateSearchLabels();
$searchAttributes = $generator->getSearchAttributes();
$searchConditions = $generator->generateSearchConditions(strtolower($modelClass));

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->searchModelClass, '\\')) ?>;

use Aabc;
use aabc\base\Model;
use aabc\data\ActiveDataProvider;
//use <?= ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") ?>;


class <?= $searchModelClass ?> extends <?= isset($modelAlias) ? $modelAlias : $modelClass ?>

{
    
    public function rules()
    {
        return [
            <?= implode(",\n            ", $rules) ?>,
        ];
    }

    
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    
    public function search($params) //GET
    //public function search() //POST
    {
        <?php
            $modelClass0 =  '$_'.$modelClass. ' = Aabc::$app->_model->'.$modelClass;
            $modelClass1 =  '$_'.$modelClass;

        ?>
        <?= $modelClass0?>;
        $query = <?= isset($modelAlias) ? $modelAlias : $modelClass1 ?>::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params); //GET
        //$this->load(Aabc::$app->request->post()); //POST

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        <?= implode("\n        ", $searchConditions) ?>

        return $dataProvider;
    }
}
