<?php

namespace Faker\ORM\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;


class Populator
{
    protected $generator;
    protected $manager;
    protected $entities = array();
    protected $quantities = array();
    protected $generateId = array();

    
    public function __construct(\Faker\Generator $generator, ObjectManager $manager = null)
    {
        $this->generator = $generator;
        $this->manager = $manager;
    }

    
    public function addEntity($entity, $number, $customColumnFormatters = array(), $customModifiers = array(), $generateId = false)
    {
        if (!$entity instanceof \Faker\ORM\Doctrine\EntityPopulator) {
            if (null === $this->manager) {
                throw new \InvalidArgumentException("No entity manager passed to Doctrine Populator.");
            }
            $entity = new \Faker\ORM\Doctrine\EntityPopulator($this->manager->getClassMetadata($entity));
        }
        $entity->setColumnFormatters($entity->guessColumnFormatters($this->generator));
        if ($customColumnFormatters) {
            $entity->mergeColumnFormattersWith($customColumnFormatters);
        }
        $entity->mergeModifiersWith($customModifiers);
        $this->generateId[$entity->getClass()] = $generateId;

        $class = $entity->getClass();
        $this->entities[$class] = $entity;
        $this->quantities[$class] = $number;
    }

    
    public function execute($entityManager = null)
    {
        if (null === $entityManager) {
            $entityManager = $this->manager;
        }
        if (null === $entityManager) {
            throw new \InvalidArgumentException("No entity manager passed to Doctrine Populator.");
        }

        $insertedEntities = array();
        foreach ($this->quantities as $class => $number) {
            $generateId = $this->generateId[$class];
            for ($i=0; $i < $number; $i++) {
                $insertedEntities[$class][]= $this->entities[$class]->execute($entityManager, $insertedEntities, $generateId);
            }
            $entityManager->flush();
        }

        return $insertedEntities;
    }
}
