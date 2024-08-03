<?php
/**
 * Append custom dictionaries
 *
 * Prototype 1, by changwoo, 2024. 08.
 */

use function FluidXml\fluidify;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/utils.php';

/**
 * Add custom dictionaries to the IDE settings.
 *
 * @param string $projectDirectory Absolute path to the project root.
 * @param string[] $customDictionaries Custom dictionaries list.
 *
 * @throws Exception
 */
function addCustomDictionaries(string $projectDirectory, array $customDictionaries): void
{
    $xml = getFluidXml(getWorkspacePath($projectDirectory));

    // Find the target node.
    $context = fetchComponent($xml, 'SpellCheckerSettings');

    /**
     * @var DOMElement $node The target node.
     * @var string[] $currentFolders Current 'Folders{X}' attribute items.
     * @var string[] $newFolders New 'Folders{X}' attribute items.
     * @var string[] $currentCustomDictionaries Current 'CustomDictionaries{X}' attribute items.
     * @var string[] $newCustomDictionaries New 'CustomDictionaries{X}' attribute items.
     */
    $node = $context[0];
    $currentFolders = [];
    $newFolders = [];
    $currentCustomDictionaries = [];
    $newCustomDictionaries = [];

    // Step 01: Fetch current folders.
    $numFolders = $node->hasAttribute('Folders') ? (int)$node->getAttribute('Folders') : 0;
    if ($numFolders > 0) {
        for ($i = 0; $i < $numFolders; $i++) {
            if ($node->hasAttribute("Folder$i")) {
                $currentFolders[$i] = $node->getAttribute("Folder$i");
            }
        }
    }

    // Step 02: Fetch current custom dictionaries.
    $numCustomDictionaries = $node->hasAttribute('CustomDictionaries') ? (int)$node->getAttribute(
        'CustomDictionaries'
    ) : 0;
    if ($numCustomDictionaries > 0) {
        for ($i = 0; $i < $numCustomDictionaries; $i++) {
            if ($node->hasAttribute("CustomDictionary$i")) {
                $currentCustomDictionaries[$i] = $node->getAttribute("CustomDictionary$i");
            }
        }
    }

    // Step 03: Create next folders and custom dictionaries items.
    foreach ($customDictionaries as $dictionary) {
        if (!file_exists($dictionary)) {
            continue;
        }

        $newFolder = '$PROJECT_DIR$/' . relativePath(dirname($dictionary), $projectDirectory);
        $newCustomDictionary = '$PROJECT_DIR$/' . relativePath($dictionary, $projectDirectory);

        $p1 = in_array($newFolder, $currentFolders);
        $p2 = in_array($newCustomDictionary, $currentCustomDictionaries);

        if (!$p1 && !$p2) {
            if (!in_array($newFolder, $newFolders)) {
                $newFolders[] = $newFolder;
            }
            $newCustomDictionaries[] = $newCustomDictionary;
        }
    }

    // Step 04: Push into the node.
    $totalFolder = count($currentFolders) + count($newFolders);
    $node->setAttribute('Folders', $totalFolder);
    foreach ($newFolders as $i => $newFolder) {
        $idx = count($currentFolders) + $i;
        $node->setAttribute("Folder$idx", $newFolder);
    }

    $totalCustomDictionaries = count($currentCustomDictionaries) + count($newCustomDictionaries);
    $node->setAttribute('CustomDictionaries', $totalCustomDictionaries);
    foreach ($newCustomDictionaries as $i => $newCustomDictionary) {
        $idx = count($currentCustomDictionaries) + $i;
        $node->setAttribute("CustomDictionary$idx", $newCustomDictionary);
    }

    // Step 05: Crate backup file, and save the file.
    saveXml($xml, getWorkspacePath($projectDirectory));
}

try {
    if ('cli' !== php_sapi_name()) {
        throw new Exception('This script can only be run from cli');
    }
    addCustomDictionaries(
        projectDirectory: '/home/changwoo/develop/wordpress/empty-project',
        customDictionaries: [
            '/home/changwoo/develop/wordpress/libs/ko-aff-dic-0.7.94/ko.dic',
            '/home/changwoo/develop/wordpress/libs/custom-plugin-directory/custom.dic',
        ],
    );
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
