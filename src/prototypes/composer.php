<?php
/**
 * Append composer.json
 *
 * Prototype 1, by changwoo, 2024. 08.
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/utils.php';

/**
 * Set up composer
 *
 * @param string $projectDirectory
 * @param string $composerJsonPath
 * @param string $composerExecutablePath
 * @return void
 *
 * @throws Exception
 */
function setComposer(
    string $projectDirectory,
    string $composerJsonPath,
    string $composerExecutablePath = 'composer'
): void {
    $xml = getFluidXml(getWorkspacePath($projectDirectory));

    // Find the target node.
    $context = fetchComponent($xml, 'ComposerSettings');

    /**
     * @var DOMElement $node The target node.
     * @var string[] $currentPharConfigPath
     * @var string[] $currentExecutables
     */
    $currentPharConfigPath = [];
    $currentExecutables = [];

    // Step 01: add synchronizationState="SYNCHRONIZE" attribute.
    $context->attr('synchronizationState', 'SYNCHRONIZE');

    // Step 02: Add pharConfigPath child node.
    $relativePath = '$PROJECT_DIR$/' . relativePath($composerJsonPath, $projectDirectory);
    $context
        ->query('./pharConfigPath')
        ->each(function (int $i, DomElement $node) use (&$currentPharConfigPath) {
            $currentPharConfigPath[] = $node->textContent;
        });
    if (!in_array($relativePath, $currentPharConfigPath)) {
        $context->addChild('pharConfigPath', $relativePath);
    }

    // Step 03: Add execution, executable path.
    if (str_starts_with($composerExecutablePath, '/')) {
        $composerExecutablePath = '$PROJECT_DIR$/' . relativePath($composerExecutablePath, $projectDirectory);
    }
    // Get all executable paths.
    $context
        ->query('./execution/executable')
        ->each(function (int $_, DOMElement $node) use (&$currentExecutables) {
            $currentExecutables[] = $node->getAttribute('path');
        });
    // Add composer executable path.
    if (!in_array($composerExecutablePath, $currentExecutables)) {
        if (0 === $context->query('./execution')->size()) {
            $context->addChild('execution');
        }
        $context
            ->query('./execution')
            ->addChild('executable', '', ['path' => $composerExecutablePath]);
    }

    saveXml($xml, getWorkspacePath($projectDirectory));
}

try {
    if ('cli' !== php_sapi_name()) {
        throw new Exception('This script can only be run from cli');
    }
    setComposer(
        projectDirectory: '/home/changwoo/develop/wordpress/empty-project',
        composerJsonPath: '/home/changwoo/develop/wordpress/empty-project/wp-content/plugins/foo/composer.json',
//        composerExecutablePath: 'composer',
    );
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
