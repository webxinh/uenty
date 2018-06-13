<?php

namespace Faker\ORM\Mandango;

use Mandango\Mandango;


class Populator
{
    protected $generator;
    protected $mandango;
    protected $entities = array();
    protected $quantities = array();

    
    public function __construct(\Faker\Generator $generator, Mandango $mandango)
    {
        $this->generator = $generator;
        $this->mandango = $mandango;
    }

    
    public function addEntity($entity, $number, $customColumnFormatters = array())
    {
        if (!$entity instanceof \Faker\ORM\Mandango\EntityPopulator) {
            $entity = new \Faker\ORM\Mandango\EntityPopulator($entity);
        }
        $entity->setColumnFormatters($entity->guessColumnFormatters($this->generator, $this->mandango));
        if ($customColumnFormatters) {
            $entity->mergeColumnFormattersWith($customColumnFormatters);
        }
        $class = $entity->getClass();
        $this->entities[$class] = $entity;
        $this->quantities[$class] = $number;
    }

    
    public function execute()
    {
        $insertedEntities = array();
        foreach ($this->quantities as $class => $number) {
            for ($i=0; $i < $number; $i++) {
                $insertedEntities[$class][]= $this->entities[$class]->execute($this->mandango, $insertedEntities);
            }
        }
        $this->mandango->flush();

        return $insertedEntities;
    }
}
