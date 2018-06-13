<?php


namespace aabc\data;

use Aabc;
use aabc\base\Object;
use aabc\web\Link;
use aabc\web\Linkable;
use aabc\web\Request;


class Pagination extends Object implements Linkable
{
    const LINK_NEXT = 'next';
    const LINK_PREV = 'prev';
    const LINK_FIRST = 'first';
    const LINK_LAST = 'last';

    
    public $pageParam = 'p';
    
    public $pageSizeParam = 't';
    //public $pageSizeParam = 'perpage';
    
    public $forcePageParam = true;
    
    public $route;
    
    public $params;
    
    public $urlManager;
    
    public $validatePage = true;
    
    public $totalCount = 0;
    
    public $defaultPageSize = 20;
    
    public $pageSizeLimit = [1, 50];

    
    private $_pageSize;


    
    public function getPageCount()
    {
        $pageSize = $this->getPageSize();
        if ($pageSize < 1) {
            return $this->totalCount > 0 ? 1 : 0;
        } else {
            $totalCount = $this->totalCount < 0 ? 0 : (int) $this->totalCount;

            return (int) (($totalCount + $pageSize - 1) / $pageSize);
        }
    }

    private $_page;

    
    public function getPage($recalculate = false)
    {
        if ($this->_page === null || $recalculate) {
            $page = (int) $this->getQueryParam($this->pageParam, 1) - 1;
            $this->setPage($page, true);
        }

        return $this->_page;
    }

    
    public function setPage($value, $validatePage = false)
    {
        if ($value === null) {
            $this->_page = null;
        } else {
            $value = (int) $value;
            if ($validatePage && $this->validatePage) {
                $pageCount = $this->getPageCount();
                if ($value >= $pageCount) {
                    $value = $pageCount - 1;
                }
            }
            if ($value < 0) {
                $value = 0;
            }
            $this->_page = $value;
        }
    }

    
    public function getPageSize()
    {
        if ($this->_pageSize === null) {
            if (empty($this->pageSizeLimit)) {
                $pageSize = $this->defaultPageSize;
                $this->setPageSize($pageSize);
            } else {
                $pageSize = (int) $this->getQueryParam($this->pageSizeParam, $this->defaultPageSize);
                $this->setPageSize($pageSize, true);
            }
        }

        return $this->_pageSize;
    }

    
    public function setPageSize($value, $validatePageSize = false)
    {
        if ($value === null) {
            $this->_pageSize = null;
        } else {
            $value = (int) $value;
            if ($validatePageSize && isset($this->pageSizeLimit[0], $this->pageSizeLimit[1]) && count($this->pageSizeLimit) === 2) {
                if ($value < $this->pageSizeLimit[0]) {
                    $value = $this->pageSizeLimit[0];
                } elseif ($value > $this->pageSizeLimit[1]) {
                    $value = $this->pageSizeLimit[1];
                }
            }
            $this->_pageSize = $value;
        }
    }

    
    public function createUrl($page, $pageSize = null, $absolute = false)
    {
        $page = (int) $page;
        $pageSize = (int) $pageSize;



        if (($params = $this->params) === null) {
            $request = Aabc::$app->getRequest();
            $params = $request instanceof Request ? $request->getQueryParams() : [];
        }
        if ($page > 0 || $page == 0 && $this->forcePageParam) {
            $params[$this->pageParam] = $page + 1;
        } else {
            unset($params[$this->pageParam]);
        }
        if ($pageSize <= 0) {
            $pageSize = $this->getPageSize();
        }
        if ($pageSize != $this->defaultPageSize) {
            $params[$this->pageSizeParam] = $pageSize;
        } else {
            unset($params[$this->pageSizeParam]);
        }
        $params[0] = $this->route === null ? Aabc::$app->controller->getRoute() : $this->route;
        $urlManager = $this->urlManager === null ? Aabc::$app->getUrlManager() : $this->urlManager;

        
        if ($absolute) {
            return $urlManager->createAbsoluteUrl($params);
        } else {
            return $urlManager->createUrl($params);
        }
    }

    
    public function getOffset()
    {
        $pageSize = $this->getPageSize();

        return $pageSize < 1 ? 0 : $this->getPage() * $pageSize;
    }

    
    public function getLimit()
    {
        $pageSize = $this->getPageSize();

        return $pageSize < 1 ? -1 : $pageSize;
    }

    
    public function getLinks($absolute = false)
    {
        $currentPage = $this->getPage();
        $pageCount = $this->getPageCount();
        $links = [
            Link::REL_SELF => $this->createUrl($currentPage, null, $absolute),
        ];
        if ($currentPage > 0) {
            $links[self::LINK_FIRST] = $this->createUrl(0, null, $absolute);
            $links[self::LINK_PREV] = $this->createUrl($currentPage - 1, null, $absolute);
        }
        if ($currentPage < $pageCount - 1) {
            $links[self::LINK_NEXT] = $this->createUrl($currentPage + 1, null, $absolute);
            $links[self::LINK_LAST] = $this->createUrl($pageCount - 1, null, $absolute);
        }

        return $links;
    }

    
    protected function getQueryParam($name, $defaultValue = null)
    {
        if (($params = $this->params) === null) {
            $request = Aabc::$app->getRequest();
            $params = $request instanceof Request ? $request->getQueryParams() : [];
        }

        return isset($params[$name]) && is_scalar($params[$name]) ? $params[$name] : $defaultValue;
    }
}
