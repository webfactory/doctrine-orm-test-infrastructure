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
 * Object manager that detaches entities after storing them.
 *
 * This decorator is mainly useful for imports, as entities are not populated
 * with database contents when they are already attached.
 * This may lead to tests that pass because of object identity without noticing
 * that the real reading from the database does not work as expected.
 *
 * @deprecated Will be removed in 2.0.
 * @see MemorizingObjectManagerDecorator
 * @internal
 */
class DetachingObjectManagerDecorator extends ObjectManagerDecorator
{

    /**
     * Contains all entities that will be detached on the next flush.
     *
     * @var array(object)
     */
    protected $entitiesToDetach = array();

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
    public function persist($object)
    {
        $this->entitiesToDetach[] = $object;
        $this->wrapped->persist($object);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->wrapped->flush();
        $this->detachPersistedEntities();
    }

    /**
     * Detaches all entities that have been passed to persist.
     */
    protected function detachPersistedEntities()
    {
        foreach ($this->entitiesToDetach as $entity) {
            /* @var $entity object */
            $this->wrapped->detach($entity);
        }
        $this->entitiesToDetach = array();
    }
}
