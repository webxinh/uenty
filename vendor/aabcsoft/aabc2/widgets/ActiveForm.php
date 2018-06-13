<?php


namespace aabc\widgets;

use Aabc;
use aabc\base\InvalidCallException;
use aabc\base\Widget;
use aabc\base\Model;
use aabc\helpers\ArrayHelper;
use aabc\helpers\Url;
use aabc\helpers\Html;
use aabc\helpers\Json;


class ActiveForm extends Widget
{
    
    public $action = '';
    
    public $method = 'post';
    
    public $options = [];
    
    public $fieldClass = 'aabc\widgets\ActiveField';
    
    public $fieldConfig = [];
    
    public $encodeErrorSummary = true;
    
    public $errorSummaryCssClass = 'error-summary';
    
    public $requiredCssClass = 'required';
    
    public $errorCssClass = 'has-error';
    
    public $successCssClass = 'has-success';
    
    public $validatingCssClass = 'validating';
    
    public $enableClientValidation = true;
    
    public $enableAjaxValidation = false;
    
    public $enableClientScript = true;
    
    public $validationUrl;
    
    public $validateOnSubmit = true;
    
    public $validateOnChange = true;
    
    public $validateOnBlur = true;
    
    public $validateOnType = false;
    
    public $validationDelay = 500;
    
    public $ajaxParam = 'ajax';
    
    public $ajaxDataType = 'json';
    
    public $scrollToError = true;
    
    public $scrollToErrorOffset = 0;
    
    public $attributes = [];

    
    private $_fields = [];


    
    public function init()
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        ob_start();
        ob_implicit_flush(false);
    }

    
    public function run()
    {
        if (!empty($this->_fields)) {
            throw new InvalidCallException('Each beginField() should have a matching endField() call.');
        }

        $content = ob_get_clean();
        echo Html::beginForm($this->action, $this->method, $this->options);
        echo $content;

        if ($this->enableClientScript) {
            $id = $this->options['id'];
            $options = Json::htmlEncode($this->getClientOptions());
            $attributes = Json::htmlEncode($this->attributes);
            $view = $this->getView();
            ActiveFormAsset::register($view);
            $view->registerJs("jQuery('#$id').aabcActiveForm($attributes, $options);");
        }

        echo Html::endForm();
    }

    
    protected function getClientOptions()
    {
        $options = [
            'encodeErrorSummary' => $this->encodeErrorSummary,
            'errorSummary' => '.' . implode('.', preg_split('/\s+/', $this->errorSummaryCssClass, -1, PREG_SPLIT_NO_EMPTY)),
            'validateOnSubmit' => $this->validateOnSubmit,
            'errorCssClass' => $this->errorCssClass,
            'successCssClass' => $this->successCssClass,
            'validatingCssClass' => $this->validatingCssClass,
            'ajaxParam' => $this->ajaxParam,
            'ajaxDataType' => $this->ajaxDataType,
            'scrollToError' => $this->scrollToError,
            'scrollToErrorOffset' => $this->scrollToErrorOffset,
        ];
        if ($this->validationUrl !== null) {
            $options['validationUrl'] = Url::to($this->validationUrl);
        }

        // only get the options that are different from the default ones (set in aabc.activeForm.js)
        return array_diff_assoc($options, [
            'encodeErrorSummary' => true,
            'errorSummary' => '.error-summary',
            'validateOnSubmit' => true,
            'errorCssClass' => 'has-error',
            'successCssClass' => 'has-success',
            'validatingCssClass' => 'validating',
            'ajaxParam' => 'ajax',
            'ajaxDataType' => 'json',
            'scrollToError' => true,
            'scrollToErrorOffset' => 0,
        ]);
    }

    
    public function errorSummary($models, $options = [])
    {
        Html::addCssClass($options, $this->errorSummaryCssClass);
        $options['encode'] = $this->encodeErrorSummary;
        return Html::errorSummary($models, $options);
    }

    
    public function field($model, $attribute, $options = [])
    {
        $config = $this->fieldConfig;
        if ($config instanceof \Closure) {
            $config = call_user_func($config, $model, $attribute);
        }
        if (!isset($config['class'])) {
            $config['class'] = $this->fieldClass;
        }
        return Aabc::createObject(ArrayHelper::merge($config, $options, [
            'model' => $model,
            'attribute' => $attribute,
            'form' => $this,
        ]));
    }

    
    public function beginField($model, $attribute, $options = [])
    {
        $field = $this->field($model, $attribute, $options);
        $this->_fields[] = $field;
        return $field->begin();
    }

    
    public function endField()
    {
        $field = array_pop($this->_fields);
        if ($field instanceof ActiveField) {
            return $field->end();
        } else {
            throw new InvalidCallException('Mismatching endField() call.');
        }
    }

    
    public static function validate($model, $attributes = null)
    {
        $result = [];
        if ($attributes instanceof Model) {
            // validating multiple models
            $models = func_get_args();
            $attributes = null;
        } else {
            $models = [$model];
        }
        /* @var $model Model */
        foreach ($models as $model) {
            $model->validate($attributes);
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getInputId($model, $attribute)] = $errors;
            }
        }

        return $result;
    }

    
    public static function validateMultiple($models, $attributes = null)
    {
        $result = [];
        /* @var $model Model */
        foreach ($models as $i => $model) {
            $model->validate($attributes);
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getInputId($model, "[$i]" . $attribute)] = $errors;
            }
        }

        return $result;
    }
}
