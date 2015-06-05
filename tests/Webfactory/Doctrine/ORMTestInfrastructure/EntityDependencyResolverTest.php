<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

/**
 * Tests the entity resolver.
 */
class EntityDependencyResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the resolver is traversable.
     */
    public function testResolverIsTraversable()
    {
        $resolver = new EntityDependencyResolver(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        ));

        $this->assertInstanceOf('\Traversable', $resolver);
    }

    /**
     * Checks if the resolved set contains the initially provided entity classes.
     */
    public function testSetContainsProvidedEntityClasses()
    {
        $resolver = new EntityDependencyResolver(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        ));

        $this->assertContainsEntity(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity',
            $resolver
        );
    }

    /**
     * Checks if entities that are directly associated to the initially provided entities
     * are contained in the resolved set.
     */
    public function testSetContainsEntityClassesThatAreDirectlyConnectedToInitialSet()
    {
        $resolver = new EntityDependencyResolver(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityWithDependency'
        ));

        $this->assertContainsEntity(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity',
            $resolver
        );
    }

    /**
     * Ensures that entities, which are connected via other associated entities,
     * are contained in the generated set.
     *
     * Example:
     *
     *     A -> B -> C
     */
    public function testSetContainsIndirectlyConnectedEntityClasses()
    {
        $resolver = new EntityDependencyResolver(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ChainReferenceEntity'
        ));

        $this->assertContainsEntity(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity',
            $resolver
        );
    }

    /**
     * Ensures that the resolver can handle dependency cycles.
     */
    public function testResolverCanHandleDependencyCycles()
    {
        $resolver = new EntityDependencyResolver(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferenceCycleEntity'
        ));

        $this->assertContainsEntity(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferenceCycleEntity',
            $resolver
        );
    }

    /**
     * Ensures that the resolved entity list contains each entity class only once.
     */
    public function testSetContainsEntitiesOnlyOnce()
    {
        $resolver = new EntityDependencyResolver(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferenceCycleEntity'
        ));

        $resolvedSet = $this->getResolvedSet($resolver);

        $normalized = array_unique($resolvedSet);
        sort($resolvedSet);
        sort($normalized);
        $this->assertEquals($normalized, $resolvedSet);
    }

    /**
     * Ensures that the resolver returns the entity class names without leading slash.
     */
    public function testResolvedSetContainsEntityClassesWithoutLeadingSlash()
    {
        $resolver = new EntityDependencyResolver(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ChainReferenceEntity'
        ));

        $resolvedSet = $this->getResolvedSet($resolver);

        foreach ($resolvedSet as $entityClass) {
            /* @var $entityClass string */
            $message = 'Entity class name must be normalized and must not start with \\.';
            $this->assertStringStartsNotWith('\\', $entityClass, $message);
        }
    }

    /**
     * Returns the resolved set of entity classes as array.
     *
     * @param EntityDependencyResolver $resolver
     * @return string[]
     */
    protected function getResolvedSet(EntityDependencyResolver $resolver)
    {
        $this->assertInstanceOf('\Traversable', $resolver);
        $entities = iterator_to_array($resolver);
        $this->assertContainsOnly('string', $entities);
        return $entities;
    }

    /**
     * Asserts that the resolved entity list contains the given entity.
     *
     * @param string $entity Name of the entity class.
     * @param EntityDependencyResolver|mixed $resolver
     */
    protected function assertContainsEntity($entity, $resolver)
    {
        $normalizedEntity = ltrim($entity, '\\');
        $this->assertContains(
            $normalizedEntity,
            $this->getResolvedSet($resolver)
        );
    }
}
