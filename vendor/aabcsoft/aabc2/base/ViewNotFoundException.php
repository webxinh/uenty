<?php


namespace aabc\base;


class ViewNotFoundException extends InvalidParamException
{
    
    public function getName()
    {
        return 'View not Found';
    }
}
