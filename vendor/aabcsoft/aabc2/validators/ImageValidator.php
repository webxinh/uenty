<?php


namespace aabc\validators;

use Aabc;
use aabc\web\UploadedFile;


class ImageValidator extends FileValidator
{
    
    public $notImage;
    
    public $minWidth;
    
    public $maxWidth;
    
    public $minHeight;
    
    public $maxHeight;
    
    public $underWidth;
    
    public $overWidth;
    
    public $underHeight;
    
    public $overHeight;


    
    public function init()
    {
        parent::init();

        if ($this->notImage === null) {
            $this->notImage = Aabc::t('aabc', 'The file "{file}" is not an image.');
        }
        if ($this->underWidth === null) {
            $this->underWidth = Aabc::t('aabc', 'The image "{file}" is too small. The width cannot be smaller than {limit, number} {limit, plural, one{pixel} other{pixels}}.');
        }
        if ($this->underHeight === null) {
            $this->underHeight = Aabc::t('aabc', 'The image "{file}" is too small. The height cannot be smaller than {limit, number} {limit, plural, one{pixel} other{pixels}}.');
        }
        if ($this->overWidth === null) {
            $this->overWidth = Aabc::t('aabc', 'The image "{file}" is too large. The width cannot be larger than {limit, number} {limit, plural, one{pixel} other{pixels}}.');
        }
        if ($this->overHeight === null) {
            $this->overHeight = Aabc::t('aabc', 'The image "{file}" is too large. The height cannot be larger than {limit, number} {limit, plural, one{pixel} other{pixels}}.');
        }
    }

    
    protected function validateValue($value)
    {
        $result = parent::validateValue($value);

        return empty($result) ? $this->validateImage($value) : $result;
    }

    
    protected function validateImage($image)
    {
        if (false === ($imageInfo = getimagesize($image->tempName))) {
            return [$this->notImage, ['file' => $image->name]];
        }

        list($width, $height) = $imageInfo;

        if ($width == 0 || $height == 0) {
            return [$this->notImage, ['file' => $image->name]];
        }

        if ($this->minWidth !== null && $width < $this->minWidth) {
            return [$this->underWidth, ['file' => $image->name, 'limit' => $this->minWidth]];
        }

        if ($this->minHeight !== null && $height < $this->minHeight) {
            return [$this->underHeight, ['file' => $image->name, 'limit' => $this->minHeight]];
        }

        if ($this->maxWidth !== null && $width > $this->maxWidth) {
            return [$this->overWidth, ['file' => $image->name, 'limit' => $this->maxWidth]];
        }

        if ($this->maxHeight !== null && $height > $this->maxHeight) {
            return [$this->overHeight, ['file' => $image->name, 'limit' => $this->maxHeight]];
        }

        return null;
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);
        return 'aabc.validation.image(attribute, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ', deferred);';
    }

    
    public function getClientOptions($model, $attribute)
    {
        $options = parent::getClientOptions($model, $attribute);

        $label = $model->getAttributeLabel($attribute);

        if ($this->notImage !== null) {
            $options['notImage'] = Aabc::$app->getI18n()->format($this->notImage, [
                'attribute' => $label,
            ], Aabc::$app->language);
        }

        if ($this->minWidth !== null) {
            $options['minWidth'] = $this->minWidth;
            $options['underWidth'] = Aabc::$app->getI18n()->format($this->underWidth, [
                'attribute' => $label,
                'limit' => $this->minWidth,
            ], Aabc::$app->language);
        }

        if ($this->maxWidth !== null) {
            $options['maxWidth'] = $this->maxWidth;
            $options['overWidth'] = Aabc::$app->getI18n()->format($this->overWidth, [
                'attribute' => $label,
                'limit' => $this->maxWidth,
            ], Aabc::$app->language);
        }

        if ($this->minHeight !== null) {
            $options['minHeight'] = $this->minHeight;
            $options['underHeight'] = Aabc::$app->getI18n()->format($this->underHeight, [
                'attribute' => $label,
                'limit' => $this->minHeight,
            ], Aabc::$app->language);
        }

        if ($this->maxHeight !== null) {
            $options['maxHeight'] = $this->maxHeight;
            $options['overHeight'] = Aabc::$app->getI18n()->format($this->overHeight, [
                'attribute' => $label,
                'limit' => $this->maxHeight,
            ], Aabc::$app->language);
        }

        return $options;
    }
}
