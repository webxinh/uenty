<?php


namespace aabc\rest;

use Aabc;
use aabc\base\Arrayable;
use aabc\base\Component;
use aabc\base\Model;
use aabc\data\DataProviderInterface;
use aabc\data\Pagination;
use aabc\helpers\ArrayHelper;
use aabc\web\Link;
use aabc\web\Request;
use aabc\web\Response;


class Serializer extends Component
{
    
    public $fieldsParam = 'fields';
    
    public $expandParam = 'expand';
    
    public $totalCountHeader = 'X-Pagination-Total-Count';
    
    public $pageCountHeader = 'X-Pagination-Page-Count';
    
    public $currentPageHeader = 'X-Pagination-Current-Page';
    
    public $perPageHeader = 'X-Pagination-Per-Page';
    
    public $collectionEnvelope;
    
    public $linksEnvelope = '_links';
    
    public $metaEnvelope = '_meta';
    
    public $request;
    
    public $response;
    
    public $preserveKeys = false;


    
    public function init()
    {
        if ($this->request === null) {
            $this->request = Aabc::$app->getRequest();
        }
        if ($this->response === null) {
            $this->response = Aabc::$app->getResponse();
        }
    }

    
    public function serialize($data)
    {
        if ($data instanceof Model && $data->hasErrors()) {
            return $this->serializeModelErrors($data);
        } elseif ($data instanceof Arrayable) {
            return $this->serializeModel($data);
        } elseif ($data instanceof DataProviderInterface) {
            return $this->serializeDataProvider($data);
        } else {
            return $data;
        }
    }

    
    protected function getRequestedFields()
    {
        $fields = $this->request->get($this->fieldsParam);
        $expand = $this->request->get($this->expandParam);

        return [
            is_string($fields) ? preg_split('/\s*,\s*/', $fields, -1, PREG_SPLIT_NO_EMPTY) : [],
            is_string($expand) ? preg_split('/\s*,\s*/', $expand, -1, PREG_SPLIT_NO_EMPTY) : [],
        ];
    }

    
    protected function serializeDataProvider($dataProvider)
    {
        if ($this->preserveKeys) {
            $models = $dataProvider->getModels();
        } else {
            $models = array_values($dataProvider->getModels());
        }
        $models = $this->serializeModels($models);

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }

        if ($this->request->getIsHead()) {
            return null;
        } elseif ($this->collectionEnvelope === null) {
            return $models;
        } else {
            $result = [
                $this->collectionEnvelope => $models,
            ];
            if ($pagination !== false) {
                return array_merge($result, $this->serializePagination($pagination));
            } else {
                return $result;
            }
        }
    }

    
    protected function serializePagination($pagination)
    {
        return [
            $this->linksEnvelope => Link::serialize($pagination->getLinks(true)),
            $this->metaEnvelope => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->getPageCount(),
                'currentPage' => $pagination->getPage() + 1,
                'perPage' => $pagination->getPageSize(),
            ],
        ];
    }

    
    protected function addPaginationHeaders($pagination)
    {
        $links = [];
        foreach ($pagination->getLinks(true) as $rel => $url) {
            $links[] = "<$url>; rel=$rel";
        }

        $this->response->getHeaders()
            ->set($this->totalCountHeader, $pagination->totalCount)
            ->set($this->pageCountHeader, $pagination->getPageCount())
            ->set($this->currentPageHeader, $pagination->getPage() + 1)
            ->set($this->perPageHeader, $pagination->pageSize)
            ->set('Link', implode(', ', $links));
    }

    
    protected function serializeModel($model)
    {
        if ($this->request->getIsHead()) {
            return null;
        } else {
            list ($fields, $expand) = $this->getRequestedFields();
            return $model->toArray($fields, $expand);
        }
    }

    
    protected function serializeModelErrors($model)
    {
        $this->response->setStatusCode(422, 'Data Validation Failed.');
        $result = [];
        foreach ($model->getFirstErrors() as $name => $message) {
            $result[] = [
                'field' => $name,
                'message' => $message,
            ];
        }

        return $result;
    }

    
    protected function serializeModels(array $models)
    {
        list ($fields, $expand) = $this->getRequestedFields();
        foreach ($models as $i => $model) {
            if ($model instanceof Arrayable) {
                $models[$i] = $model->toArray($fields, $expand);
            } elseif (is_array($model)) {
                $models[$i] = ArrayHelper::toArray($model);
            }
        }

        return $models;
    }
}
