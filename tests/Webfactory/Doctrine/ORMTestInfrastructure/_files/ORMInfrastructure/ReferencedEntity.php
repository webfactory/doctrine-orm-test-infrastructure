<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest;

use Doctrine\ORM\Mapping as ORM;

/**
 * An entity that is referenced by another one.
 *
 * @ORM\Entity()
 * @ORM\Table(name="referenced_entity")
 */
class ReferencedEntity
{

}
