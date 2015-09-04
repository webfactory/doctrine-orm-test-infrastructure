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
     * Ensures that a parent that uses class table inheritance is listed in the resolved set.
     */
    public function testResolvedSetContainsNameOfClassTableInheritanceParent()
    {
        $resolver = new EntityDependencyResolver(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Inheritance\ClassTableChildEntity'
        ));

        $this->assertContainsEntity(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Inheritance\ClassTableParentEntity',
            $resolver
        );
    }

    /**
     * Ensures that the resolved set contains an entity class that is referenced by a parent
     * entity (with class table inheritance strategy).
     */
    public function testResolvedSetContainsNameOfClassThatIsReferencedByParentWithClassTableStrategy()
    {
        $resolver = new EntityDependencyResolver(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Inheritance' .
            '\ClassTableChildWithParentReferenceEntity'
        ));

        $this->assertContainsEntity(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity',
            $resolver
        );
    }

    /**
     * Ensures that a mapped super class is listed in the resolved set.
     */
    public function testResolvedSetContainsNameOfMappedSuperClass()
    {
        $resolver = new EntityDependencyResolver(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Inheritance\MappedSuperClassChild'
        ));

        $this->assertContainsEntity(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Inheritance' .
            '\MappedSuperClassParentWithReference',
            $resolver
        );
    }

    /**
     * Ensures that an entity, that is referenced by a mapped super class, is listed in the resolved set.
     */
    public function testResolvedSetContainsNameOfEntityThatIsReferencedByMappedSuperClass()
    {
        $resolver = new EntityDependencyResolver(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Inheritance\MappedSuperClassChild'
        ));

        $this->assertContainsEntity(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity',
            $resolver
        );
    }

    /**
     * Ensures that the resolved set contains the entities that are explicitly mentioned in
     * a discriminator map.
     *
     * Doctrine uses the information from the discriminator map to generate its queries.
     * Therefore, the tables on the mentioned entities must be generated in the tests.
     */
    public function testResolvedSetContainsNamesOfEntitiesThatAreMentionedInDiscriminatorMap()
    {

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
