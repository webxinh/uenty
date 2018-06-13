<?php

namespace Faker\ORM\Spot;

use Faker\Generator;
use Faker\Guesser\Name;
use Spot\Locator;
use Spot\Mapper;
use Spot\Relation\BelongsTo;


class EntityPopulator
{
    
    const RELATED_FETCH_COUNT = 10;

    
    protected $mapper;

    
    protected $locator;

    
    protected $columnFormatters = array();
    
    protected $modifiers = array();

    
    protected $useExistingData = false;

    
    public function __construct(Mapper $mapper, Locator $locator, $useExistingData = false)
    {
        $this->mapper = $mapper;
        $this->locator = $locator;
        $this->useExistingData = $useExistingData;
    }

    
    public function getMapper()
    {
        return $this->mapper;
    }

    
    public function setColumnFormatters($columnFormatters)
    {
        $this->columnFormatters = $columnFormatters;
    }

    
    public function getColumnFormatters()
    {
        return $this->columnFormatters;
    }

    
    public function mergeColumnFormattersWith($columnFormatters)
    {
        $this->columnFormatters = array_merge($this->columnFormatters, $columnFormatters);
    }

    
    public function setModifiers(array $modifiers)
    {
        $this->modifiers = $modifiers;
    }

    
    public function getModifiers()
    {
        return $this->modifiers;
    }

    
    public function mergeModifiersWith(array $modifiers)
    {
        $this->modifiers = array_merge($this->modifiers, $modifiers);
    }

    
    public function guessColumnFormatters(Generator $generator)
    {
        $formatters = array();
        $nameGuesser = new Name($generator);
        $columnTypeGuesser = new ColumnTypeGuesser($generator);
        $fields = $this->mapper->fields();
        foreach ($fields as $fieldName => $field) {
            if ($field['primary'] === true) {
                continue;
            }
            if ($formatter = $nameGuesser->guessFormat($fieldName)) {
                $formatters[$fieldName] = $formatter;
                continue;
            }
            if ($formatter = $columnTypeGuesser->guessFormat($field)) {
                $formatters[$fieldName] = $formatter;
                continue;
            }
        }
        $entityName = $this->mapper->entity();
        $entity = $this->mapper->build([]);
        $relations = $entityName::relations($this->mapper, $entity);
        foreach ($relations as $relation) {
            // We don't need any other relation here.
            if ($relation instanceof BelongsTo) {

                $fieldName = $relation->localKey();
                $entityName = $relation->entityName();
                $field = $fields[$fieldName];
                $required = $field['required'];

                $locator = $this->locator;

                $formatters[$fieldName] = function ($inserted) use ($required, $entityName, $locator) {
                    if (!empty($inserted[$entityName])) {
                        return $inserted[$entityName][mt_rand(0, count($inserted[$entityName]) - 1)]->getId();
                    } else {
                        if ($required && $this->useExistingData) {
                            // We did not add anything like this, but it's required,
                            // So let's find something existing in DB.
                            $mapper = $this->locator->mapper($entityName);
                            $records = $mapper->all()->limit(self::RELATED_FETCH_COUNT)->toArray();
                            if (empty($records)) {
                                return null;
                            }
                            $id = $records[mt_rand(0, count($records) - 1)]['id'];

                            return $id;
                        } else {
                            return null;
                        }
                    }
                };

            }
        }

        return $formatters;
    }

    
    public function execute($insertedEntities)
    {
        $obj = $this->mapper->build([]);

        $this->fillColumns($obj, $insertedEntities);
        $this->callMethods($obj, $insertedEntities);

        $this->mapper->insert($obj);


        return $obj;
    }

    
    private function fillColumns($obj, $insertedEntities)
    {
        foreach ($this->columnFormatters as $field => $format) {
            if (null !== $format) {
                $value = is_callable($format) ? $format($insertedEntities, $obj) : $format;
                $obj->set($field, $value);
            }
        }
    }

    
    private function callMethods($obj, $insertedEntities)
    {
        foreach ($this->getModifiers() as $modifier) {
            $modifier($obj, $insertedEntities);
        }
    }
}
