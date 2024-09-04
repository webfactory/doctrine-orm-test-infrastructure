# Changelog for `webfactory/doctrine-orm-test-infrastructure`

This changelog tracks deprecations and changes breaking backwards compatibility. For more details on particular releases, consult the [GitHub releases page](https://github.com/webfactory/doctrine-orm-test-infrastructure/releases).

# Version 1.16

- The `\Webfactory\Doctrine\ORMTestInfrastructure\Query::getExecutionTimeInSeconds()` method has been deprecated without replacement in https://github.com/webfactory/doctrine-orm-test-infrastructure/pull/52, to prepare for the removal of the `DebugStack` class in Doctrine DBAL 4.0.
