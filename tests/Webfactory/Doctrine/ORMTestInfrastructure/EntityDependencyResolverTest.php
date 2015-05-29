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

    }

    /**
     * Checks if the resolved set contains the initially provided entity classes.
     */
    public function testSetContainsProvidedEntityClasses()
    {

    }

    /**
     * Checks if entities that are directly associated to the initially provided entities
     * are contained in the resolved set.
     */
    public function testSetContainsEntityClassesThatAreDirectlyConnectedToInitialSet()
    {

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

    }

    /**
     * Ensures that the resolver can handle dependency cycles.
     */
    public function testResolverCanHandleDependencyCycles()
    {

    }
}
