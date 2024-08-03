<?php
/**
 * Append PHP language level settings.
 *
 * Prototype 1, by changwoo, 2024. 08.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/utils.php';

/**
 * Set up PHP language level
 *
 * @param string $projectDirectory
 * @param string $languageLevel
 * @return void
 *
 * @throws Exception
 */
function setPhpLanguageLevel(string $projectDirectory, string $languageLevel): void
{
    $xml = getFluidXml(getIdeaFilePath($projectDirectory, 'php.xml'));

    if (0 === $xml->query('project/component[name="PhpProjectSharedConfiguration"]')->size()) {
        $xml
            ->addChild(
                'component',
                '',
                [
                    'name' => 'PhpProjectSharedConfiguration',
                    'php_language_level' => '',
                ],
                true
            )
            ->add(
                'option',
                '',
                [
                    'name' => 'suggestChangeDefaultLanguageLevel',
                    'value' => 'false',
                ]
            );
    }

    $context = fetchComponent($xml, 'PhpProjectSharedConfiguration');
    $context->attr('php_language_level', $languageLevel);
    saveXml($xml, getIdeaFilePath($projectDirectory, 'php.xml'));
}

try {
    if ('cli' !== php_sapi_name()) {
        throw new Exception('This script can only be run from cli');
    }
    setPhpLanguageLevel(
        '/home/changwoo/develop/wordpress/empty-project',
        '7.2'
    );
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
