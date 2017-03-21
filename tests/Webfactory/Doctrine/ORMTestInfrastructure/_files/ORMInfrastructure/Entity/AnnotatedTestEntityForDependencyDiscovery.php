<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest;

use Doctrine\ORM\Mapping as ORM;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Annotation\AnnotationForTestWithDependencyDiscovery;

/**
 * Doctrine entity that is used for to test custom annotations in combination with dependency discovery.
 *
 * @ORM\Entity()
 * @ORM\Table(name="test_entity_with_annotation")
 */
class AnnotatedTestEntityForDependencyDiscovery
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
     * @AnnotationForTestWithDependencyDiscovery()
     */
    public $name = null;
}
