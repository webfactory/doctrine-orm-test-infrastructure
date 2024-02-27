<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManagerDecorator;

/**
 * Object manager that remembers entities that where stored.
 *
 * This decorator is mainly useful during imports, where stored entities must
 * be removed from the identity map afterwards.
 *
 * @internal
 * @deprecated Will be removed in 2.0. Not needed anymore, as selective detach()
 *             calls have been removed from the importer.
 */
class MemorizingObjectManagerDecorator extends ObjectManagerDecorator
{
    /**
     * Contains all entities that were persisted by this object manager decorator.
     *
     * @var object[]
     */
    private $seenEntities = array();

    /**
     * Creates a decorator that encapsulates the provided object manager.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->wrapped = $objectManager;
    }

    /**
     * {@inheritdoc}
     *
     * @param object $object
     */
    public function persist($object): void
    {
        $this->seenEntities[] = $object;
        $this->wrapped->persist($object);
    }

    /**
     * Returns all entities that were passed through this decorator.
     *
     * @return object[]
     */
    public function getSeenEntities()
    {
        return $this->seenEntities;
    }
}
