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

        $this->assertContains(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity',
            $this->getResolvedSet($resolver)
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

        $this->assertContains(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity',
            $this->getResolvedSet($resolver)
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

        $this->assertContains(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity',
            $this->getResolvedSet($resolver)
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

        $this->assertContains(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferenceCycleEntity',
            $this->getResolvedSet($resolver)
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
}
