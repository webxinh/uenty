<?php

namespace phpDocumentor\Reflection;


interface ProjectFactory
{
    
    public function create($name, array $files);
}
