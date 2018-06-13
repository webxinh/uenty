<?php


namespace aabc\web;

use aabc\base\Component;


class HtmlResponseFormatter extends Component implements ResponseFormatterInterface
{
    
    public $contentType = 'text/html';


    
    public function format($response)
    {
        if (stripos($this->contentType, 'charset') === false) {
            $this->contentType .= '; charset=' . $response->charset;
        }
        $response->getHeaders()->set('Content-Type', $this->contentType);
        if ($response->data !== null) {
            $response->content = $response->data;
        }
    }
}
