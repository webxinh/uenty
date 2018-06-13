<?php


namespace aabc\bootstrap;

use aabc\base\InvalidConfigException;
use aabc\helpers\ArrayHelper;


class Collapse extends Widget
{
    
    public $items = [];
    
    public $encodeLabels = true;


    
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, ['widget' => 'panel-group']);
    }

    
    public function run()
    {
        $this->registerPlugin('collapse');
        return implode("\n", [
            Html::beginTag('div', $this->options),
            $this->renderItems(),
            Html::endTag('div')
        ]) . "\n";
    }

    
    public function renderItems()
    {
        $items = [];
        $index = 0;
        foreach ($this->items as $item) {
            if (!array_key_exists('label', $item)) {
                throw new InvalidConfigException("The 'label' option is required.");
            }
            $header = $item['label'];
            $options = ArrayHelper::getValue($item, 'options', []);
            Html::addCssClass($options, ['panel' => 'panel', 'widget' => 'panel-default']);
            $items[] = Html::tag('div', $this->renderItem($header, $item, ++$index), $options);
        }

        return implode("\n", $items);
    }

    
    public function renderItem($header, $item, $index)
    {
        if (array_key_exists('content', $item)) {
            $id = $this->options['id'] . '-collapse' . $index;
            $options = ArrayHelper::getValue($item, 'contentOptions', []);
            $options['id'] = $id;
            Html::addCssClass($options, ['widget' => 'panel-collapse', 'collapse' => 'collapse']);

            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            if ($encodeLabel) {
                $header = Html::encode($header);
            }

            $headerToggle = Html::a($header, '#' . $id, [
                    'class' => 'collapse-toggle',
                    'data-toggle' => 'collapse',
                    'data-parent' => '#' . $this->options['id']
                ]) . "\n";

            $header = Html::tag('h4', $headerToggle, ['class' => 'panel-title']);

            if (is_string($item['content']) || is_object($item['content'])) {
                $content = Html::tag('div', $item['content'], ['class' => 'panel-body']) . "\n";
            } elseif (is_array($item['content'])) {
                $content = Html::ul($item['content'], [
                    'class' => 'list-group',
                    'itemOptions' => [
                        'class' => 'list-group-item'
                    ],
                    'encode' => false,
                ]) . "\n";
                if (isset($item['footer'])) {
                    $content .= Html::tag('div', $item['footer'], ['class' => 'panel-footer']) . "\n";
                }
            } else {
                throw new InvalidConfigException('The "content" option should be a string, array or object.');
            }
        } else {
            throw new InvalidConfigException('The "content" option is required.');
        }
        $group = [];

        $group[] = Html::tag('div', $header, ['class' => 'panel-heading']);
        $group[] = Html::tag('div', $content, $options);

        return implode("\n", $group);
    }
}
