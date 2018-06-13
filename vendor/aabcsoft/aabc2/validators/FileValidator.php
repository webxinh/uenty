<?php


namespace aabc\validators;

use Aabc;
use aabc\helpers\Html;
use aabc\helpers\Json;
use aabc\web\JsExpression;
use aabc\web\UploadedFile;
use aabc\helpers\FileHelper;


class FileValidator extends Validator
{
    
    public $extensions;
    
    public $checkExtensionByMimeType = true;
    
    public $mimeTypes;
    
    public $minSize;
    
    public $maxSize;
    
    public $maxFiles = 1;
    
    public $message;
    
    public $uploadRequired;
    
    public $tooBig;
    
    public $tooSmall;
    
    public $tooMany;
    
    public $wrongExtension;
    
    public $wrongMimeType;


    
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Aabc::t('aabc', 'File upload failed.');
        }
        if ($this->uploadRequired === null) {
            $this->uploadRequired = Aabc::t('aabc', 'Please upload a file.');
        }
        if ($this->tooMany === null) {
            $this->tooMany = Aabc::t('aabc', 'You can upload at most {limit, number} {limit, plural, one{file} other{files}}.');
        }
        if ($this->wrongExtension === null) {
            $this->wrongExtension = Aabc::t('aabc', 'Only files with these extensions are allowed: {extensions}.');
        }
        if ($this->tooBig === null) {
            $this->tooBig = Aabc::t('aabc', 'The file "{file}" is too big. Its size cannot exceed {formattedLimit}.');
        }
        if ($this->tooSmall === null) {
            $this->tooSmall = Aabc::t('aabc', 'The file "{file}" is too small. Its size cannot be smaller than {formattedLimit}.');
        }
        if (!is_array($this->extensions)) {
            $this->extensions = preg_split('/[\s,]+/', strtolower($this->extensions), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $this->extensions = array_map('strtolower', $this->extensions);
        }
        if ($this->wrongMimeType === null) {
            $this->wrongMimeType = Aabc::t('aabc', 'Only files with these MIME types are allowed: {mimeTypes}.');
        }
        if (!is_array($this->mimeTypes)) {
            $this->mimeTypes = preg_split('/[\s,]+/', strtolower($this->mimeTypes), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $this->mimeTypes = array_map('strtolower', $this->mimeTypes);
        }
    }

    
    public function validateAttribute($model, $attribute)
    {
        if ($this->maxFiles != 1) {
            $files = $model->$attribute;
            if (!is_array($files)) {
                $this->addError($model, $attribute, $this->uploadRequired);

                return;
            }
            foreach ($files as $i => $file) {
                if (!$file instanceof UploadedFile || $file->error == UPLOAD_ERR_NO_FILE) {
                    unset($files[$i]);
                }
            }
            $model->$attribute = array_values($files);
            if (empty($files)) {
                $this->addError($model, $attribute, $this->uploadRequired);
            }
            if ($this->maxFiles && count($files) > $this->maxFiles) {
                $this->addError($model, $attribute, $this->tooMany, ['limit' => $this->maxFiles]);
            } else {
                foreach ($files as $file) {
                    $result = $this->validateValue($file);
                    if (!empty($result)) {
                        $this->addError($model, $attribute, $result[0], $result[1]);
                    }
                }
            }
        } else {
            $result = $this->validateValue($model->$attribute);
            if (!empty($result)) {
                $this->addError($model, $attribute, $result[0], $result[1]);
            }
        }
    }

    
    protected function validateValue($value)
    {
        if (!$value instanceof UploadedFile || $value->error == UPLOAD_ERR_NO_FILE) {
            return [$this->uploadRequired, []];
        }

        switch ($value->error) {
            case UPLOAD_ERR_OK:
                if ($this->maxSize !== null && $value->size > $this->getSizeLimit()) {
                    return [
                        $this->tooBig,
                        [
                            'file' => $value->name,
                            'limit' => $this->getSizeLimit(),
                            'formattedLimit' => Aabc::$app->formatter->asShortSize($this->getSizeLimit()),
                        ],
                    ];
                } elseif ($this->minSize !== null && $value->size < $this->minSize) {
                    return [
                        $this->tooSmall,
                        [
                            'file' => $value->name,
                            'limit' => $this->minSize,
                            'formattedLimit' => Aabc::$app->formatter->asShortSize($this->minSize),
                        ],
                    ];
                } elseif (!empty($this->extensions) && !$this->validateExtension($value)) {
                    return [$this->wrongExtension, ['file' => $value->name, 'extensions' => implode(', ', $this->extensions)]];
                } elseif (!empty($this->mimeTypes) &&  !$this->validateMimeType($value)) {
                    return [$this->wrongMimeType, ['file' => $value->name, 'mimeTypes' => implode(', ', $this->mimeTypes)]];
                }
                return null;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:   
                return [$this->tooBig, [
                    'file' => $value->name,
                    'limit' => $this->getSizeLimit(),
                    'formattedLimit' => Aabc::$app->formatter->asShortSize($this->getSizeLimit())
                ]];
            case UPLOAD_ERR_PARTIAL:
                Aabc::warning('File was only partially uploaded: ' . $value->name, __METHOD__);
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                Aabc::warning('Missing the temporary folder to store the uploaded file: ' . $value->name, __METHOD__);
                break;
            case UPLOAD_ERR_CANT_WRITE:
                Aabc::warning('Failed to write the uploaded file to disk: ' . $value->name, __METHOD__);
                break;
            case UPLOAD_ERR_EXTENSION:
                Aabc::warning('File upload was stopped by some PHP extension: ' . $value->name, __METHOD__);
                break;
            default:
                break;
        }

        return [$this->message, []];
    }

    
    public function getSizeLimit()
    {
        // Get the lowest between post_max_size and upload_max_filesize, log a warning if the first is < than the latter
        $limit = $this->sizeToBytes(ini_get('upload_max_filesize'));
        $postLimit = $this->sizeToBytes(ini_get('post_max_size'));
        if ($postLimit > 0 && $postLimit < $limit) {
            Aabc::warning('PHP.ini\'s \'post_max_size\' is less than \'upload_max_filesize\'.', __METHOD__);
            $limit = $postLimit;
        }
        if ($this->maxSize !== null && $limit > 0 && $this->maxSize < $limit) {
            $limit = $this->maxSize;
        }
        if (isset($_POST['MAX_FILE_SIZE']) && $_POST['MAX_FILE_SIZE'] > 0 && $_POST['MAX_FILE_SIZE'] < $limit) {
            $limit = (int) $_POST['MAX_FILE_SIZE'];
        }

        return $limit;
    }

    
    public function isEmpty($value, $trim = false)
    {
        $value = is_array($value) ? reset($value) : $value;
        return !($value instanceof UploadedFile) || $value->error == UPLOAD_ERR_NO_FILE;
    }

    
    private function sizeToBytes($sizeStr)
    {
        switch (substr($sizeStr, -1)) {
            case 'M':
            case 'm':
                return (int) $sizeStr * 1048576;
            case 'K':
            case 'k':
                return (int) $sizeStr * 1024;
            case 'G':
            case 'g':
                return (int) $sizeStr * 1073741824;
            default:
                return (int) $sizeStr;
        }
    }

    
    protected function validateExtension($file)
    {
        $extension = mb_strtolower($file->extension, 'UTF-8');

        if ($this->checkExtensionByMimeType) {

            $mimeType = FileHelper::getMimeType($file->tempName, null, false);
            if ($mimeType === null) {
                return false;
            }

            $extensionsByMimeType = FileHelper::getExtensionsByMimeType($mimeType);

            if (!in_array($extension, $extensionsByMimeType, true)) {
                return false;
            }
        }

        if (!in_array($extension, $this->extensions, true)) {
            return false;
        }

        return true;
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);
        return 'aabc.validation.file(attribute, messages, ' . Json::encode($options) . ');';
    }

    
    public function getClientOptions($model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [];
        if ($this->message !== null) {
            $options['message'] = Aabc::$app->getI18n()->format($this->message, [
                'attribute' => $label,
            ], Aabc::$app->language);
        }

        $options['skipOnEmpty'] = $this->skipOnEmpty;

        if (!$this->skipOnEmpty) {
            $options['uploadRequired'] = Aabc::$app->getI18n()->format($this->uploadRequired, [
                'attribute' => $label,
            ], Aabc::$app->language);
        }

        if ($this->mimeTypes !== null) {
            $mimeTypes = [];
            foreach ($this->mimeTypes as $mimeType) {
                $mimeTypes[] = new JsExpression(Html::escapeJsRegularExpression($this->buildMimeTypeRegexp($mimeType)));
            }
            $options['mimeTypes'] = $mimeTypes;
            $options['wrongMimeType'] = Aabc::$app->getI18n()->format($this->wrongMimeType, [
                'attribute' => $label,
                'mimeTypes' => implode(', ', $this->mimeTypes),
            ], Aabc::$app->language);
        }

        if ($this->extensions !== null) {
            $options['extensions'] = $this->extensions;
            $options['wrongExtension'] = Aabc::$app->getI18n()->format($this->wrongExtension, [
                'attribute' => $label,
                'extensions' => implode(', ', $this->extensions),
            ], Aabc::$app->language);
        }

        if ($this->minSize !== null) {
            $options['minSize'] = $this->minSize;
            $options['tooSmall'] = Aabc::$app->getI18n()->format($this->tooSmall, [
                'attribute' => $label,
                'limit' => $this->minSize,
                'formattedLimit' => Aabc::$app->formatter->asShortSize($this->minSize),
            ], Aabc::$app->language);
        }

        if ($this->maxSize !== null) {
            $options['maxSize'] = $this->maxSize;
            $options['tooBig'] = Aabc::$app->getI18n()->format($this->tooBig, [
                'attribute' => $label,
                'limit' => $this->getSizeLimit(),
                'formattedLimit' => Aabc::$app->formatter->asShortSize($this->getSizeLimit()),
            ], Aabc::$app->language);
        }

        if ($this->maxFiles !== null) {
            $options['maxFiles'] = $this->maxFiles;
            $options['tooMany'] = Aabc::$app->getI18n()->format($this->tooMany, [
                'attribute' => $label,
                'limit' => $this->maxFiles,
            ], Aabc::$app->language);
        }

        return $options;
    }

    
    private function buildMimeTypeRegexp($mask)
    {
        return '/^' . str_replace('\*', '.*', preg_quote($mask, '/')) . '$/';
    }

    
    protected function validateMimeType($file)
    {
        $fileMimeType = FileHelper::getMimeType($file->tempName);

        foreach ($this->mimeTypes as $mimeType) {
            if ($mimeType === $fileMimeType) {
                return true;
            }

            if (strpos($mimeType, '*') !== false && preg_match($this->buildMimeTypeRegexp($mimeType), $fileMimeType)) {
                return true;
            }
        }

        return false;
    }
}
