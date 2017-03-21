<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest;

use Doctrine\ORM\Mapping as ORM;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Annotation\CustomAnnotation;

/**
 * Doctrine entity that is used for testing.
 *
 * @ORM\Entity()
 * @ORM\Table(name="test_entity_with_annotation")
 */
class TestEntityWithAnnotation
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

    /**
     * A string property.
     *
     * @var string
     * @CustomAnnotation()
     */
    public $name = null;
}
