<?php


namespace aabc\gii\controllers;

use Aabc;
use aabc\web\Controller;
use aabc\web\NotFoundHttpException;


class DefaultController extends Controller
{
    public $layout = 'generator';
    
    public $module;
    
    public $generator;


    public function actionIndex()
    {
        $this->layout = 'main';

        return $this->render('index');
    }

    public function actionView($id)
    {
        $generator = $this->loadGenerator($id);
        $params = ['generator' => $generator, 'id' => $id];

        $preview = Aabc::$app->request->post('preview');
        $generate = Aabc::$app->request->post('generate');
        $answers = Aabc::$app->request->post('answers');

        if ($preview !== null || $generate !== null) {
            if ($generator->validate()) {
                $generator->saveStickyAttributes();
                $files = $generator->generate();
                if ($generate !== null && !empty($answers)) {
                    $params['hasError'] = !$generator->save($files, (array) $answers, $results);
                    $params['results'] = $results;
                } else {
                    $params['files'] = $files;
                    $params['answers'] = $answers;
                }
            }
        }

        return $this->render('view', $params);
    }

    public function actionPreview($id, $file)
    {
        $generator = $this->loadGenerator($id);
        if ($generator->validate()) {
            foreach ($generator->generate() as $f) {
                if ($f->id === $file) {
                    $content = $f->preview();
                    if ($content !== false) {
                        return  '<div class="content">' . $content . '</content>';
                    } else {
                        return '<div class="error">Preview is not available for this file type.</div>';
                    }
                }
            }
        }
        throw new NotFoundHttpException("Code file not found: $file");
    }

    public function actionDiff($id, $file)
    {
        $generator = $this->loadGenerator($id);
        if ($generator->validate()) {
            foreach ($generator->generate() as $f) {
                if ($f->id === $file) {
                    return $this->renderPartial('diff', [
                        'diff' => $f->diff(),
                    ]);
                }
            }
        }
        throw new NotFoundHttpException("Code file not found: $file");
    }

    
    public function actionAction($id, $name)
    {
        $generator = $this->loadGenerator($id);
        $method = 'action' . $name;
        if (method_exists($generator, $method)) {
            return $generator->$method();
        } else {
            throw new NotFoundHttpException("Unknown generator action: $name");
        }
    }

    
    protected function loadGenerator($id)
    {
        if (isset($this->module->generators[$id])) {
            $this->generator = $this->module->generators[$id];
            $this->generator->loadStickyAttributes();
            $this->generator->load(Aabc::$app->request->post());

            return $this->generator;
        } else {
            throw new NotFoundHttpException("Code generator not found: $id");
        }
    }
}
