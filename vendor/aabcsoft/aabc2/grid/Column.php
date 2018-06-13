<?php


namespace aabc\grid;

use Closure;
use aabc\base\Object;
use aabc\helpers\Html;


class Column extends Object
{
    
    public $grid;
    
    public $header;
    
    public $footer;
    
    public $content;
    
    public $visible = true;
    
    public $options = [];
    
    public $headerOptions = [];
    
    public $contentOptions = [];
    
    public $footerOptions = [];
    
    public $filterOptions = [];


    
    public function renderHeaderCell()
    {
        return Html::tag('th', $this->renderHeaderCellContent(), $this->headerOptions);
    }

    
    public function renderFooterCell()
    {
        return Html::tag('td', $this->renderFooterCellContent(), $this->footerOptions);
    }

    
    public function renderDataCell($model, $key, $index)
    {
        if ($this->contentOptions instanceof Closure) {
            $options = call_user_func($this->contentOptions, $model, $key, $index, $this);
        } else {
            $options = $this->contentOptions;
        }
        return Html::tag('td', $this->renderDataCellContent($model, $key, $index), $options);
    }

    
    public function renderFilterCell()
    {
        return Html::tag('td', $this->renderFilterCellContent(), $this->filterOptions);
    }

    
    protected function renderHeaderCellContent()
    {
        return trim($this->header) !== '' ? $this->header : $this->getHeaderCellLabel();
    }

    
    protected function getHeaderCellLabel()
    {
        return $this->grid->emptyCell;
    }

    
    protected function renderFooterCellContent()
    {
        return trim($this->footer) !== '' ? $this->footer : $this->grid->emptyCell;
    }

    
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return call_user_func($this->content, $model, $key, $index, $this);
        } else {
            return $this->grid->emptyCell;
        }
    }

    
    protected function renderFilterCellContent()
    {
        return $this->grid->emptyCell;
    }
}
