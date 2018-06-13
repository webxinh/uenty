<?php


namespace aabc\captcha;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\validators\ValidationAsset;
use aabc\validators\Validator;


class CaptchaValidator extends Validator
{
    
    public $skipOnEmpty = false;
    
    public $caseSensitive = false;
    
    public $captchaAction = 'site/captcha';


    
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Aabc::t('aabc', 'The verification code is incorrect.');
        }
    }

    
    protected function validateValue($value)
    {
        $captcha = $this->createCaptchaAction();
        $valid = !is_array($value) && $captcha->validate($value, $this->caseSensitive);

        return $valid ? null : [$this->message, []];
    }

    
    public function createCaptchaAction()
    {
        $ca = Aabc::$app->createController($this->captchaAction);
        if ($ca !== false) {
            /* @var $controller \aabc\base\Controller */
            list($controller, $actionID) = $ca;
            $action = $controller->createAction($actionID);
            if ($action !== null) {
                return $action;
            }
        }
        throw new InvalidConfigException('Invalid CAPTCHA action ID: ' . $this->captchaAction);
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'aabc.validation.captcha(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    
    public function getClientOptions($model, $attribute)
    {
        $captcha = $this->createCaptchaAction();
        $code = $captcha->getVerifyCode(false);
        $hash = $captcha->generateValidationHash($this->caseSensitive ? $code : strtolower($code));
        $options = [
            'hash' => $hash,
            'hashKey' => 'aabcCaptcha/' . $captcha->getUniqueId(),
            'caseSensitive' => $this->caseSensitive,
            'message' => Aabc::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ], Aabc::$app->language),
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
