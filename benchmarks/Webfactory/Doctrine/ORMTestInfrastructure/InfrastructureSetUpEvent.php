<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Athletic\AthleticEvent;

/**
 * Compares the different methods that can be used to setup the infrastructure.
 *
 * After creation of the infrastructure object, the entity manager must be retrieved
 * as the initialization is applied lazy.
 */
class InfrastructureSetUpEvent extends AthleticEvent
{
    /**
     * @iterations 100
     */
    public function simpleEntityWithAssociationDetection()
    {
        $infrastructure = ORMInfrastructure::createWithDependenciesFor(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        );
        $infrastructure->getEntityManager();
    }

    /**
     * @iterations 100
     */
    public function simpleEntityWithoutAssociationDetection()
    {
        $infrastructure = ORMInfrastructure::createOnlyFor(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        );
        $infrastructure->getEntityManager();
    }

    /**
     * @iterations 100
     */
    public function singleAssociationEntityWithAssociationDetection()
    {
        $infrastructure = ORMInfrastructure::createWithDependenciesFor(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityWithDependency'
        );
        $infrastructure->getEntityManager();
    }

    /**
     * @iterations 100
     */
    public function singleAssociationEntityWithExplicitAssociationDefinition()
    {
        $infrastructure = ORMInfrastructure::createOnlyFor(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityWithDependency',
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity'
        ));
        $infrastructure->getEntityManager();
    }

    /**
     * @iterations 100
     */
    public function complexEntityWithAssociationDetection()
    {
        $infrastructure = ORMInfrastructure::createWithDependenciesFor(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ChainReferenceEntity'
        );
        $infrastructure->getEntityManager();
    }

    /**
     * @iterations 100
     */
    public function complexEntityWithExplicitAssociationDefinition()
    {
        $infrastructure = ORMInfrastructure::createOnlyFor(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ChainReferenceEntity',
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityWithDependency',
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity'
        ));
        $infrastructure->getEntityManager();
    }
}
