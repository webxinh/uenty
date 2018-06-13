<?php


namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

interface Strategy
{
    public function create($body);
}
