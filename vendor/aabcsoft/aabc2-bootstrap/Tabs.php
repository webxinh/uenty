<?php


namespace aabc\bootstrap;

use aabc\base\InvalidConfigException;
use aabc\helpers\ArrayHelper;


class Tabs extends Widget
{
    
    public $items = [];
    
    public $itemOptions = [];
    
    public $headerOptions = [];
    
    public $linkOptions = [];
    
    public $encodeLabels = true;
    
    public $navType = 'nav-tabs';
    
    public $renderTabContent = true;


    
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, ['widget' => 'nav', $this->navType]);
    }

    
    public function run()
    {
        $this->registerPlugin('tab');
        return $this->renderItems();
    }

    
    protected function renderItems()
    {
        $headers = [];
        $panes = [];

        if (!$this->hasActiveTab() && !empty($this->items)) {
            $this->items[0]['active'] = true;
        }

        foreach ($this->items as $n => $item) {
            if (!ArrayHelper::remove($item, 'visible', true)) {
                continue;
            }
            if (!array_key_exists('label', $item)) {
                throw new InvalidConfigException("The 'label' option is required.");
            }
            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            $label = $encodeLabel ? Html::encode($item['label']) : $item['label'];
            $headerOptions = array_merge($this->headerOptions, ArrayHelper::getValue($item, 'headerOptions', []));
            $linkOptions = array_merge($this->linkOptions, ArrayHelper::getValue($item, 'linkOptions', []));

            if (isset($item['items'])) {
                $label .= ' <b class="caret"></b>';
                Html::addCssClass($headerOptions, ['widget' => 'dropdown']);

                if ($this->renderDropdown($n, $item['items'], $panes)) {
                    Html::addCssClass($headerOptions, 'active');
                }

                Html::addCssClass($linkOptions, ['widget' => 'dropdown-toggle']);
                if (!isset($linkOptions['data-toggle'])) {
                    $linkOptions['data-toggle'] = 'dropdown';
                }
                $header = Html::a($label, "#", $linkOptions) . "\n"
                    . Dropdown::widget(['items' => $item['items'], 'clientOptions' => false, 'view' => $this->getView()]);
            } else {
                $options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
                $options['id'] = ArrayHelper::getValue($options, 'id', $this->options['id'] . '-tab' . $n);

                Html::addCssClass($options, ['widget' => 'tab-pane']);
                if (ArrayHelper::remove($item, 'active')) {
                    Html::addCssClass($options, 'active');
                    Html::addCssClass($headerOptions, 'active');
                }

                if (isset($item['url'])) {
                    $header = Html::a($label, $item['url'], $linkOptions);
                } else {
                    if (!isset($linkOptions['data-toggle'])) {
                        $linkOptions['data-toggle'] = 'tab';
                    }
                    $header = Html::a($label, '#' . $options['id'], $linkOptions);
                }

                if ($this->renderTabContent) {
                    $tag = ArrayHelper::remove($options, 'tag', 'div');
                    $panes[] = Html::tag($tag, isset($item['content']) ? $item['content'] : '', $options);
                }
            }

            $headers[] = Html::tag('li', $header, $headerOptions);
        }

        return Html::tag('ul', implode("\n", $headers), $this->options)
        . ($this->renderTabContent ? "\n" . Html::tag('div', implode("\n", $panes), ['class' => 'tab-content']) : '');
    }

    
    protected function hasActiveTab()
    {
        foreach ($this->items as $item) {
            if (isset($item['active']) && $item['active'] === true) {
                return true;
            }
        }

        return false;
    }

    
    protected function renderDropdown($itemNumber, &$items, &$panes)
    {
        $itemActive = false;

        foreach ($items as $n => &$item) {
            if (is_string($item)) {
                continue;
            }
            if (isset($item['visible']) && !$item['visible']) {
                continue;
            }
            if (!array_key_exists('content', $item)) {
                throw new InvalidConfigException("The 'content' option is required.");
            }

            $content = ArrayHelper::remove($item, 'content');
            $options = ArrayHelper::remove($item, 'contentOptions', []);
            Html::addCssClass($options, ['widget' => 'tab-pane']);
            if (ArrayHelper::remove($item, 'active')) {
                Html::addCssClass($options, 'active');
                Html::addCssClass($item['options'], 'active');
                $itemActive = true;
            }

            $options['id'] = ArrayHelper::getValue($options, 'id', $this->options['id'] . '-dd' . $itemNumber . '-tab' . $n);
            $item['url'] = '#' . $options['id'];
            if (!isset($item['linkOptions']['data-toggle'])) {
                $item['linkOptions']['data-toggle'] = 'tab';
            }
            $panes[] = Html::tag('div', $content, $options);

            unset($item);
        }

        return $itemActive;
    }
}
