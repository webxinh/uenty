<?php


namespace aabc\console;


class Request extends \aabc\base\Request
{
    private $_params;


    
    public function getParams()
    {
        if ($this->_params === null) {
            if (isset($_SERVER['argv'])) {
                $this->_params = $_SERVER['argv'];
                array_shift($this->_params);
            } else {
                $this->_params = [];
            }
        }

        return $this->_params;
    }

    
    public function setParams($params)
    {
        $this->_params = $params;
    }

    
    public function resolve()
    {
        $rawParams = $this->getParams();
        $endOfOptionsFound = false;
        if (isset($rawParams[0])) {
            $route = array_shift($rawParams);

            if ($route === '--') {
                $endOfOptionsFound = true;
                $route = array_shift($rawParams);
            }
        } else {
            $route = '';
        }

        $params = [];
        foreach ($rawParams as $param) {
            if ($endOfOptionsFound) {
                $params[] = $param;
            } elseif ($param === '--') {
                $endOfOptionsFound = true;
            } elseif (preg_match('/^--(\w+)(?:=(.*))?$/', $param, $matches)) {
                $name = $matches[1];
                if ($name !== Application::OPTION_APPCONFIG) {
                    $params[$name] = isset($matches[2]) ? $matches[2] : true;
                }
            } elseif (preg_match('/^-(\w+)(?:=(.*))?$/', $param, $matches)) {
                $name = $matches[1];
                if (is_numeric($name)) {
                    $params[] = $param;
                } else {
                    $params['_aliases'][$name] = isset($matches[2]) ? $matches[2] : true;
                }
            } else {
                $params[] = $param;
            }
        }

        return [$route, $params];
    }
}
