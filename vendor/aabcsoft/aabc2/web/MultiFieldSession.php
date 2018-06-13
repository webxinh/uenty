<?php


namespace aabc\web;


abstract class MultiFieldSession extends Session
{
    
    public $readCallback;
    
    public $writeCallback;


    
    public function getUseCustomStorage()
    {
        return true;
    }

    
    protected function composeFields($id, $data)
    {
        $fields = [
            'data' => $data,
        ];
        if ($this->writeCallback !== null) {
            $fields = array_merge(
                $fields,
                call_user_func($this->writeCallback, $this)
            );
            if (!is_string($fields['data'])) {
                $_SESSION = $fields['data'];
                $fields['data'] = session_encode();
            }
        }
        // ensure 'id' and 'expire' are never affected by [[writeCallback]]
        $fields = array_merge($fields, [
            'id' => $id,
            'expire' => time() + $this->getTimeout(),
        ]);
        return $fields;
    }

    
    protected function extractData($fields)
    {
        if ($this->readCallback !== null) {
            if (!isset($fields['data'])) {
                $fields['data'] = '';
            }
            $extraData = call_user_func($this->readCallback, $fields);
            if (!empty($extraData)) {
                session_decode($fields['data']);
                $_SESSION = array_merge((array)$_SESSION, (array)$extraData);
                return session_encode();
            }
            return $fields['data'];
        } else {
            return isset($fields['data']) ? $fields['data'] : '';
        }
    }
}
