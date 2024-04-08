<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Doctrine entity that is used for to test custom annotations in combination with dependency discovery.
 *
 * @ORM\Entity()
 * @ORM\Table(name="test_entity_with_annotation")
 */
#[ORM\Table(name: 'test_entity_with_annotation')]
#[ORM\Entity]
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
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'id')]
    #[ORM\GeneratedValue]
    public $id = null;

    /**
     * A string property.
     *
     * @var string
     * @AnnotationForTestWithDependencyDiscovery()
     */
    public $name = null;
}
