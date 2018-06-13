<?php


namespace aabc\web;

use Aabc;
use aabc\base\Component;
use aabc\helpers\Json;


class JsonResponseFormatter extends Component implements ResponseFormatterInterface
{
    
    public $useJsonp = false;
    
    public $encodeOptions = 320;
    
    public $prettyPrint = false;


    
    public function format($response)
    {
        if ($this->useJsonp) {
            $this->formatJsonp($response);
        } else {
            $this->formatJson($response);
        }
    }

    
    protected function formatJson($response)
    {
        $response->getHeaders()->set('Content-Type', 'application/json; charset=UTF-8');
        if ($response->data !== null) {
            $options = $this->encodeOptions;
            if ($this->prettyPrint) {
                $options |= JSON_PRETTY_PRINT;
            }
            $response->content = Json::encode($response->data, $options);
        }
    }

    
    protected function formatJsonp($response)
    {
        $response->getHeaders()->set('Content-Type', 'application/javascript; charset=UTF-8');
        if (is_array($response->data) && isset($response->data['data'], $response->data['callback'])) {
            $response->content = sprintf('%s(%s);', $response->data['callback'], Json::htmlEncode($response->data['data']));
        } elseif ($response->data !== null) {
            $response->content = '';
            Aabc::warning("The 'jsonp' response requires that the data be an array consisting of both 'data' and 'callback' elements.", __METHOD__);
        }
    }
}
