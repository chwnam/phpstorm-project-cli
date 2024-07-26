<?php declare(strict_types=1);

namespace Changwoo\PhpStormWorkspaceSetupHelper;

use function FluidXml\fluidify;

require_once __DIR__ . '/vendor/autoload.php';

if ('cli' !== php_sapi_name()) {
    die('This script must be run from the command line.');
}

try {
    $xml = fluidify(__DIR__ . '/file.xml');
    $xml->remove('component[name="VcsDirectoryMappings"] > mapping');
    $xml
        ->query('component[name="VcsDirectoryMappings"]')
        ->addChild('mapping', '', ['directory' => 'foo', 'vcs' => 'git'])
        ->addChild('mapping', '', ['directory' => 'bar', 'vcs' => 'git']);
    $xml->save('output.xml');
} catch (\Exception $e) {
}
