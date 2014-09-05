<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure;

/**
 * Tests the infrastructure.
 */
class ORMInfrastructureTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Checks if getEntityManager() returns the Doctrine entity manager,
     */
    public function testGetEntityManagerReturnsDoctrineEntityManager()
    {

    }

    /**
     * Ensures that getRepository() returns the Doctrine repository that belongs
     * to the given entity class.
     */
    public function testGetRepositoryReturnsRepositoryThatBelongsToEntity()
    {

    }

    /**
     * Checks if import() adds entities to the database.
     *
     * There are different options to import entities, but these are handled in detail
     * in the importer tests.
     *
     * @see \Webfactory\Doctrine\ORMTestInfrastructure\ImporterTest
     */
    public function testImportAddsEntities()
    {

    }

    /**
     * Ensures that entities with non-Doctrine annotations can be used.
     */
    public function testInfrastructureCanUseEntitiesWithNonDoctrineAnnotations()
    {

    }

    /**
     * Ensures that different infrastructure instances provide database isolation.
     */
    public function testDifferentInfrastructureInstancesUseSeparatedDatabases()
    {

    }

}
 