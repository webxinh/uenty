<?php
namespace Codeception\Module;

use Codeception\Lib\Interfaces\DataMapper;
use Codeception\Module as CodeceptionModule;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\TestInterface;
use Doctrine\ORM\EntityManagerInterface;
use Codeception\Util\Stub;



class Doctrine2 extends CodeceptionModule implements DependsOnModule, DataMapper
{

    protected $config = [
        'cleanup' => true,
        'connection_callback' => false,
        'depends' => null
    ];

    protected $dependencyMessage = <<<EOF
Provide connection_callback function to establish database connection and get Entity Manager:

modules:
    enabled:
        - Doctrine2:
            connection_callback: [My\ConnectionClass, getEntityManager]

Or set a dependent module, which can be either Symfony or ZF2 to get EM from service locator:

modules:
    enabled:
        - Doctrine2:
            depends: Symfony
EOF;

    
    public $em = null;

    
    private $dependentModule;

    public function _depends()
    {
        if ($this->config['connection_callback']) {
            return [];
        }
        return ['Codeception\Lib\Interfaces\DoctrineProvider' => $this->dependencyMessage];
    }

    public function _inject(DoctrineProvider $dependentModule = null)
    {
        $this->dependentModule = $dependentModule;
    }

    public function _beforeSuite($settings = [])
    {
        $this->retrieveEntityManager();
    }

    public function _before(TestInterface $test)
    {
        $this->retrieveEntityManager();
        if ($this->config['cleanup']) {
            $this->em->getConnection()->beginTransaction();
        }
    }

    protected function retrieveEntityManager()
    {
        if ($this->dependentModule) {
            $this->em = $this->dependentModule->_getEntityManager();
        } else {
            if (is_callable($this->config['connection_callback'])) {
                $this->em = call_user_func($this->config['connection_callback']);
            }
        }

        if (!$this->em) {
            throw new ModuleConfigException(
                __CLASS__,
                "EntityManager can't be obtained.\n \n"
                . "Please specify either `connection_callback` config option\n"
                . "with callable which will return instance of EntityManager or\n"
                . "pass a dependent module which are Symfony or ZF2\n"
                . "to connect to Doctrine using Dependency Injection Container"
            );
        }


        if (!($this->em instanceof \Doctrine\ORM\EntityManagerInterface)) {
            throw new ModuleConfigException(
                __CLASS__,
                "Connection object is not an instance of \\Doctrine\\ORM\\EntityManagerInterface.\n"
                . "Use `connection_callback` or dependent framework modules to specify one"
            );
        }

        $this->em->getConnection()->connect();
    }

    public function _after(TestInterface $test)
    {
        if (!$this->em instanceof \Doctrine\ORM\EntityManagerInterface) {
            return;
        }
        if ($this->config['cleanup'] && $this->em->getConnection()->isTransactionActive()) {
            try {
                $this->em->getConnection()->rollback();
            } catch (\PDOException $e) {
            }
        }
        $this->clean();
        $this->em->getConnection()->close();
    }

