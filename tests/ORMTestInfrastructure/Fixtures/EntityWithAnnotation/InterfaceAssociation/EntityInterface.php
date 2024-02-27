<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\InterfaceAssociation;

/**
 * Interface that is used as target in an association.
 */
interface EntityInterface
{
    /**
     * Dummy function.
     *
     * @return integer
     */
    public function getId();
}
