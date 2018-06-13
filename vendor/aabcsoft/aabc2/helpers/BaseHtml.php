<?php


namespace aabc\helpers;

use Aabc;
use aabc\base\InvalidParamException;
use aabc\db\ActiveRecordInterface;
use aabc\validators\StringValidator;
use aabc\web\Request;
use aabc\base\Model;


class BaseHtml
{
    
    public static $voidElements = [
        'area' => 1,
        'base' => 1,
        'br' => 1,
        'col' => 1,
        'command' => 1,
        'embed' => 1,
        'hr' => 1,
        'img' => 1,
        'input' => 1,
        'keygen' => 1,
        'link' => 1,
        'meta' => 1,
        'param' => 1,
        'source' => 1,
        'track' => 1,
        'wbr' => 1,
    ];
    
    public static $attributeOrder = [
        'type',
        'id',
        'class',
        'name',
        'value',

        'href',
        'src',
        'action',
        'method',

        'selected',
        'checked',
        'readonly',
        'disabled',
        'multiple',

        'size',
        'maxlength',
        'width',
        'height',
        'rows',
        'cols',

        'alt',
        'title',
        'rel',
        'media',
    ];
    
    public static $dataAttributes = ['data', 'data-ng', 'ng'];


    
    public static function encode($content, $doubleEncode = true)
    {
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, Aabc::$app ? Aabc::$app->charset : 'UTF-8', $doubleEncode);
    }

    
    public static function decode($content)
    {
        return htmlspecialchars_decode($content, ENT_QUOTES);
    }

    
    public static function tag($name, $content = '', $options = [])
    {
        if ($name === null || $name === false) {
            return $content;
        }
        $html = "<$name" . static::renderTagAttributes($options) . '>';
        return isset(static::$voidElements[strtolower($name)]) ? $html : "$html$content</$name>";
    }

    
    public static function beginTag($name, $options = [])
    {
        if ($name === null || $name === false) {
            return '';
        }
        return "<$name" . static::renderTagAttributes($options) . '>';
    }

    
    public static function endTag($name)
    {
        if ($name === null || $name === false) {
            return '';
        }
        return "</$name>";
    }

    
    public static function style($content, $options = [])
    {
        return static::tag('style', $content, $options);
    }

    
    public static function script($content, $options = [])
    {
        return static::tag('script', $content, $options);
    }

    
    public static function cssFile($url, $options = [])
    {
        if (!isset($options['rel'])) {
            $options['rel'] = 'stylesheet';
        }
        $options['href'] = Url::to($url);

        if (isset($options['condition'])) {
            $condition = $options['condition'];
            unset($options['condition']);
            return self::wrapIntoCondition(static::tag('link', '', $options), $condition);
        } elseif (isset($options['noscript']) && $options['noscript'] === true) {
            unset($options['noscript']);
            return '<noscript>' . static::tag('link', '', $options) . '</noscript>';
        } else {
            return static::tag('link', '', $options);
        }
    }

    
    public static function jsFile($url, $options = [])
    {
        $options['src'] = Url::to($url);
        if (isset($options['condition'])) {
            $condition = $options['condition'];
            unset($options['condition']);
            return self::wrapIntoCondition(static::tag('script', '', $options), $condition);
        } else {
            return static::tag('script', '', $options);
        }
    }

    
    private static function wrapIntoCondition($content, $condition)
    {
        if (strpos($condition, '!IE') !== false) {
            return "<!--[if $condition]><!-->\n" . $content . "\n<!--<![endif]-->";
        }
        return "<!--[if $condition]>\n" . $content . "\n<![endif]-->";
    }

    
    public static function csrfMetaTags()
    {
        $request = Aabc::$app->getRequest();
        if ($request instanceof Request && $request->enableCsrfValidation) {
            return static::tag('meta', '', ['name' => 'csrf-param', 'content' => $request->csrfParam]) . "\n    "
                . static::tag('meta', '', ['name' => 'csrf-token', 'content' => $request->getCsrfToken()]) . "\n";
        } else {
            return '';
        }
    }

    
    public static function beginForm($action = '', $method = 'post', $options = [])
    {
        $action = Url::to($action);

        $hiddenInputs = [];

        $request = Aabc::$app->getRequest();
        if ($request instanceof Request) {
            if (strcasecmp($method, 'get') && strcasecmp($method, 'post')) {
                // simulate PUT, DELETE, etc. via POST
                $hiddenInputs[] = static::hiddenInput($request->methodParam, $method);
                $method = 'post';
            }
            $csrf = ArrayHelper::remove($options, 'csrf', true);

            if ($csrf && $request->enableCsrfValidation && strcasecmp($method, 'post') === 0) {
                $hiddenInputs[] = static::hiddenInput($request->csrfParam, $request->getCsrfToken());
            }
        }

        if (!strcasecmp($method, 'get') && ($pos = strpos($action, '?')) !== false) {
            // query parameters in the action are ignored for GET method
            // we use hidden fields to add them back
            foreach (explode('&', substr($action, $pos + 1)) as $pair) {
                if (($pos1 = strpos($pair, '=')) !== false) {
                    $hiddenInputs[] = static::hiddenInput(
                        urldecode(substr($pair, 0, $pos1)),
                        urldecode(substr($pair, $pos1 + 1))
                    );
                } else {
                    $hiddenInputs[] = static::hiddenInput(urldecode($pair), '');
                }
            }
            $action = substr($action, 0, $pos);
        }

        $options['action'] = $action;
        $options['method'] = $method;
        $form = static::beginTag('form', $options);
        if (!empty($hiddenInputs)) {
            $form .= "\n" . implode("\n", $hiddenInputs);
        }

        return $form;
    }

    
    public static function endForm()
    {
        return '</form>';
    }

    
    public static function a($text, $url = null, $options = [])
    {
        if ($url !== null) {
            $options['href'] = Url::to($url);
        }
        return static::tag('a', $text, $options);
    }

    
    public static function mailto($text, $email = null, $options = [])
    {
        $options['href'] = 'mailto:' . ($email === null ? $text : $email);
        return static::tag('a', $text, $options);
    }

    
    public static function img($src, $options = [])
    {
        $options['src'] = Url::to($src);
        if (!isset($options['alt'])) {
            $options['alt'] = '';
        }
        return static::tag('img', '', $options);
    }

    
    public static function label($content, $for = null, $options = [])
    {
        $options['for'] = $for;
        return static::tag('label', $content, $options);
    }

    
    public static function button($content = 'Button', $options = [])
    {
        if (!isset($options['type'])) {
            $options['type'] = 'button';
        }
        return static::tag('button', $content, $options);
    }

    
    public static function submitButton($content = 'Submit', $options = [])
    {
        $options['type'] = 'submit';
        return static::button($content, $options);
    }

    
    public static function resetButton($content = 'Reset', $options = [])
    {
        $options['type'] = 'reset';
        return static::button($content, $options);
    }

    
    public static function input($type, $name = null, $value = null, $options = [])
    {
        if (!isset($options['type'])) {
            $options['type'] = $type;
        }
        $options['name'] = $name;
        $options['value'] = $value === null ? null : (string) $value;
        return static::tag('input', '', $options);
    }

    
    public static function buttonInput($label = 'Button', $options = [])
    {
        $options['type'] = 'button';
        $options['value'] = $label;
        return static::tag('input', '', $options);
    }

    
    public static function submitInput($label = 'Submit', $options = [])
    {
        $options['type'] = 'submit';
        $options['value'] = $label;
        return static::tag('input', '', $options);
    }

    
    public static function resetInput($label = 'Reset', $options = [])
    {
        $options['type'] = 'reset';
        $options['value'] = $label;
        return static::tag('input', '', $options);
    }

    
    public static function textInput($name, $value = null, $options = [])
    {
        return static::input('text', $name, $value, $options);
    }

    
    public static function hiddenInput($name, $value = null, $options = [])
    {
        return static::input('hidden', $name, $value, $options);
    }

    
    public static function passwordInput($name, $value = null, $options = [])
    {
        return static::input('password', $name, $value, $options);
    }

    
    public static function fileInput($name, $value = null, $options = [])
    {
        return static::input('file', $name, $value, $options);
    }

    
    public static function textarea($name, $value = '', $options = [])
    {
        $options['name'] = $name;
        $doubleEncode = ArrayHelper::remove($options, 'doubleEncode', true);
        return static::tag('textarea', static::encode($value, $doubleEncode), $options);
    }

    
    public static function radio($name, $checked = false, $options = [])
    {
        return static::booleanInput('radio', $name, $checked, $options);
    }

    
    public static function checkbox($name, $checked = false, $options = [])
    {
        return static::booleanInput('checkbox', $name, $checked, $options);
    }

    
    protected static function booleanInput($type, $name, $checked = false, $options = [])
    {
        $options['checked'] = (bool) $checked;
        $value = array_key_exists('value', $options) ? $options['value'] : '1';
        if (isset($options['uncheck'])) {
            // add a hidden field so that if the checkbox is not selected, it still submits a value
            $hidden = static::hiddenInput($name, $options['uncheck']);
            unset($options['uncheck']);
        } else {
            $hidden = '';
        }
        if (isset($options['label'])) {
            $label = $options['label'];
            $labelOptions = isset($options['labelOptions']) ? $options['labelOptions'] : [];
            unset($options['label'], $options['labelOptions']);
            $content = static::label(static::input($type, $name, $value, $options) . ' ' . $label, null, $labelOptions);
            return $hidden . $content;
        } else {
            return $hidden . static::input($type, $name, $value, $options);
        }
    }

    
    public static function dropDownList($name, $selection = null, $items = [], $options = [])
    {
        if (!empty($options['multiple'])) {
            return static::listBox($name, $selection, $items, $options);
        }
        $options['name'] = $name;
        unset($options['unselect']);
        $selectOptions = static::renderSelectOptions($selection, $items, $options);
        return static::tag('select', "\n" . $selectOptions . "\n", $options);
    }

    
    public static function listBox($name, $selection = null, $items = [], $options = [])
    {
        if (!array_key_exists('size', $options)) {
            $options['size'] = 4;
        }
        if (!empty($options['multiple']) && !empty($name) && substr_compare($name, '[]', -2, 2)) {
            $name .= '[]';
        }
        $options['name'] = $name;
        if (isset($options['unselect'])) {
            // add a hidden field so that if the list box has no option being selected, it still submits a value
            if (!empty($name) && substr_compare($name, '[]', -2, 2) === 0) {
                $name = substr($name, 0, -2);
            }
            $hidden = static::hiddenInput($name, $options['unselect']);
            unset($options['unselect']);
        } else {
            $hidden = '';
        }
        $selectOptions = static::renderSelectOptions($selection, $items, $options);
        return $hidden . static::tag('select', "\n" . $selectOptions . "\n", $options);
    }

    
    public static function checkboxList($name, $selection = null, $items = [], $options = [])
    {
        if (substr($name, -2) !== '[]') {
            $name .= '[]';
        }

        $formatter = ArrayHelper::remove($options, 'item');
        $itemOptions = ArrayHelper::remove($options, 'itemOptions', []);
        $encode = ArrayHelper::remove($options, 'encode', true);
        $separator = ArrayHelper::remove($options, 'separator', "\n");
        $tag = ArrayHelper::remove($options, 'tag', 'div');

        $lines = [];
        $index = 0;
        foreach ($items as $value => $label) {
            $checked = $selection !== null &&
                (!ArrayHelper::isTraversable($selection) && !strcmp($value, $selection)
                    || ArrayHelper::isTraversable($selection) && ArrayHelper::isIn($value, $selection));
            if ($formatter !== null) {
                $lines[] = call_user_func($formatter, $index, $label, $name, $checked, $value);
            } else {
                $lines[] = static::checkbox($name, $checked, array_merge($itemOptions, [
                    'value' => $value,
                    'label' => $encode ? static::encode($label) : $label,
                ]));
            }
            $index++;
        }

        if (isset($options['unselect'])) {
            // add a hidden field so that if the list box has no option being selected, it still submits a value
            $name2 = substr($name, -2) === '[]' ? substr($name, 0, -2) : $name;
            $hidden = static::hiddenInput($name2, $options['unselect']);
            unset($options['unselect']);
        } else {
            $hidden = '';
        }

        $visibleContent = implode($separator, $lines);

        if ($tag === false) {
            return $hidden . $visibleContent;
        }

        return $hidden . static::tag($tag, $visibleContent, $options);
    }

    
    public static function radioList($name, $selection = null, $items = [], $options = [])
    {
        $formatter = ArrayHelper::remove($options, 'item');
        $itemOptions = ArrayHelper::remove($options, 'itemOptions', []);
        $encode = ArrayHelper::remove($options, 'encode', true);
        $separator = ArrayHelper::remove($options, 'separator', "\n");
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        // add a hidden field so that if the list box has no option being selected, it still submits a value
        $hidden = isset($options['unselect']) ? static::hiddenInput($name, $options['unselect']) : '';
        unset($options['unselect']);

        $lines = [];
        $index = 0;
        foreach ($items as $value => $label) {
            $checked = $selection !== null &&
                (!ArrayHelper::isTraversable($selection) && !strcmp($value, $selection)
                    || ArrayHelper::isTraversable($selection) && ArrayHelper::isIn($value, $selection));
            if ($formatter !== null) {
                $lines[] = call_user_func($formatter, $index, $label, $name, $checked, $value);
            } else {
                $lines[] = static::radio($name, $checked, array_merge($itemOptions, [
                    'value' => $value,
                    'label' => $encode ? static::encode($label) : $label,
                ]));
            }
            $index++;
        }
        $visibleContent = implode($separator, $lines);

        if ($tag === false) {
            return $hidden . $visibleContent;
        }

        return $hidden . static::tag($tag, $visibleContent, $options);
    }

    
    public static function ul($items, $options = [])
    {
        $tag = ArrayHelper::remove($options, 'tag', 'ul');
        $encode = ArrayHelper::remove($options, 'encode', true);
        $formatter = ArrayHelper::remove($options, 'item');
        $separator = ArrayHelper::remove($options, 'separator', "\n");
        $itemOptions = ArrayHelper::remove($options, 'itemOptions', []);

        if (empty($items)) {
            return static::tag($tag, '', $options);
        }

        $results = [];
        foreach ($items as $index => $item) {
            if ($formatter !== null) {
                $results[] = call_user_func($formatter, $item, $index);
            } else {
                $results[] = static::tag('li', $encode ? static::encode($item) : $item, $itemOptions);
            }
        }

        return static::tag(
            $tag,
            $separator . implode($separator, $results) . $separator,
            $options
        );
    }

    
    public static function ol($items, $options = [])
    {
        $options['tag'] = 'ol';
        return static::ul($items, $options);
    }

    
    public static function activeLabel($model, $attribute, $options = [])
    {
        $for = ArrayHelper::remove($options, 'for', static::getInputId($model, $attribute));
        $attribute = static::getAttributeName($attribute);
        $label = ArrayHelper::remove($options, 'label', static::encode($model->getAttributeLabel($attribute)));
        return static::label($label, $for, $options);
    }

    
    public static function activeHint($model, $attribute, $options = [])
    {
        $attribute = static::getAttributeName($attribute);
        $hint = isset($options['hint']) ? $options['hint'] : $model->getAttributeHint($attribute);
        if (empty($hint)) {
            return '';
        }
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        unset($options['hint']);
        return static::tag($tag, $hint, $options);
    }

    
    public static function errorSummary($models, $options = [])
    {
        $header = isset($options['header']) ? $options['header'] : '<p>' . Aabc::t('aabc', 'Please fix the following errors:') . '</p>';
        $footer = ArrayHelper::remove($options, 'footer', '');
        $encode = ArrayHelper::remove($options, 'encode', true);
        $showAllErrors = ArrayHelper::remove($options, 'showAllErrors', false);
        unset($options['header']);

        $lines = [];
        if (!is_array($models)) {
            $models = [$models];
        }
        foreach ($models as $model) {
            /* @var $model Model */
            foreach ($model->getErrors() as $errors) {
                foreach ($errors as $error) {
                    $line = $encode ? Html::encode($error) : $error;
                    if (array_search($line, $lines) === false) {
                        $lines[] = $line;
                    }
                    if (!$showAllErrors) {
                        break;
                    }
                }
            }
        }

        if (empty($lines)) {
            // still render the placeholder for client-side validation use
            $content = '<ul></ul>';
            $options['style'] = isset($options['style']) ? rtrim($options['style'], ';') . '; display:none' : 'display:none';
        } else {
            $content = '<ul><li>' . implode("</li>\n<li>", $lines) . '</li></ul>';
        }
        return Html::tag('div', $header . $content . $footer, $options);
    }

    
    public static function error($model, $attribute, $options = [])
    {
        $attribute = static::getAttributeName($attribute);
        $error = $model->getFirstError($attribute);
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        $encode = ArrayHelper::remove($options, 'encode', true);
        return Html::tag($tag, $encode ? Html::encode($error) : $error, $options);
    }

    
    public static function activeInput($type, $model, $attribute, $options = [])
    {
        $name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
        $value = isset($options['value']) ? $options['value'] : static::getAttributeValue($model, $attribute);
        if (!array_key_exists('id', $options)) {
            $options['id'] = static::getInputId($model, $attribute);
        }
        return static::input($type, $name, $value, $options);
    }

    
    private static function normalizeMaxLength($model, $attribute, &$options)
    {
        if (isset($options['maxlength']) && $options['maxlength'] === true) {
            unset($options['maxlength']);
            $attrName = static::getAttributeName($attribute);
            foreach ($model->getActiveValidators($attrName) as $validator) {
                if ($validator instanceof StringValidator && $validator->max !== null) {
                    $options['maxlength'] = $validator->max;
                    break;
                }
            }
        }
    }

    
    public static function activeTextInput($model, $attribute, $options = [])
    {
        self::normalizeMaxLength($model, $attribute, $options);
        return static::activeInput('text', $model, $attribute, $options);
    }

    
    public static function activeHiddenInput($model, $attribute, $options = [])
    {
        return static::activeInput('hidden', $model, $attribute, $options);
    }

    
    public static function activePasswordInput($model, $attribute, $options = [])
    {
        self::normalizeMaxLength($model, $attribute, $options);
        return static::activeInput('password', $model, $attribute, $options);
    }

    
    public static function activeFileInput($model, $attribute, $options = [])
    {
        // add a hidden field so that if a model only has a file field, we can
        // still use isset($_POST[$modelClass]) to detect if the input is submitted
        $hiddenOptions = ['id' => null, 'value' => ''];
        if (isset($options['name'])) {
            $hiddenOptions['name'] = $options['name'];
        }
        return static::activeHiddenInput($model, $attribute, $hiddenOptions)
            . static::activeInput('file', $model, $attribute, $options);
    }

    
    public static function activeTextarea($model, $attribute, $options = [])
    {
        $name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
        if (isset($options['value'])) {
            $value = $options['value'];
            unset($options['value']);
        } else {
            $value = static::getAttributeValue($model, $attribute);
        }
        if (!array_key_exists('id', $options)) {
            $options['id'] = static::getInputId($model, $attribute);
        }
        self::normalizeMaxLength($model, $attribute, $options);
        return static::textarea($name, $value, $options);
    }

    
    public static function activeRadio($model, $attribute, $options = [])
    {
        return static::activeBooleanInput('radio', $model, $attribute, $options);
    }

    
    public static function activeCheckbox($model, $attribute, $options = [])
    {
        return static::activeBooleanInput('checkbox', $model, $attribute, $options);
    }

    
    protected static function activeBooleanInput($type, $model, $attribute, $options = [])
    {
        $name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
        $value = static::getAttributeValue($model, $attribute);

        if (!array_key_exists('value', $options)) {
            $options['value'] = '1';
        }
        if (!array_key_exists('uncheck', $options)) {
            $options['uncheck'] = '0';
        }
        if (!array_key_exists('label', $options)) {
            $options['label'] = static::encode($model->getAttributeLabel(static::getAttributeName($attribute)));
        }

        $checked = "$value" === "{$options['value']}";

        if (!array_key_exists('id', $options)) {
            $options['id'] = static::getInputId($model, $attribute);
        }

        return static::$type($name, $checked, $options);
    }

    
    public static function activeDropDownList($model, $attribute, $items, $options = [])
    {
        if (empty($options['multiple'])) {
            return static::activeListInput('dropDownList', $model, $attribute, $items, $options);
        } else {
            return static::activeListBox($model, $attribute, $items, $options);
        }
    }

    
    public static function activeListBox($model, $attribute, $items, $options = [])
    {
        return static::activeListInput('listBox', $model, $attribute, $items, $options);
    }

    
    public static function activeCheckboxList($model, $attribute, $items, $options = [])
    {
        return static::activeListInput('checkboxList', $model, $attribute, $items, $options);
    }

    
    public static function activeRadioList($model, $attribute, $items, $options = [])
    {
        return static::activeListInput('radioList', $model, $attribute, $items, $options);
    }

    
    protected static function activeListInput($type, $model, $attribute, $items, $options = [])
    {
        $name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
        $selection = isset($options['value']) ? $options['value'] : static::getAttributeValue($model, $attribute);
        if (!array_key_exists('unselect', $options)) {
            $options['unselect'] = '';
        }
        if (!array_key_exists('id', $options)) {
            $options['id'] = static::getInputId($model, $attribute);
        }
        return static::$type($name, $selection, $items, $options);
    }

    
    public static function renderSelectOptions($selection, $items, &$tagOptions = [])
    {
        $lines = [];
        $encodeSpaces = ArrayHelper::remove($tagOptions, 'encodeSpaces', false);
        $encode = ArrayHelper::remove($tagOptions, 'encode', true);
        if (isset($tagOptions['prompt'])) {
            $promptOptions = ['value' => ''];
            if (is_string($tagOptions['prompt'])) {
                $promptText = $tagOptions['prompt'];
            } else {
                $promptText = $tagOptions['prompt']['text'];
                $promptOptions = array_merge($promptOptions, $tagOptions['prompt']['options']);
            }
            $promptText = $encode ? static::encode($promptText) : $promptText;
            if ($encodeSpaces) {
                $promptText = str_replace(' ', '&nbsp;', $promptText);
            }
            $lines[] = static::tag('option', $promptText, $promptOptions);
        }

        $options = isset($tagOptions['options']) ? $tagOptions['options'] : [];
        $groups = isset($tagOptions['groups']) ? $tagOptions['groups'] : [];
        unset($tagOptions['prompt'], $tagOptions['options'], $tagOptions['groups']);
        $options['encodeSpaces'] = ArrayHelper::getValue($options, 'encodeSpaces', $encodeSpaces);
        $options['encode'] = ArrayHelper::getValue($options, 'encode', $encode);

        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $groupAttrs = isset($groups[$key]) ? $groups[$key] : [];
                if (!isset($groupAttrs['label'])) {
                    $groupAttrs['label'] = $key;
                }
                $attrs = ['options' => $options, 'groups' => $groups, 'encodeSpaces' => $encodeSpaces, 'encode' => $encode];
                $content = static::renderSelectOptions($selection, $value, $attrs);
                $lines[] = static::tag('optgroup', "\n" . $content . "\n", $groupAttrs);
            } else {
                $attrs = isset($options[$key]) ? $options[$key] : [];
                $attrs['value'] = (string) $key;
                if (!array_key_exists('selected', $attrs)) {
                    $attrs['selected'] = $selection !== null &&
                        (!ArrayHelper::isTraversable($selection) && !strcmp($key, $selection)
                        || ArrayHelper::isTraversable($selection) && ArrayHelper::isIn($key, $selection));
                }
                $text = $encode ? static::encode($value) : $value;
                if ($encodeSpaces) {
                    $text = str_replace(' ', '&nbsp;', $text);
                }
                $lines[] = static::tag('option', $text, $attrs);
            }
        }

        return implode("\n", $lines);
    }

    
    public static function renderTagAttributes($attributes)
    {
        if (count($attributes) > 1) {
            $sorted = [];
            foreach (static::$attributeOrder as $name) {
                if (isset($attributes[$name])) {
                    $sorted[$name] = $attributes[$name];
                }
            }
            $attributes = array_merge($sorted, $attributes);
        }

        $html = '';
        foreach ($attributes as $name => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= " $name";
                }
            } elseif (is_array($value)) {
                if (in_array($name, static::$dataAttributes)) {
                    foreach ($value as $n => $v) {
                        if (is_array($v)) {
                            $html .= " $name-$n='" . Json::htmlEncode($v) . "'";
                        } else {
                            $html .= " $name-$n=\"" . static::encode($v) . '"';
                        }
                    }
                } elseif ($name === 'class') {
                    if (empty($value)) {
                        continue;
                    }
                    $html .= " $name=\"" . static::encode(implode(' ', $value)) . '"';
                } elseif ($name === 'style') {
                    if (empty($value)) {
                        continue;
                    }
                    $html .= " $name=\"" . static::encode(static::cssStyleFromArray($value)) . '"';
                } else {
                    $html .= " $name='" . Json::htmlEncode($value) . "'";
                }
            } elseif ($value !== null) {
                $html .= " $name=\"" . static::encode($value) . '"';
            }
        }

        return $html;
    }

    
    public static function addCssClass(&$options, $class)
    {
        if (isset($options['class'])) {
            if (is_array($options['class'])) {
                $options['class'] = self::mergeCssClasses($options['class'], (array) $class);
            } else {
                $classes = preg_split('/\s+/', $options['class'], -1, PREG_SPLIT_NO_EMPTY);
                $options['class'] = implode(' ', self::mergeCssClasses($classes, (array) $class));
            }
        } else {
            $options['class'] = $class;
        }
    }

    
    private static function mergeCssClasses(array $existingClasses, array $additionalClasses)
    {
        foreach ($additionalClasses as $key => $class) {
            if (is_int($key) && !in_array($class, $existingClasses)) {
                $existingClasses[] = $class;
            } elseif (!isset($existingClasses[$key])) {
                $existingClasses[$key] = $class;
            }
        }
        return array_unique($existingClasses);
    }

    
    public static function removeCssClass(&$options, $class)
    {
        if (isset($options['class'])) {
            if (is_array($options['class'])) {
                $classes = array_diff($options['class'], (array) $class);
                if (empty($classes)) {
                    unset($options['class']);
                } else {
                    $options['class'] = $classes;
                }
            } else {
                $classes = preg_split('/\s+/', $options['class'], -1, PREG_SPLIT_NO_EMPTY);
                $classes = array_diff($classes, (array) $class);
                if (empty($classes)) {
                    unset($options['class']);
                } else {
                    $options['class'] = implode(' ', $classes);
                }
            }
        }
    }

    
    public static function addCssStyle(&$options, $style, $overwrite = true)
    {
        if (!empty($options['style'])) {
            $oldStyle = is_array($options['style']) ? $options['style'] : static::cssStyleToArray($options['style']);
            $newStyle = is_array($style) ? $style : static::cssStyleToArray($style);
            if (!$overwrite) {
                foreach ($newStyle as $property => $value) {
                    if (isset($oldStyle[$property])) {
                        unset($newStyle[$property]);
                    }
                }
            }
            $style = array_merge($oldStyle, $newStyle);
        }
        $options['style'] = is_array($style) ? static::cssStyleFromArray($style) : $style;
    }

    
    public static function removeCssStyle(&$options, $properties)
    {
        if (!empty($options['style'])) {
            $style = is_array($options['style']) ? $options['style'] : static::cssStyleToArray($options['style']);
            foreach ((array) $properties as $property) {
                unset($style[$property]);
            }
            $options['style'] = static::cssStyleFromArray($style);
        }
    }

    
    public static function cssStyleFromArray(array $style)
    {
        $result = '';
        foreach ($style as $name => $value) {
            $result .= "$name: $value; ";
        }
        // return null if empty to avoid rendering the "style" attribute
        return $result === '' ? null : rtrim($result);
    }

    
    public static function cssStyleToArray($style)
    {
        $result = [];
        foreach (explode(';', $style) as $property) {
            $property = explode(':', $property);
            if (count($property) > 1) {
                $result[trim($property[0])] = trim($property[1]);
            }
        }
        return $result;
    }

    
    public static function getAttributeName($attribute)
    {
        if (preg_match('/(^|.*\])([\w\.]+)(\[.*|$)/', $attribute, $matches)) {
            return $matches[2];
        } else {
            throw new InvalidParamException('Attribute name must contain word characters only.');
        }
    }

    
    public static function getAttributeValue($model, $attribute)
    {
        if (!preg_match('/(^|.*\])([\w\.]+)(\[.*|$)/', $attribute, $matches)) {
            throw new InvalidParamException('Attribute name must contain word characters only.');
        }
        $attribute = $matches[2];
        $value = $model->$attribute;
        if ($matches[3] !== '') {
            foreach (explode('][', trim($matches[3], '[]')) as $id) {
                if ((is_array($value) || $value instanceof \ArrayAccess) && isset($value[$id])) {
                    $value = $value[$id];
                } else {
                    return null;
                }
            }
        }

        // https://github.com/aabcsoft/aabc2/issues/1457
        if (is_array($value)) {
            foreach ($value as $i => $v) {
                if ($v instanceof ActiveRecordInterface) {
                    $v = $v->getPrimaryKey(false);
                    $value[$i] = is_array($v) ? json_encode($v) : $v;
                }
            }
        } elseif ($value instanceof ActiveRecordInterface) {
            $value = $value->getPrimaryKey(false);

            return is_array($value) ? json_encode($value) : $value;
        }

        return $value;
    }

    
    public static function getInputName($model, $attribute)
    {
        $formName = $model->formName();
        if (!preg_match('/(^|.*\])([\w\.]+)(\[.*|$)/', $attribute, $matches)) {
            throw new InvalidParamException('Attribute name must contain word characters only.');
        }
        $prefix = $matches[1];
        $attribute = $matches[2];
        $suffix = $matches[3];
        if ($formName === '' && $prefix === '') {
            return $attribute . $suffix;
        } elseif ($formName !== '') {
            return $formName . $prefix . "[$attribute]" . $suffix;
        } else {
            throw new InvalidParamException(get_class($model) . '::formName() cannot be empty for tabular inputs.');
        }
    }

    
    public static function getInputId($model, $attribute)
    {
        $name = strtolower(static::getInputName($model, $attribute));
        return str_replace(['[]', '][', '[', ']', ' ', '.'], ['', '-', '-', '', '-', '-'], $name);
    }

    
    public static function escapeJsRegularExpression($regexp)
    {
        $pattern = preg_replace('/\\\\x\{?([0-9a-fA-F]+)\}?/', '\u$1', $regexp);
        $deliminator = substr($pattern, 0, 1);
        $pos = strrpos($pattern, $deliminator, 1);
        $flag = substr($pattern, $pos + 1);
        if ($deliminator !== '/') {
            $pattern = '/' . str_replace('/', '\\/', substr($pattern, 1, $pos - 1)) . '/';
        } else {
            $pattern = substr($pattern, 0, $pos + 1);
        }
        if (!empty($flag)) {
            $pattern .= preg_replace('/[^igm]/', '', $flag);
        }

        return $pattern;
    }
}
