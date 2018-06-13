<?php


/* @var $this aabc\web\View */
/* @var $generator aabc\gii\generators\module\Generator */

echo "<?php\n";
?>

namespace <?= $generator->getControllerNamespace() ?>;

use aabc\web\Controller;


class DefaultController extends Controller
{
    
    public function actionIndex()
    {
        return $this->render('index');
    }
}
