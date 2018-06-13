<?php


namespace aabc\debug\panels;

use Aabc;
use aabc\helpers\Html;
use aabc\debug\Panel;
use aabc\web\AssetBundle;
use aabc\web\AssetManager;


class AssetPanel extends Panel
{
    
    public function getName()
    {
        return 'Asset Bundles';
    }

    
    public function getSummary()
    {
        return Aabc::$app->view->render('panels/assets/summary', ['panel' => $this]);
    }

    
    public function getDetail()
    {
        return Aabc::$app->view->render('panels/assets/detail', ['panel' => $this]);
    }

    
    public function save()
    {
        $bundles = Aabc::$app->view->assetManager->bundles;
        if (empty($bundles)) { // bundles can be false
            return [];
        }
        $data = [];
        foreach ($bundles as $name => $bundle) {
            if ($bundle instanceof AssetBundle) {
                $bundleData = (array) $bundle;
                if (isset($bundleData['publishOptions']['beforeCopy']) && $bundleData['publishOptions']['beforeCopy'] instanceof \Closure) {
                    $bundleData['publishOptions']['beforeCopy'] = '\Closure';
                }
                if (isset($bundleData['publishOptions']['afterCopy']) && $bundleData['publishOptions']['afterCopy'] instanceof \Closure) {
                    $bundleData['publishOptions']['afterCopy'] = '\Closure';
                }
                $data[$name] = $bundleData;
            }
        }
        return $data;
    }

    
    protected function format(array $bundles)
    {
        foreach ($bundles as $bundle) {

            $this->cssCount += count($bundle->css);
            $this->jsCount += count($bundle->js);

            array_walk($bundle->css, function(&$file, $key, $userdata) {
                $file = Html::a($file, $userdata->baseUrl . '/' . $file, ['target' => '_blank']);
            }, $bundle);

            array_walk($bundle->js, function(&$file, $key, $userdata) {
                $file = Html::a($file, $userdata->baseUrl . '/' . $file, ['target' => '_blank']);
            }, $bundle);

            array_walk($bundle->depends, function(&$depend) {
                $depend = Html::a($depend, '#' . $depend);
            });

            $this->formatOptions($bundle->publishOptions);
            $this->formatOptions($bundle->jsOptions);
            $this->formatOptions($bundle->cssOptions);
        }

        return $bundles;
    }

    
    protected function formatOptions(array &$params)
    {
        if (!is_array($params)) {
            return $params;
        }

        foreach ($params as $param => $value) {
            $params[$param] = Html::tag('strong', '\'' . $param . '\' => ') . (string) $value;
        }

        return $params;
    }
}
