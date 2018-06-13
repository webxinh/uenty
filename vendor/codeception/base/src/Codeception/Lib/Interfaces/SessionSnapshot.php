<?php
namespace Codeception\Lib\Interfaces;

interface SessionSnapshot
{
    
    public function saveSessionSnapshot($name);

    
    public function loadSessionSnapshot($name);
}
