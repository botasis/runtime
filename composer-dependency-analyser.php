<?php
declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

return $config
    ->addPathsToScan([__DIR__ . '/config', __DIR__ . '/src'], false)
    ->addPathToScan(__DIR__ . '/tests', true)
    ->ignoreErrorsOnPath(__DIR__ . '/config', [ErrorType::DEV_DEPENDENCY_IN_PROD]) // We really can configure non-existent classes
    ->ignoreErrorsOnPath(__DIR__ . '/src/Console', [ErrorType::DEV_DEPENDENCY_IN_PROD]) // symfony/console is an optional dependency, CLI commands won't be used without it
    ->disableComposerAutoloadPathScan();
