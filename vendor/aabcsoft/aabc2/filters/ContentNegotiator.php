<?php


namespace aabc\filters;

use Aabc;
use aabc\base\ActionFilter;
use aabc\base\BootstrapInterface;
use aabc\base\InvalidConfigException;
use aabc\web\Response;
use aabc\web\Request;
use aabc\web\UnsupportedMediaTypeHttpException;


class ContentNegotiator extends ActionFilter implements BootstrapInterface
{
    
    public $formatParam = '_format';
    
    public $languageParam = '_lang';
    
    public $formats;
    
    public $languages;
    
    public $request;
    
    public $response;


    
    public function bootstrap($app)
    {
        $this->negotiate();
    }

    
    public function beforeAction($action)
    {
        $this->negotiate();
        return true;
    }

    
    public function negotiate()
    {
        $request = $this->request ? : Aabc::$app->getRequest();
        $response = $this->response ? : Aabc::$app->getResponse();
        if (!empty($this->formats)) {
            $this->negotiateContentType($request, $response);
        }
        if (!empty($this->languages)) {
            Aabc::$app->language = $this->negotiateLanguage($request);
        }
    }

    
    protected function negotiateContentType($request, $response)
    {
        if (!empty($this->formatParam) && ($format = $request->get($this->formatParam)) !== null) {
            if (in_array($format, $this->formats)) {
                $response->format = $format;
                $response->acceptMimeType = null;
                $response->acceptParams = [];
                return;
            } else {
                throw new UnsupportedMediaTypeHttpException('The requested response format is not supported: ' . $format);
            }
        }

        $types = $request->getAcceptableContentTypes();
        if (empty($types)) {
            $types['*/*'] = [];
        }

        foreach ($types as $type => $params) {
            if (isset($this->formats[$type])) {
                $response->format = $this->formats[$type];
                $response->acceptMimeType = $type;
                $response->acceptParams = $params;
                return;
            }
        }

        if (isset($types['*/*'])) {
            // return the first format
            foreach ($this->formats as $type => $format) {
                $response->format = $this->formats[$type];
                $response->acceptMimeType = $type;
                $response->acceptParams = [];
                return;
            }
        }

        throw new UnsupportedMediaTypeHttpException('None of your requested content types is supported.');
    }

    
    protected function negotiateLanguage($request)
    {
        if (!empty($this->languageParam) && ($language = $request->get($this->languageParam)) !== null) {
            if (isset($this->languages[$language])) {
                return $this->languages[$language];
            }
            foreach ($this->languages as $key => $supported) {
                if (is_int($key) && $this->isLanguageSupported($language, $supported)) {
                    return $supported;
                }
            }
            return reset($this->languages);
        }

        foreach ($request->getAcceptableLanguages() as $language) {
            if (isset($this->languages[$language])) {
                return $this->languages[$language];
            }
            foreach ($this->languages as $key => $supported) {
                if (is_int($key) && $this->isLanguageSupported($language, $supported)) {
                    return $supported;
                }
            }
        }

        return reset($this->languages);
    }

    
    protected function isLanguageSupported($requested, $supported)
    {
        $supported = str_replace('_', '-', strtolower($supported));
        $requested = str_replace('_', '-', strtolower($requested));
        return strpos($requested . '-', $supported . '-') === 0;
    }
}