    protected function clean()
    {
        $em = $this->em;

        $reflectedEm = new \ReflectionClass($em);
        if ($reflectedEm->hasProperty('repositories')) {
            $property = $reflectedEm->getProperty('repositories');
            $property->setAccessible(true);
            $property->setValue($em, []);
        }
        $this->em->clear();
    }


    
    public function flushToDatabase()
    {
        $this->em->flush();
    }


    
    public function persistEntity($obj, $values = [])
    {
        if ($values) {
            $reflectedObj = new \ReflectionClass($obj);
            foreach ($values as $key => $val) {
                $property = $reflectedObj->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($obj, $val);
            }
        }

        $this->em->persist($obj);
        $this->em->flush();
    }

    
    public function haveFakeRepository($classname, $methods = [])
    {
        $em = $this->em;

        $metadata = $em->getMetadataFactory()->getMetadataFor($classname);
        $customRepositoryClassName = $metadata->customRepositoryClassName;

        if (!$customRepositoryClassName) {
            $customRepositoryClassName = '\Doctrine\ORM\EntityRepository';
        }

        $mock = Stub::make(
            $customRepositoryClassName,
            array_merge(
                [
                    '_entityName' => $metadata->name,
                    '_em' => $em,
                    '_class' => $metadata
                ],
                $methods
            )
        );
        $em->clear();
        $reflectedEm = new \ReflectionClass($em);
        if ($reflectedEm->hasProperty('repositories')) {
            $property = $reflectedEm->getProperty('repositories');
            $property->setAccessible(true);
            $property->setValue($em, array_merge($property->getValue($em), [$classname => $mock]));
        } else {
            $this->debugSection(
                'Warning',
                'Repository can\'t be mocked, the EventManager class doesn\'t have "repositories" property'
            );
        }
    }

    
    public function haveInRepository($entity, array $data)
    {
        $reflectedEntity = new \ReflectionClass($entity);
        $entityObject = $reflectedEntity->newInstance();
        foreach ($reflectedEntity->getProperties() as $property) {
            
            if (!isset($data[$property->name])) {
                continue;
            }
            $property->setAccessible(true);
            $property->setValue($entityObject, $data[$property->name]);
        }
        $this->em->persist($entityObject);
        $this->em->flush();

        if (method_exists($entityObject, 'getId')) {
            $id = $entityObject->getId();
            $this->debug("$entity entity created with id:$id");
            return $id;
        }
    }

    
    public function seeInRepository($entity, $params = [])
    {
        $res = $this->proceedSeeInRepository($entity, $params);
        $this->assert($res);
    }

    
    public function dontSeeInRepository($entity, $params = [])
    {
        $res = $this->proceedSeeInRepository($entity, $params);
        $this->assertNot($res);
    }

    protected function proceedSeeInRepository($entity, $params = [])
    {
        // we need to store to database...
        $this->em->flush();
        $data = $this->em->getClassMetadata($entity);
        $qb = $this->em->getRepository($entity)->createQueryBuilder('s');
        $this->buildAssociationQuery($qb, $entity, 's', $params);
        $this->debug($qb->getDQL());
        $res = $qb->getQuery()->getArrayResult();

        return ['True', (count($res) > 0), "$entity with " . json_encode($params)];
    }

    
    public function grabFromRepository($entity, $field, $params = [])
    {
        // we need to store to database...
        $this->em->flush();
        $data = $this->em->getClassMetadata($entity);
        $qb = $this->em->getRepository($entity)->createQueryBuilder('s');
        $qb->select('s.' . $field);
        $this->buildAssociationQuery($qb, $entity, 's', $params);
        $this->debug($qb->getDQL());
        return $qb->getQuery()->getSingleScalarResult();
    }

    
    protected function buildAssociationQuery($qb, $assoc, $alias, $params)
    {
        $data = $this->em->getClassMetadata($assoc);
        foreach ($params as $key => $val) {
            if (isset($data->associationMappings)) {
                if ($map = array_key_exists($key, $data->associationMappings)) {
                    if (is_array($val)) {
                        $qb->innerJoin("$alias.$key", $key);
                        foreach ($val as $column => $v) {
                            if (is_array($v)) {
                                $this->buildAssociationQuery($qb, $map['targetEntity'], $column, $v);
                                continue;
                            }
                            $paramname = $key . '__' . $column;
                            $qb->andWhere("$key.$column = :$paramname");
                            $qb->setParameter($paramname, $v);
                        }
                        continue;
                    }
                }
            }
            if ($val === null) {
                $qb->andWhere("s.$key IS NULL");
            } else {
                $paramname = str_replace(".", "", "s_$key");
                $qb->andWhere("s.$key = :$paramname");
                $qb->setParameter($paramname, $val);
            }
        }
    }

    public function _getEntityManager()
    {
        return $this->em;
    }
}
