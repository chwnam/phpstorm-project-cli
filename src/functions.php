<?php
use FluidXml\FluidXml;
use FluidXml\FluidContext;
use function FluidXml\fluidify;

/**
 * Make path relative.
 *
 * @param string $path Input absolute path.
 * @param string $base Absolute base path.
 *
 * @return string
 */
function relativePath(string $path, string $base): string
{
    $path = str_replace(['/', '\\'], '/', $path);
    $base = str_replace(['/', '\\'], '/', $base);

    $arrayPath = explode('/', rtrim($path, '/'));
    $arrayBase = explode('/', rtrim($base, '/'));

    while (count($arrayPath) && count($arrayBase) && ($arrayBase[0] == $arrayPath[0])) {
        array_shift($arrayBase);
        array_shift($arrayPath);
    }

    return str_pad("", count($arrayBase) * 3, '..' . '/') . implode('/', $arrayPath);
}


/**
 * Get workspace XML.
 *
 * @param string $xmlPath
 *
 * @return FluidXml
 * @throws Exception
 */
function getFluidXml(string $xmlPath): FluidXml
{
    if (!file_exists($xmlPath)) {
        throw new Exception('Could not find workspace.xml.');
    }

    return \FluidXml\fluidify($xmlPath);
}

function getIdeaFilePath(string $projectDirectory, string $fileName): string
{
    return "$projectDirectory/.idea/$fileName";
}


function getWorkspacePath(string $projectDirectory): string
{
    return getIdeaFilePath($projectDirectory, 'workspace.xml');
}


/**
 * Fetch the component node
 *
 * @param FluidXml $xml
 * @param string $componentName
 *
 * @return FluidContext
 *
 * @throws Exception
 */
function fetchComponent(\FluidXml\FluidXml $xml, string $componentName): FluidContext
{
    $context = $xml->query("component[name='$componentName']");

    if (0 === $context->size()) {
        throw new Exception("Could not find $componentName.");
    }

    return $context;
}


/**
 * Save file
 *
 * @param FluidXml|FluidContext $xml
 * @param string $path
 * @param bool $makeBackup
 *
 * @return void
 * @throws Exception
 */
function saveXml(FluidXml|FluidContext $xml, string $path, bool $makeBackup = true): void
{
    if ('-' === $path) {
        echo $xml->xml() . PHP_EOL;
        return;
    }

    if ($makeBackup) {
        $index = 0;
        do {
            $backup = sprintf('%s.bak.%04d', $path, $index++);
        } while (file_exists($backup));
        copy($path, $backup);
    }

    $xml->save($path);
}

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
    $numFolders = $node->hasAttribute('Folders') ?
        (int)$node->getAttribute('Folders') : 0;
    if ($numFolders > 0) {
        for ($i = 0; $i < $numFolders; $i++) {
            if ($node->hasAttribute("Folder$i")) {
                $currentFolders[$i] = $node->getAttribute("Folder$i");
            }
        }
    }

    // Step 02: Fetch current custom dictionaries.
    $numCustomDictionaries = $node->hasAttribute('CustomDictionaries') ?
        (int)$node->getAttribute('CustomDictionaries') : 0;
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
