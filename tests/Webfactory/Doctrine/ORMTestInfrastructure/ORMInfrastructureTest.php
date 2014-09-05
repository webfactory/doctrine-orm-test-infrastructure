<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure;

/**
 * Tests the infrastructure.
 */
class ORMInfrastructureTest extends \PHPUnit_Framework_TestCase
{

    /**
     * System under test.
     *
     * @var \Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure
     */
    protected $infrastructure = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->infrastructure = new ORMInfrastructure(array(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        ));
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->infrastructure = null;
        parent::tearDown();
    }

    /**
     * Checks if getEntityManager() returns the Doctrine entity manager,
     */
    public function testGetEntityManagerReturnsDoctrineEntityManager()
    {
        $entityManager = $this->infrastructure->getEntityManager();

        $this->assertInstanceOf('Doctrine\ORM\EntityManager', $entityManager);
    }

    /**
     * Ensures that getRepository() returns the Doctrine repository that belongs
     * to the given entity class.
     */
    public function testGetRepositoryReturnsRepositoryThatBelongsToEntity()
    {
        $repository = $this->infrastructure->getRepository(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        );

        $this->assertInstanceOf(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityRepository',
            $repository
        );
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
     * Checks if an imported entity receives a generated ID.
     */
    public function testEntityIdIsAvailableAfterImport()
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
 