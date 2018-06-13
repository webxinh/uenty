<?php


namespace aabc\web;

use aabc\base\Object;
use aabc\helpers\ArrayHelper;
use aabc\helpers\StringHelper;


class MultipartFormDataParser extends Object implements RequestParserInterface
{
    
    private $_uploadFileMaxSize;
    
    private $_uploadFileMaxCount;


    
    public function getUploadFileMaxSize()
    {
        if ($this->_uploadFileMaxSize === null) {
            $this->_uploadFileMaxSize = $this->getByteSize(ini_get('upload_max_filesize'));
        }
        return $this->_uploadFileMaxSize;
    }

    
    public function setUploadFileMaxSize($uploadFileMaxSize)
    {
        $this->_uploadFileMaxSize = $uploadFileMaxSize;
    }

    
    public function getUploadFileMaxCount()
    {
        if ($this->_uploadFileMaxCount === null) {
            $this->_uploadFileMaxCount = ini_get('max_file_uploads');
        }
        return $this->_uploadFileMaxCount;
    }

    
    public function setUploadFileMaxCount($uploadFileMaxCount)
    {
        $this->_uploadFileMaxCount = $uploadFileMaxCount;
    }

    
    public function parse($rawBody, $contentType)
    {
        if (!empty($_POST) || !empty($_FILES)) {
            // normal POST request is parsed by PHP automatically
            return $_POST;
        }

        if (empty($rawBody)) {
            return [];
        }

        if (!preg_match('/boundary=(.*)$/is', $contentType, $matches)) {
            return [];
        }
        $boundary = $matches[1];

        $bodyParts = preg_split('/\\R?-+' . preg_quote($boundary) . '/s', $rawBody);
        array_pop($bodyParts); // last block always has no data, contains boundary ending like `--`

        $bodyParams = [];
        $filesCount = 0;
        foreach ($bodyParts as $bodyPart) {
            if (empty($bodyPart)) {
                continue;
            }
            list($headers, $value) = preg_split("/\\R\\R/", $bodyPart, 2);
            $headers = $this->parseHeaders($headers);
            
            if (!isset($headers['content-disposition']['name'])) {
                continue;
            }

            if (isset($headers['content-disposition']['filename'])) {
                // file upload:
                if ($filesCount >= $this->getUploadFileMaxCount()) {
                    continue;
                }

                $fileInfo = [
                    'name' => $headers['content-disposition']['filename'],
                    'type' => ArrayHelper::getValue($headers, 'content-type', 'application/octet-stream'),
                    'size' => StringHelper::byteLength($value),
                    'error' => UPLOAD_ERR_OK,
                    'tmp_name' => null,
                ];

                if ($fileInfo['size'] > $this->getUploadFileMaxSize()) {
                    $fileInfo['error'] = UPLOAD_ERR_INI_SIZE;
                } else {
                    $tmpResource = tmpfile();
                    if ($tmpResource === false) {
                        $fileInfo['error'] = UPLOAD_ERR_CANT_WRITE;
                    } else {
                        $tmpResourceMetaData = stream_get_meta_data($tmpResource);
                        $tmpFileName = $tmpResourceMetaData['uri'];
                        if (empty($tmpFileName)) {
                            $fileInfo['error'] = UPLOAD_ERR_CANT_WRITE;
                            @fclose($tmpResource);
                        } else {
                            fwrite($tmpResource, $value);
                            $fileInfo['tmp_name'] = $tmpFileName;
                            $fileInfo['tmp_resource'] = $tmpResource; // save file resource, otherwise it will be deleted
                        }
                    }
                }

                $this->addFile($_FILES, $headers['content-disposition']['name'], $fileInfo);

                $filesCount++;
            } else {
                // regular parameter:
                $this->addValue($bodyParams, $headers['content-disposition']['name'], $value);
            }
        }

        return $bodyParams;
    }

    
    private function parseHeaders($headerContent)
    {
        $headers = [];
        $headerParts = preg_split("/\\R/s", $headerContent, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($headerParts as $headerPart) {
            if (($separatorPos = strpos($headerPart, ':')) === false) {
                continue;
            }

            list($headerName, $headerValue) = explode(':', $headerPart, 2);
            $headerName = strtolower(trim($headerName));
            $headerValue = trim($headerValue);

            if (strpos($headerValue, ';') === false) {
                $headers[$headerName] = $headerValue;
            } else {
                $headers[$headerName] = [];
                foreach (explode(';', $headerValue) as $part) {
                    $part = trim($part);
                    if (strpos($part, '=') === false) {
                        $headers[$headerName][] = $part;
                    } else {
                        list($name, $value) = explode('=', $part, 2);
                        $name = strtolower(trim($name));
                        $value = trim(trim($value), '"');
                        $headers[$headerName][$name] = $value;
                    }
                }
            }
        }

        return $headers;
    }

    
    private function addValue(&$array, $name, $value)
    {
        $nameParts = preg_split('/\\]\\[|\\[/s', $name);
        $current = &$array;
        foreach ($nameParts as $namePart) {
            $namePart = trim($namePart, ']');
            if ($namePart === '') {
                $current[] = [];
                $lastKey = array_pop(array_keys($current));
                $current = &$current[$lastKey];
            } else {
                if (!isset($current[$namePart])) {
                    $current[$namePart] = [];
                }
                $current = &$current[$namePart];
            }
        }
        $current = $value;
    }

    
    private function addFile(&$files, $name, $info)
    {
        if (strpos($name, '[') === false) {
            $files[$name] = $info;
            return;
        }

        $fileInfoAttributes = [
            'name',
            'type',
            'size',
            'error',
            'tmp_name',
            'tmp_resource'
        ];

        $nameParts = preg_split('/\\]\\[|\\[/s', $name);
        $baseName = array_shift($nameParts);
        if (!isset($files[$baseName])) {
            $files[$baseName] = [];
            foreach ($fileInfoAttributes as $attribute) {
                $files[$baseName][$attribute] = [];
            }
        } else {
            foreach ($fileInfoAttributes as $attribute) {
                $files[$baseName][$attribute] = (array)$files[$baseName][$attribute];
            }
        }

        foreach ($fileInfoAttributes as $attribute) {
            if (!isset($info[$attribute])) {
                continue;
            }

            $current = &$files[$baseName][$attribute];
            foreach ($nameParts as $namePart) {
                $namePart = trim($namePart, ']');
                if ($namePart === '') {
                    $current[] = [];
                    $lastKey = array_pop(array_keys($current));
                    $current = &$current[$lastKey];
                } else {
                    if (!isset($current[$namePart])) {
                        $current[$namePart] = [];
                    }
                    $current = &$current[$namePart];
                }
            }
            $current = $info[$attribute];
        }
    }

    
    private function getByteSize($verboseSize)
    {
        if (empty($verboseSize)) {
            return 0;
        }
        if (is_numeric($verboseSize)) {
            return (int) $verboseSize;
        }
        $sizeUnit = trim($verboseSize, '0123456789');
        $size = str_replace($sizeUnit, '', $verboseSize);
        $size = trim($size);
        if (!is_numeric($size)) {
            return 0;
        }
        switch (strtolower($sizeUnit)) {
            case 'kb':
            case 'k':
                return $size * 1024;
            case 'mb':
            case 'm':
                return $size * 1024 * 1024;
            case 'gb':
            case 'g':
                return $size * 1024 * 1024 * 1024;
            default:
                return 0;
        }
    }
}
