<?php


/* @var $this aabc\web\View */
/* @var $generator aabc\gii\generators\module\Generator */

$className = $generator->moduleClass;
$pos = strrpos($className, '\\');
$ns = ltrim(substr($className, 0, $pos), '\\');
$className = substr($className, $pos + 1);

echo "<?php\n";
?>

namespace <?= $ns ?>;


class <?= $className ?> extends \aabc\base\Module
{
    
    public $controllerNamespace = '<?= $generator->getControllerNamespace() ?>';

    
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
