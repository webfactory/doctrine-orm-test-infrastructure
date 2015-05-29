<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

/**
 * Takes a set of entity classes and resolves to a set that contains all entities
 * that are referenced by the provided entity classes (via associations).
 *
 * The resolved set also includes the original entity classes.
 */
class EntityDependencyResolver implements \IteratorAggregate
{
    /**
     * Creates a resolver for the given entity classes.
     *
     * @param string[] $entityClasses
     */
    public function __construct(array $entityClasses)
    {

    }

    /**
     * Allows iterating over the set of resolved entities.
     *
     * @return \Traversable
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     */
    public function getIterator()
    {

    }
}
