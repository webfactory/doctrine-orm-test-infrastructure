# Autoloading of Test Classes #

Autoloading for the test classes in this directory is configured as class map (only available in dev mode).
Therefore, the class map must be regenerated whenever test classes are added here:

    php composer.phar install

If this step is omitted, autoloading of recently added test classes will fail.
