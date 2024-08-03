<?php
/**
 * Utils for prototype 1.
 */

use FluidXml\FluidXml;
use FluidXml\FluidContext;

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
