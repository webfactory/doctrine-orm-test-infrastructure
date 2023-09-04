<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\_files\ORMInfrastructure\Attribute;

use Attribute;

if (PHP_VERSION_ID >= 80000) {
    #[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
    class AttributeForSimpleTest
    {

    }
}