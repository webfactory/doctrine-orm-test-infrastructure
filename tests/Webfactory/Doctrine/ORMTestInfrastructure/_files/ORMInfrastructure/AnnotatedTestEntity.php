<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest;

use Doctrine\ORM\Mapping as ORM;

/**
 * An entity that uses a custom annotation.
 *
 * @ORM\Entity()
 * @ORM\Table(name="annotated_test_entity")
 * @TestAnnotation
 */
class AnnotatedTestEntity
{

    /**
     * A unique ID.
     *
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue
     */
    public $id = null;
}
