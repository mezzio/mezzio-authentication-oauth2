<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('Entity', 'src/Entity')
    ->layer('Exception', 'src/Exception')
    ->layer('Response', 'src/Response')
    ->layer('Helper', [
        'src/ConfigTrait.php',
        'src/CryptKeyTrait.php',
        'src/Psr17ResponseFactoryTrait.php',
        'src/RepositoryTrait.php',
    ])
    ->layer('Grant', 'src/Grant')
    ->layer('Repository', 'src/Repository')
    ->layerPattern(
        'OAuth2',
        '/^Mezzio\\\\Authentication\\\\OAuth2\\\\.*$/',
        [
            '/^Mezzio\\\\Authentication\\\\OAuth2\\\\(Entity|Exception|Grant|Repository|Response)(\\\\.*)?$/',
            '/^Mezzio\\\\Authentication\\\\OAuth2\\\\\w+Trait$/',
            '/^Mezzio\\\\Authentication\\\\OAuth2\\\\ConfigProvider$/',
        ]
    )
    ->layer('ConfigProvider', 'src/ConfigProvider.php')
    ->ruleset([
        'Entity'         => [],
        'Exception'      => [],
        'Response'       => [],
        'Helper'         => ['Exception', 'Response'],
        'Grant'          => ['+Helper'],
        'Repository'     => ['Entity', 'Exception'],
        'OAuth2'         => ['+Helper'],
        'ConfigProvider' => ['+OAuth2', '+Grant', '+Repository'],
    ]);
