<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure;

/**
 * Takes a set of entity classes and resolves to a set that contains all entities
 * that are referenced by the provided entity classes (via associations).
 *
 * The resolved set also includes the original entity classes.
 */
class EntityDependencyResolver
{
    /**
     * Creates a resolver for the given entity classes.
     *
     * @param string[] $entityClasses
     */
    public function __construct(array $entityClasses)
    {

    }
}
