<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Helper class that is used to import entities via entity manager.
 */
class Importer
{

    /**
     * The entity manager that is used to add the imported entities.
     *
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager = null;

    /**
     * Creates an importer that uses the provided entity manager.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Imports entities from the provided data source.
     *
     * The importer supports several ways to add entities to the database.
     * In any case the importer handles necessary flush() calls, therefore,
     * manual flushing is not necessary.
     *
     * # Callbacks #
     *
     * Callbacks are executed and receive an object manager as argument:
     *
     *     $loader = function (\Doctrine\Common\Persistence\ObjectManager $objectManager) {
     *         $objectManager->persist(new MyEntity());
     *         $objectManager->persist(new MyEntity());
     *     }
     *     $importer->import($loader);
     *
     * Please note, that an object manager and not the original entity manager is passed.
     *
     * # Single entities and lists of entities #
     *
     * Single entities and lists of entities are automatically persisted:
     *
     *     $importer->import(new MyEntity());
     *     $importer->import(array(new MyEntity(), new MyEntity()));
     *
     * # Files #
     *
     * To create re-usable data sets entities can be imported from PHP files:
     *
     *     $importer->import('/path/to/data/set.php');
     *
     * The imported file has access to the global variable $objectManager, which
     * can be used to persist the entities:
     *
     *     <?php
     *
     *     $objectManager->persist(new MyEntity());
     *     $objectManager->persist(new MyEntity());
     *
     * Alternatively, the file can return an array of entities that must be persisted.
     * This avoids the dependency on the global $objectManager variable:
     *
     *     <?php
     *
     *     return array(
     *         new MyEntity(),
     *         new MyEntity()
     *     );
     *
     * @param mixed $dataSource
     * @throws \InvalidArgumentException If the data source is not supported.
     */
    public function import($dataSource)
    {
        if (is_callable($dataSource)) {
            $this->importFromCallback($dataSource);
            return;
        }
        if (is_object($dataSource)) {
            $this->importEntity($dataSource);
            return;
        }
        if (is_array($dataSource)) {
            $this->importEntityList($dataSource);
            return;
        }
        if (is_file($dataSource)) {
            $this->importFromFile($dataSource);
            return;
        }
        $message = 'Cannot handle data source of type "' . gettype($dataSource) . '".';
        throw new \InvalidArgumentException($message);
    }

    /**
     * Imports a single entity.
     *
     * @param object $entity
     */
    protected function importEntity($entity)
    {
        $this->importEntityList(array($entity));
    }

    /**
     * Imports a list of entities.
     *
     * @param array(object) $entities
     */
    protected function importEntityList(array $entities)
    {
        $this->importFromCallback(function (ObjectManager $objectManager) use ($entities) {
            foreach ($entities as $entity) {
                /* @var $entity object */
                $objectManager->persist($entity);
            }
        });
    }

    /**
     * Imports entities from a PHP file.
     *
     * @param string $path
     */
    protected function importFromFile($path)
    {
        $entities = null;
        /* @noinspection PhpUnusedParameterInspection $objectManager should be in the scope of the included file. */
        $this->importFromCallback(function (ObjectManager $objectManager) use ($path, &$entities) {
            $entities = include $path;
        });
        if (is_array($entities)) {
            // Persist entities that were returned by the file.
            $this->importEntityList($entities);
        }
    }

    /**
     * Uses the provided callback to import entities.
     *
     * @param mixed $callback
     */
    protected function importFromCallback($callback)
    {
        $import = function (ObjectManager $objectManager) use ($callback) {
            $decorator = new DetachingObjectManagerDecorator($objectManager);
            call_user_func($callback, $decorator);
            // Flush manually to ensure that persisted entities are detached.
            $decorator->flush();
        };
        $this->entityManager->transactional($import);
    }

}
