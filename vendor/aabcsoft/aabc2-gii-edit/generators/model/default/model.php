<?php echo "<?php\n"; ?>
namespace <?= $generator->ns ?>;
use Aabc;
use common\cont\_<?= strtoupper($className) ?>;
/**
<?php echo "<?php\n"?>
namespace common\cont;
class _<?= strtoupper($className) ?> { 

require_once(__DIR__ . '/../common/const/<?= strtoupper($className) ?>.php');
//Copy to index.php

  const M = 'backend\models\<?=$className?>';
  const S = 'backend\models\<?=$className?>Search';
  const t = '<?= strtolower($className) ?>';
  const T = '<?=$className?>';

  const table = '<?= $generator->generateTableName($tableName) ?>';

<?php foreach ($tableSchema->columns as $column): ?>
 //const <?= "{$column->comment}" ?> = '<?= "{$column->name}" ?>';
<?php endforeach; ?>

<?php foreach ($tableSchema->columns as $column): ?>
 const <?= "{$column->name}" ?> = '<?= "{$column->name}" ?>';
<?php endforeach; ?>

<?php foreach ($tableSchema->columns as $column): ?>
 const <?= "__{$column->name}" ?> = '<?= "{$column->name}" ?>';
<?php endforeach; ?>

} ?>
**/


class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
    
    public static function tableName()
    {
        return _<?= strtoupper($className)?>::table;        
    }
<?php if ($generator->db !== 'db'): ?>

    
    public static function getDb()
    {
        return Aabc::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . ",\n        " ?>];
    }

<?php 
$tableNamePrefix = '';
?>


    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?php 
                if($tableNamePrefix == ''){
                    $tableNamePrefix = substr($name, 0, strpos($name, '_',1));
                } 
                
            ?>            
            _<?= strtoupper($className)."::$name => _".strtoupper($className) ."::__$name ". "," ?>
<?php endforeach; ?>
        ];
    }

   


<?php foreach ($relations as $name => $relation): ?>   
    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>





<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
   
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>
}
